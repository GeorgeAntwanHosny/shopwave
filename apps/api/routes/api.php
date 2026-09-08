<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PingController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\VendorProductController;
use App\Http\Controllers\VendorProductImageController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\VendorCouponController;

Route::get('/v1/ping', PingController::class);

Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/v1/vendor/onboard', [VendorController::class, 'onboard']);
    Route::get('/v1/vendor/status', [VendorController::class, 'status']);
});

Route::post('/v1/webhooks/stripe', [StripeWebhookController::class, 'handle']);


Route::get('/v1/categories', [CategoryController::class, 'index']);
Route::get('/v1/products/featured', [ProductController::class, 'featured']); // must precede {product:slug}
Route::get('/v1/products/{product:slug}', [ProductController::class, 'show']);
Route::get('/v1/products', [ProductController::class, 'index']);

Route::middleware(['auth:sanctum', 'vendor'])->prefix('v1/vendor/products')->group(function () {
    Route::get('/', [VendorProductController::class, 'index']);
    Route::post('/', [VendorProductController::class, 'store']);
    Route::get('/{product}', [VendorProductController::class, 'show']);
    Route::put('/{product}', [VendorProductController::class, 'update']);
    Route::delete('/{product}', [VendorProductController::class, 'destroy']);
    Route::post('/{product}/images', [VendorProductImageController::class, 'store']);
    Route::delete('/{product}/images/{image}', [VendorProductImageController::class, 'destroy']);
    Route::put('/{product}/images/reorder', [VendorProductImageController::class, 'reorder']);
});

// Cart — open to guests and authenticated users alike; identity is
// resolved per-request via optional Sanctum auth + an X-Cart-Token header,
// deliberately NOT behind auth:sanctum (which would 401 guests).
Route::prefix('v1/cart')->group(function () {
    Route::get('/', [CartController::class, 'show']);
    Route::post('/items', [CartController::class, 'storeItem']);
    Route::put('/items/{product}', [CartController::class, 'updateItem']);
    Route::delete('/items/{product}', [CartController::class, 'destroyItem']);
    Route::post('/coupon', [CartController::class, 'applyCoupon']);
    Route::delete('/coupon', [CartController::class, 'destroyCoupon']);
});

Route::middleware(['auth:sanctum', 'vendor'])->prefix('v1/vendor/coupons')->group(function () {
    Route::get('/', [VendorCouponController::class, 'index']);
    Route::post('/', [VendorCouponController::class, 'store']);
    Route::put('/{coupon}', [VendorCouponController::class, 'update']);
    Route::delete('/{coupon}', [VendorCouponController::class, 'destroy']);
});
