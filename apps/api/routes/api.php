<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PingController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\AuthController;

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
