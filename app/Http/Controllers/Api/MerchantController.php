<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\DB;
use App\Services\InvoiceNumberService;
use App\Services\InvoicePdfService;
use App\Events\AdminNotificationCreated;
class MerchantController extends Controller
{
    private function merchant(Request $request)
    {
        $merchant = \App\Models\Merchant::first();

        if (! $merchant) {
            abort(403, 'Aucune boutique configurée.');
        }

        return $merchant;
    }

    public function dashboard(Request $request)
    {
        $merchant = $this->merchant($request);

        $customOrders = \App\Models\CustomOrder::where('merchant_id', $merchant->id);

        $data = [
            'products_count' => $merchant->products()->count(),
            'low_stock_count' => $merchant->products()->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            'orders_today' => $merchant->orders()->whereDate('created_at', today())->count(),
            'pending_orders' => $merchant->orders()->where('status', 'pending')->count(),

            // ✅ Sur-mesure
            'custom_orders_total' => (clone $customOrders)->count(),
            'custom_orders_pending' => (clone $customOrders)->where('status', 'pending_quote')->count(),
            'custom_orders_quoted' => (clone $customOrders)->where('status', 'quoted')->count(),
            'custom_orders_accepted' => (clone $customOrders)->where('status', 'accepted')->count(),
        ];

        // CA réservé à l'admin
        if ($request->user()->role === 'admin') {
            $data['revenue_today'] = $merchant->orders()
                ->whereDate('created_at', today())
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount');
        }

        return response()->json($data);
    }

    public function products(Request $request)
    {
        return $this->merchant($request)->products()->with('type')->orderBy('name')->get();
    }

