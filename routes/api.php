<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\DeliverySlot;
use App\Models\EventPackage;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CustomOrderController;
use App\Http\Controllers\Api\PaymentController;

/*
|--------------------------------------------------------------------------
| Routes publiques (aucune authentification)
|--------------------------------------------------------------------------
*/

// Catalogue
Route::get('/products', fn () => Product::where('is_active', true)->with('type')->get());
Route::get('/products/{slug}', fn (string $slug) => Product::where('slug', $slug)->where('is_active', true)->with('type')->firstOrFail());
Route::get('/product-types', fn () => ProductType::orderBy('sort_order')->get());

// Créneaux de livraison / retrait
Route::get('/delivery-slots', function (\Illuminate\Http\Request $request) {
    $type = $request->query('type', 'delivery');
    return DeliverySlot::where('type', $type)
        ->where('is_active', true)
        ->where('date', '>=', now()->toDateString())
        ->orderBy('date')
        ->orderBy('start_time')
        ->get();
});

// Formules événementielles
Route::get('/event-packages', fn () => EventPackage::where('is_active', true)->with('merchant')->get());

// Commandes standard (invité autorisé)
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{reference}', [OrderController::class, 'show']);

// Commandes sur mesure
// ⚠️ ORDRE CRITIQUE : /mine AVANT /{reference}
Route::post('/custom-orders', [CustomOrderController::class, 'store']);
Route::get('/custom-orders/mine', [CustomOrderController::class, 'mine'])->middleware('auth:sanctum');
Route::get('/custom-orders/{reference}', [CustomOrderController::class, 'show']);

// Webhook Stripe (public, signé par Stripe, pas de CSRF)
Route::post('/stripe/webhook', [PaymentController::class, 'webhook']);

/*
|--------------------------------------------------------------------------
| État d'authentification (retourne l'utilisateur ou null, jamais 401)
|--------------------------------------------------------------------------
*/
Route::get('/me', fn (\Illuminate\Http\Request $request) => response()->json([
    'user' => auth('sanctum')->user(),
]));

/*
|--------------------------------------------------------------------------
| Routes protégées (authentification requise)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Commandes du client connecté
    Route::get('/my-orders', fn (\Illuminate\Http\Request $request) => $request->user()->orders()->with('items')->latest()->get());

    // Réponse du client à un devis (accepter / refuser)
    Route::put('/custom-orders/{reference}/respond', [CustomOrderController::class, 'respond']);

    // Paiement Stripe d'une commande
   
});

Route::post('/orders/{reference}/checkout', [PaymentController::class, 'checkout']);
Route::get('/orders/{reference}/payment-status', [PaymentController::class, 'status']);

Route::get('/settings/delivery', function () {
    return response()->json([
        'min_order_amount' => (float) \App\Models\Setting::get('delivery.min_order_amount', 15),
        'free_threshold' => (float) \App\Models\Setting::get('delivery.free_threshold', 30),
        'default_fee' => (float) \App\Models\Setting::get('delivery.default_fee', 5),
    ]);
});
    
Route::post('/custom-orders/{reference}/respond-guest', [CustomOrderController::class, 'respondAsGuest']);

Route::get('/notifications', function (\Illuminate\Http\Request $request) {
    return $request->user()->notifications()->latest()->limit(20)->get();
})->middleware('auth:sanctum');

Route::put('/notifications/{id}/read', function (\Illuminate\Http\Request $request, string $id) {
    $notification = $request->user()->notifications()->findOrFail($id);
    $notification->markAsRead();
    return response()->json(['ok' => true]);
})->middleware('auth:sanctum');

Route::put('/notifications/read-all', function (\Illuminate\Http\Request $request) {
    $request->user()->unreadNotifications->markAsRead();
    return response()->json(['ok' => true]);
})->middleware('auth:sanctum');


Route::post('/orders/track', [OrderController::class, 'track']);
Route::post('/custom-orders/track', [CustomOrderController::class, 'track']);
Route::put('/custom-orders/{reference}/respond', [CustomOrderController::class, 'respond'])
    ->middleware('auth:sanctum');
    
// Téléchargement du devis PDF (connecté ou invité signé)
Route::get('/custom-orders/{reference}/quote-pdf', [CustomOrderController::class, 'downloadQuotePdf'])
    ->name('custom-orders.quote-pdf');

// ✅ Accepter/refuser via un lien signé (invité)
Route::put('/custom-orders/{reference}/respond-guest', [CustomOrderController::class, 'respondAsGuest'])
    ->name('custom-orders.respond-guest');

Route::get('/custom-orders/{reference}/invoice/{type}', [CustomOrderController::class, 'downloadInvoice'])
    ->name('custom-orders.invoice');
    