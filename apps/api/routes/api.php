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