    public function updateProduct(Request $request, Product $product)
    {
        $user = $request->user();
        $this->authorizeProduct($request, $product);

        if ($user->role === 'merchant') {
            // ✅ Pâtissier : seulement is_active
            $validated = $request->validate([
                'is_active' => 'required|boolean',
            ]);
            $product->update(['is_active' => $validated['is_active']]);
        } else {
            // ✅ Admin : tous les champs
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'price' => 'sometimes|numeric|min:0',
                'low_stock_threshold' => 'sometimes|integer|min:0',
                'is_active' => 'sometimes|boolean',
            ]);
            $product->update($validated);
        }
        // ✅ Broadcast pour rafraîchir les autres onglets/pages
        AdminNotificationCreated::dispatch([
            'type' => 'product_updated',
            'title' => "Produit mis à jour : {$product->name}",
            'message' => $product->is_active ? 'Activé' : 'Désactivé',
            'url' => '/admin/produits',
            'icon' => $product->is_active ? '✅' : '⏸️',
        ]);
        return response()->json($product->fresh());
    }

    public function adjustStock(Request $request, Product $product)
    {
        $this->authorizeProduct($request, $product);

        $validated = $request->validate([
            'quantity' => 'required|integer|not_in:0',
            'reason' => 'nullable|string|max:255',
        ]);

        $before = $product->stock_quantity;
        $after = max(0, $before + $validated['quantity']);

        $product->update(['stock_quantity' => $after]);

        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
            'type' => $validated['quantity'] > 0 ? 'in' : 'adjustment',
            'quantity' => $validated['quantity'],
            'stock_before' => $before,
            'stock_after' => $after,
            'reason' => $validated['reason'] ?? null,
        ]);

        // Alerte stock faible
        if ($after <= $product->low_stock_threshold && $before > $product->low_stock_threshold) {
            $merchant = $this->merchant($request);
            if ($merchant && $merchant->user) {
                $merchant->user->notify(new \App\Notifications\LowStockAlert($product->fresh()));
            }

            \App\Models\User::where('role', 'admin')->get()->each(
                fn ($admin) => $admin->notify(new \App\Notifications\LowStockAlert($product->fresh()))
            );
        }

        // ✅ Broadcast pour les autres onglets admin
        AdminNotificationCreated::dispatch([
            'type' => 'stock_updated',
            'title' => "Stock mis à jour : {$product->name}",
            'message' => "Nouveau stock : {$after}",
            'url' => '/admin/produits',
            'icon' => '📦',
        ]);

        return response()->json($product->fresh());
    }

    public function orders(Request $request)
    {
        $status = $request->query('status');

        $query = $this->merchant($request)->orders()->with('items')->latest();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function updateOrderStatus(Request $request, Order $order)
    {
        $order->loadMissing('items');

        if ($order->merchant_id !== $this->merchant($request)->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,delivering,delivered,cancelled',
            'comment' => 'nullable|string',
        ]);

        $newStatus = $validated['status'];
        $currentStatus = $order->status;

        if (in_array($currentStatus, ['delivered', 'cancelled'])) {
            abort(422, "Une commande {$currentStatus} ne peut plus être modifiée.");
        }

        if ($newStatus !== 'cancelled') {
            $orderFlow = ['pending', 'confirmed', 'preparing', 'ready', 'delivering', 'delivered'];
            $currentIndex = array_search($currentStatus, $orderFlow);
            $newIndex = array_search($newStatus, $orderFlow);

            if ($newIndex === false || $newIndex <= $currentIndex) {
                abort(422, "Transition non autorisée : {$currentStatus} → {$newStatus}.");
            }
        }

        if ($newStatus === 'cancelled') {
            DB::transaction(function () use ($order) {
                foreach ($order->items as $item) {
                    \App\Models\Product::where('id', $item->product_id)
                        ->increment('stock_quantity', $item->quantity);
                }
            });

            if ($order->payment_status === 'paid') {
                \Log::warning("Commande {$order->reference} annulée mais payée — remboursement Stripe à faire manuellement");
            }

            // ✅ Notifier le client (connecté ou invité)
            try {
                $notification = new \App\Notifications\OrderCancelled($order);

                if ($order->user) {
                    $order->user->notify($notification);
                } elseif ($order->customer_email) {
                    \Illuminate\Support\Facades\Notification::route('mail', $order->customer_email)
                        ->notify($notification);
                }
            } catch (\Throwable $e) {
                \Log::warning('OrderCancelled mail failed', [
                    'reference' => $order->reference,
                    'error' => $e->getMessage(),
                ]);
            }

            // ✅ Broadcast Reverb
            \App\Events\AdminNotificationCreated::dispatch([
                'type' => 'order_cancelled',
                'title' => "Commande {$order->reference} annulée",
                'message' => "Stock restauré pour {$order->items->count()} article(s)",
                'url' => '/admin/commandes',
                'icon' => '↩️',
            ]);
        }

        DB::transaction(function () use ($order, $newStatus, $request, $validated) {
            $order->update(['status' => $newStatus]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => $request->user()->id,
                'status' => $newStatus,
                'comment' => $validated['comment'] ?? null,
            ]);

            if ($newStatus === 'delivered' && ! $order->invoice_path) {
                $invoiceService = app(\App\Services\InvoicePdfService::class);
                $invoiceService->generateOrder($order);
            }
        });

        if ($newStatus === 'delivered') {
            $this->notifyInvoiceAvailable($order->fresh());
        }

        return response()->json($order->fresh()->load('items'));
    }

    /**
     * Envoie le mail "facture disponible" au client (connecté ou invité).
     */
    private function notifyInvoiceAvailable(Order $order): void
    {
        try {
            $notification = new \App\Notifications\OrderInvoiceAvailable($order);

            if ($order->user) {
                $order->user->notify($notification);
            } elseif ($order->customer_email) {
                \Illuminate\Support\Facades\Notification::route('mail', $order->customer_email)
                    ->notify($notification);
            }
        } catch (\Throwable $e) {
            \Log::warning('OrderInvoiceAvailable mail failed', [
                'reference' => $order->reference,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function authorizeProduct(Request $request, Product $product): void
    {
        if ($product->merchant_id !== $this->merchant($request)->id) {
            abort(403, 'Ce produit n’appartient pas à votre boutique.');
        }
    }

    public function customOrders(Request $request)
    {
        $status = $request->query('status');

        $query = $this->merchant($request)->customOrders()->latest();

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->get();

        // ✅ Ajouter le libellé et les champs de livraison à chaque commande
        return $orders->map(function ($order) {
            $order->event_label = \App\Models\CustomOrder::EVENT_TYPES[$order->event_type] ?? $order->event_type;

            // ✅ Champs de livraison
            $order->delivery_type = $order->delivery_type;
            $order->delivery_address = $order->delivery_address;
            $order->delivery_postal_code = $order->delivery_postal_code;
            $order->delivery_city = $order->delivery_city;
            $order->delivery_notes = $order->delivery_notes;

            return $order;
        });
    }

    public function quoteCustomOrder(Request $request, \App\Models\CustomOrder $customOrder)
    {
        if ($customOrder->merchant_id !== $this->merchant($request)->id) {
            abort(403);
        }

        $validated = $request->validate([
            'quoted_price' => 'required|numeric|min:0',
            'merchant_response' => 'required|string',
        ]);

        $customOrder->update([
            'quoted_price' => $validated['quoted_price'],
            'merchant_response' => $validated['merchant_response'],
            'status' => 'quoted',
        ]);

        // ✅ Notifier le client (connecté ou invité)
        $notification = new \App\Notifications\CustomOrderQuoted($customOrder);
        if ($customOrder->user) {
            $customOrder->user->notify($notification);
        } else {
            \Illuminate\Support\Facades\Notification::route('mail', $customOrder->customer_email)
                ->notify($notification);
        }

        return response()->json($customOrder);
    }

    public function updateCustomOrderStatus(Request $request, \App\Models\CustomOrder $customOrder)
    {
        if ($customOrder->merchant_id !== $this->merchant($request)->id) {
            abort(403);
        }

        if ($customOrder->status !== 'accepted') {
            abort(422, 'Seule une demande acceptée peut être marquée comme terminée.');
        }

        $total = (float) ($customOrder->quoted_price ?? 0);
        $paid = (float) ($customOrder->amount_paid ?? 0);
        $remaining = round($total - $paid, 2);
        $hasBalance = $remaining > 0.01;

        $validated = $request->validate([
            'status' => 'required|in:completed',
            'payment_method' => [
                $hasBalance ? 'required' : 'nullable',
                'in:cash,card,transfer,check',
            ],
        ]);

        DB::transaction(function () use ($customOrder, $validated, $hasBalance, $remaining) {
            if ($hasBalance) {
                // 1. Met à jour le paiement AVANT la facture
                //    → le PDF contiendra les bonnes valeurs (reste = 0)
                $customOrder->amount_paid = $customOrder->quoted_price;
                $customOrder->balance_payment_method = $validated['payment_method'];
                $customOrder->balance_paid_at = now();

                // 2. Génère la facture de solde
                $invoiceService = new InvoicePdfService(new InvoiceNumberService());
                $customOrder->balance_invoice_path =
                    $invoiceService->generateBalance($customOrder, $remaining);
            }

            // 3. Clôture
            $customOrder->status = 'completed';
            $customOrder->completed_at = now();
            $customOrder->save();
        });

        // Notification client
        $notification = new \App\Notifications\CustomOrderStatusChanged($customOrder);
        if ($customOrder->user) {
            $customOrder->user->notify($notification);
        } else {
            \Illuminate\Support\Facades\Notification::route('mail', $customOrder->customer_email)
                ->notify($notification);
        }

        // Reverb → rafraîchit la liste admin en direct
        \App\Events\AdminNotificationCreated::dispatch([
            'type' => 'custom_order_completed',
            'title' => "Commande {$customOrder->reference} terminée",
            'message' => $hasBalance
                ? "{$customOrder->customer_name} — solde de " . number_format($remaining, 2, ',', ' ') . ' € encaissé'
                : "{$customOrder->customer_name} — clôturée",
            'url' => '/admin/sur-mesure',
            'icon' => '✅',
        ]);

        return response()->json($customOrder->fresh());
    }
    
}