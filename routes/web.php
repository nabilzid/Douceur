<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\CustomOrderController;
use App\Http\Controllers\Api\MerchantController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Middleware\EnsureUserIsMerchant;
use App\Http\Middleware\EnsureUserIsAdmin;

// Routes publiques (auth)
Route::prefix('api')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('web');
    Route::get('/me', fn (\Illuminate\Http\Request $r) => response()->json(['user' => auth('sanctum')->user()]));

    // Routes client authentifié
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/my-orders', function (\Illuminate\Http\Request $request) {
            return $request->user()->orders()->with('items')->latest()->get();
        });

        Route::put('/custom-orders/{reference}/respond', [CustomOrderController::class, 'respond']);

        Route::put('/user/profile', [AuthController::class, 'updateProfile']);

        Route::get('/user/addresses', [AddressController::class, 'index']);
        Route::post('/user/addresses', [AddressController::class, 'store']);
        Route::put('/user/addresses/{address}', [AddressController::class, 'update']);
        Route::delete('/user/addresses/{address}', [AddressController::class, 'destroy']);
    });
});

// Routes marchand (back-office) — admin + merchant
// Le pâtissier peut : voir les demandes, marquer comme terminée
Route::prefix('api/merchant')->middleware(['auth:sanctum', EnsureUserIsMerchant::class])->group(function () {
    Route::get('/dashboard', [MerchantController::class, 'dashboard']);
    Route::get('/products', [MerchantController::class, 'products']);
    Route::put('/products/{product}', [MerchantController::class, 'updateProduct']);
    Route::post('/products/{product}/stock', [MerchantController::class, 'adjustStock']);
    Route::get('/orders', [MerchantController::class, 'orders']);
    Route::put('/orders/{order}/status', [MerchantController::class, 'updateOrderStatus']);

    // Sur mesure : lecture seule + marquer terminée
    Route::get('/custom-orders', [MerchantController::class, 'customOrders']);
    Route::put('/custom-orders/{customOrder}/status', [MerchantController::class, 'updateCustomOrderStatus']);
});

// Routes admin (commercial + paramètres + utilisateurs)
Route::prefix('api/admin')->middleware(['auth:sanctum', EnsureUserIsAdmin::class])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/settings', [AdminController::class, 'settings']);
    Route::put('/settings', [AdminController::class, 'updateSettings']);
    Route::get('/users', [AdminController::class, 'users']);
    Route::put('/users/{user}/role', [AdminController::class, 'updateUserRole']);
    Route::post('/products', [AdminController::class, 'storeProduct']);
    Route::put('/products/{product}', [AdminController::class, 'updateProductFull']);
    Route::delete('/products/{product}', [AdminController::class, 'destroyProduct']);
    Route::get('/merchants', fn () => \App\Models\Merchant::with('user')->get());

    // ✅ Sur mesure : commercial (devis, refus, annulation)
    Route::get('/custom-orders', [AdminController::class, 'customOrders']);
    Route::put('/custom-orders/{customOrder}/quote', [AdminController::class, 'quoteCustomOrder']);
    Route::put('/custom-orders/{customOrder}/refuse', [AdminController::class, 'refuseCustomOrder']);
    Route::put('/custom-orders/{customOrder}/status', [AdminController::class, 'updateCustomOrderStatus']);
});


// Catch-all pour Vue Router (en dernier)
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '^(?!api|sanctum).*$');
