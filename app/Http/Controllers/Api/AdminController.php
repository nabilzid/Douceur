<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        return response()->json([
            'total_users' => User::count(),
            'total_merchants' => User::where('role', 'merchant')->count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_orders' => \App\Models\Order::count(),
            'total_revenue' => \App\Models\Order::where('payment_status', 'paid')->sum('total_amount'),
            'pending_orders' => \App\Models\Order::where('status', 'pending')->count(),
        ]);
    }

    public function settings()
    {
        return response()->json([
            'delivery_min_order_amount' => (float) Setting::get('delivery.min_order_amount', 15),
            'delivery_free_threshold' => (float) Setting::get('delivery.free_threshold', 30),
            'delivery_default_fee' => (float) Setting::get('delivery.default_fee', 5),
            'tax_vat_rate' => (float) Setting::get('tax.vat_rate', 5.5),
            'custom_order_min_lead_days' => (int) Setting::get('custom_order.min_lead_days', 3),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'delivery_min_order_amount' => 'required|numeric|min:0',
            'delivery_free_threshold' => 'required|numeric|min:0',
            'delivery_default_fee' => 'required|numeric|min:0',
            'tax_vat_rate' => 'required|numeric|min:0',
            'custom_order_min_lead_days' => 'required|integer|min:1',
        ]);

        Setting::set('delivery.min_order_amount', $validated['delivery_min_order_amount'], 'decimal');
        Setting::set('delivery.free_threshold', $validated['delivery_free_threshold'], 'decimal');
        Setting::set('delivery.default_fee', $validated['delivery_default_fee'], 'decimal');
        Setting::set('tax.vat_rate', $validated['tax_vat_rate'], 'decimal');
        Setting::set('custom_order.min_lead_days', $validated['custom_order_min_lead_days'], 'integer');

        return response()->json(['message' => 'Paramètres mis à jour.']);
    }

    public function users()
    {
        return User::select('id', 'name', 'email', 'role', 'is_active', 'created_at')->get();
    }

    public function updateUserRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|in:admin,merchant,customer',
        ]);

        if ($user->id === $request->user()->id) {
            abort(422, 'Vous ne pouvez pas changer votre propre rôle.');
        }

        $user->update(['role' => $validated['role']]);

        return response()->json($user);
    }

    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'product_type_id' => 'required|exists:product_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
            'allergens' => 'nullable|array',
            'is_active' => 'boolean',
            'is_customizable' => 'boolean',
        ]);

        $merchant = \App\Models\Merchant::first();
        if (! $merchant) {
            abort(422, 'Aucune boutique trouvée.');
        }
        $validated['merchant_id'] = $merchant->id;

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        $baseSlug = $validated['slug'];
        $counter = 1;
        while (\App\Models\Product::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] = $baseSlug . '-' . $counter++;
        }

        $product = \App\Models\Product::create($validated);

        return response()->json($product->load('type', 'merchant'), 201);
    }

    public function updateProductFull(Request $request, \App\Models\Product $product)
    {
        $validated = $request->validate([
            'product_type_id' => 'sometimes|exists:product_types,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'low_stock_threshold' => 'sometimes|integer|min:0',
            'allergens' => 'nullable|array',
            'is_active' => 'boolean',
            'is_customizable' => 'boolean',
        ]);

        if (isset($validated['name']) && $validated['name'] !== $product->name) {
            $baseSlug = \Illuminate\Support\Str::slug($validated['name']);
            $slug = $baseSlug;
            $counter = 1;
            while (\App\Models\Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $validated['slug'] = $slug;
        }

        $product->update($validated);

        return response()->json($product->load('type', 'merchant'));
    }

    public function destroyProduct(\App\Models\Product $product)
    {
        $product->delete();
        return response()->json(['message' => 'Produit supprimé.']);
    }

    public function customOrders(Request $request)
    {
        $status = $request->query('status');

        $query = \App\Models\CustomOrder::with('merchant')->latest();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->get();
    }

    public function quoteCustomOrder(Request $request, \App\Models\CustomOrder $customOrder)
    {
        if ($customOrder->status !== 'pending_quote') {
            abort(422, 'Cette demande n’est plus en attente de devis.');
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

        // ✅ 1. Générer ET sauvegarder le PDF sur le disque
        $pdfService = new \App\Services\QuotePdfService();
        $pdfPath = $pdfService->generate($customOrder);
        $customOrder->update(['quote_pdf_path' => $pdfPath]);

        // ✅ 2. Notifier le client (le PDF sera relu depuis le disque)
        $notification = new \App\Notifications\CustomOrderQuoted($customOrder);
        if ($customOrder->user) {
            $customOrder->user->notify($notification);
        } else {
            \Illuminate\Support\Facades\Notification::route('mail', $customOrder->customer_email)
                ->notify($notification);
        }

        return response()->json($customOrder->fresh());
    }

    public function refuseCustomOrder(Request $request, \App\Models\CustomOrder $customOrder)
    {
        if ($customOrder->status !== 'pending_quote') {
            abort(422, 'Cette demande ne peut plus être refusée.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $customOrder->update([
            'merchant_response' => $validated['reason'],
            'status' => 'refused_by_merchant',
        ]);

        // Notifier le client
        $notification = new \App\Notifications\CustomOrderRefused($customOrder);
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
        $validated = $request->validate([
            'status' => 'required|in:completed,cancelled',
        ]);

        if ($customOrder->status !== 'accepted') {
            abort(422, 'Seule une demande acceptée peut être terminée ou annulée.');
        }

        $customOrder->update(['status' => $validated['status']]);

        // Notifier le client
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