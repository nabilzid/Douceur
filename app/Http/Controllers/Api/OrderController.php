<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Merchant;
use App\Models\User;
use App\Models\Setting;
use App\Notifications\NewOrderReceived;
use App\Notifications\LowStockAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $user = $request->user();

        $rules = [
            'delivery_type' => 'required|in:delivery,pickup',
            'delivery_address' => 'required_if:delivery_type,delivery|nullable|string',
            'delivery_city' => 'required_if:delivery_type,delivery|nullable|string',
            'delivery_postal_code' => 'required_if:delivery_type,delivery|nullable|string|max:10',
            'delivery_slot_id' => 'nullable|exists:delivery_slots,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];

        if (! $user) {
            $rules['customer_name'] = 'required|string|max:255';
            $rules['customer_email'] = 'required|email';
            $rules['customer_phone'] = 'required|string|max:20';
        }

        $validated = $request->validate($rules);

        return DB::transaction(function () use ($request, $validated, $user) {
            $items = collect($validated['items']);
            $productIds = $items->pluck('product_id');
            $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

            $subtotal = 0;
            $merchantId = null;
            $orderItemsData = [];

            // ✅ 1. Calcul du sous-total + vérification du stock
            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if (! $product || ! $product->is_active) {
                    abort(422, "Produit {$item['product_id']} indisponible.");
                }
                if ($product->stock_quantity < $item['quantity']) {
                    abort(422, "Stock insuffisant pour {$product->name}.");
                }

                $lineTotal = $product->price * $item['quantity'];
                $subtotal += $lineTotal;
                $merchantId = $merchantId ?? $product->merchant_id;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $item['quantity'],
                    'line_total' => $lineTotal,
                ];
            }

            // ✅ 2. Vérification du seuil de livraison
            $minDelivery = (float) Setting::get('delivery.min_order_amount', 15);
            if ($validated['delivery_type'] === 'delivery' && $subtotal < $minDelivery) {
                abort(422, "Le montant minimum pour la livraison est de {$minDelivery}€.");
            }

            // ✅ 3. Calcul des frais de livraison
            $deliveryFee = 0;
            if ($validated['delivery_type'] === 'delivery') {
                $freeThreshold = (float) Setting::get('delivery.free_threshold', 30);
                $defaultFee = (float) Setting::get('delivery.default_fee', 5);
                $deliveryFee = $subtotal >= $freeThreshold ? 0 : $defaultFee;
            }

            $total = $subtotal + $deliveryFee;

            // ✅ 4. Coordonnées client
            $customerName = $user?->name ?? $validated['customer_name'];
            $customerEmail = $user?->email ?? $validated['customer_email'];
            $customerPhone = $user?->phone ?? $validated['customer_phone'];

            // ✅ 5. Création de la commande
            $order = Order::create([
                'reference' => 'CMD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4)),
                'user_id' => $user?->id,
                'merchant_id' => $merchantId,
                'delivery_slot_id' => $validated['delivery_slot_id'] ?? null,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'customer_phone' => $customerPhone,
                'delivery_type' => $validated['delivery_type'],
                'delivery_address' => $validated['delivery_address'] ?? null,
                'delivery_city' => $validated['delivery_city'] ?? null,
                'delivery_postal_code' => $validated['delivery_postal_code'] ?? null,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount_amount' => 0,
                'total_amount' => $total,
                'status' => 'pending',
                'payment_status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // ✅ 6. Lignes de commande
            $order->items()->createMany($orderItemsData);

            // ✅ 7. Décrémenter le stock + alerte
            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $before = $product->stock_quantity;
                $product->decrement('stock_quantity', $item['quantity']);

                $after = $product->fresh()->stock_quantity;

                if ($after <= $product->low_stock_threshold && $before > $product->low_stock_threshold) {
                    $merchant = Merchant::find($product->merchant_id);
                    if ($merchant && $merchant->user) {
                        $merchant->user->notify(new LowStockAlert($product->fresh()));
                    }

                    User::where('role', 'admin')->get()->each(
                        fn ($admin) => $admin->notify(new LowStockAlert($product->fresh()))
                    );
                }
            }

            // ✅ 8. Historique
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'user_id' => $user?->id,
                'status' => 'pending',
                'comment' => 'Commande créée',
            ]);

            return response()->json([
                'order' => $order->load('items'),
                'message' => 'Commande créée avec succès.',
            ], 201);
        });
    }

    public function show(string $reference)
    {
        $order = Order::where('reference', $reference)
            ->with(['items', 'merchant', 'deliverySlot'])
            ->firstOrFail();

        return response()->json($order);
    }

    public function track(Request $request)
    {
        $validated = $request->validate([
            'reference' => 'required|string',
            'contact' => 'nullable|string', // ← optionnel pour les connectés
        ]);

        $query = Order::where('reference', $validated['reference']);

        // Si le client est connecté, on vérifie que la commande lui appartient
        if ($request->user()) {
            $order = $query->where('user_id', $request->user()->id)->with('items')->first();
            if ($order) {
                return response()->json($this->formatOrder($order));
            }
            // Si la commande n'appartient pas au client, on continue avec la vérification par contact
        }

        // Sinon, vérification par email/téléphone
        if (! $validated['contact']) {
            abort(422, 'Veuillez fournir un email ou téléphone.');
        }

        $order = $query->where(function ($q) use ($validated) {
            $q->where('customer_email', $validated['contact'])
            ->orWhere('customer_phone', $validated['contact']);
        })->with('items')->first();

        if (! $order) {
            abort(404, 'Aucune commande trouvée avec ces informations.');
        }

        return response()->json($this->formatOrder($order));
    }

    private function formatOrder(Order $order): array
    {
        return [
            'reference' => $order->reference,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'customer_name' => $order->customer_name,
            'delivery_type' => $order->delivery_type,
            'delivery_address' => $order->delivery_address,
            'delivery_city' => $order->delivery_city,
            'delivery_postal_code' => $order->delivery_postal_code,
            'subtotal' => $order->subtotal,
            'delivery_fee' => $order->delivery_fee,
            'total_amount' => $order->total_amount,
            'created_at' => $order->created_at,
            'items' => $order->items->map(fn ($item) => [
                'product_name' => $item->product_name,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
            ]),
        ];
    }
}