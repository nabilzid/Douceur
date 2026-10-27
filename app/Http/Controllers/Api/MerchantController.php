<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

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
        $user = $request->user();
        $merchant = $this->merchant($request);

        $data = [
            'products_count' => $merchant->products()->count(),
            'low_stock_count' => $merchant->products()->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count(),
            'orders_today' => $merchant->orders()->whereDate('created_at', today())->count(),
            'pending_orders' => $merchant->orders()->where('status', 'pending')->count(),
        ];

        // ✅ CA réservé à l'admin
        if ($user->role === 'admin') {
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
        if ($order->merchant_id !== $this->merchant($request)->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,preparing,ready,delivering,delivered,cancelled',
            'comment' => 'nullable|string',
        ]);

        $order->update(['status' => $validated['status']]);

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'status' => $validated['status'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json($order->load('items'));
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

        $validated = $request->validate([
            'status' => 'required|in:completed',
        ]);

        // ✅ Générer la facture de solde si un reste à payer existe
        $remaining = $customOrder->quoted_price - $customOrder->amount_paid;

        if ($remaining > 0) {
            $invoiceService = new \App\Services\InvoicePdfService(
                new \App\Services\InvoiceNumberService()
            );
            $path = $invoiceService->generateBalance($customOrder, $remaining);
            $customOrder->update(['balance_invoice_path' => $path]);
        }

        $customOrder->update(['status' => 'completed']);

        $notification = new \App\Notifications\CustomOrderStatusChanged($customOrder);
        if ($customOrder->user) {
            $customOrder->user->notify($notification);
        } else {
            \Illuminate\Support\Facades\Notification::route('mail', $customOrder->customer_email)
                ->notify($notification);
        }

        return response()->json($customOrder);
    }
    
}