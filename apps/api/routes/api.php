<?php

use App\Events\OrderStatusChanged;
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
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewReplyController;
use App\Http\Controllers\StripePaymentWebhookController;
use App\Http\Controllers\VendorOrderController;
use App\Http\Controllers\VendorDashboardController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminVendorController;
use App\Events\TestBroadcastEvent;

// TEMPORARY — reports the exact broadcasting config in use and any
// exception raised while publishing. Delete once resolved.
Route::get('/v1/debug/broadcast-test', function () {
    try {
        broadcast(new TestBroadcastEvent());

        return response()->json([
            'fired' => true,
            'broadcast_driver' => config('broadcasting.default'),
            'reverb_connection_key' => config('broadcasting.connections.reverb.key'),
            'reverb_connection_host' => config('broadcasting.connections.reverb.options.host'),
            'reverb_connection_port' => config('broadcasting.connections.reverb.options.port'),
            'reverb_connection_scheme' => config('broadcasting.connections.reverb.options.scheme'),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'fired' => false,
            'error' => $e->getMessage(),
            'exception_class' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
});

Route::get('/v1/ping', PingController::class);

Route::prefix('v1/auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware(['auth:sanctum', 'vendor'])->group(function () {
    Route::prefix('v1/vendor/products')->group(function () {
        Route::get('/', [VendorProductController::class, 'index']);
        Route::post('/', [VendorProductController::class, 'store']);
        Route::get('/{product}', [VendorProductController::class, 'show']);
        Route::put('/{product}', [VendorProductController::class, 'update']);
        Route::delete('/{product}', [VendorProductController::class, 'destroy']);
        Route::post('/{product}/images', [VendorProductImageController::class, 'store']);
        Route::delete('/{product}/images/{image}', [VendorProductImageController::class, 'destroy']);
        Route::put('/{product}/images/reorder', [VendorProductImageController::class, 'reorder']);
    });

    Route::prefix('v1/vendor/coupons')->group(function () {
        Route::get('/', [VendorCouponController::class, 'index']);
        Route::post('/', [VendorCouponController::class, 'store']);
        Route::put('/{coupon}', [VendorCouponController::class, 'update']);
        Route::delete('/{coupon}', [VendorCouponController::class, 'destroy']);
    });

    Route::prefix('v1/vendor/orders')->group(function () {
        Route::get('/', [VendorOrderController::class, 'index']);
        Route::get('/{order}', [VendorOrderController::class, 'show']);
        Route::put('/{order}', [VendorOrderController::class, 'update']);
    });

    Route::prefix('v1/vendor/dashboard')->group(function () {
        Route::get('/stats', [VendorDashboardController::class, 'stats']);
        Route::get('/revenue-chart', [VendorDashboardController::class, 'revenueChart']);
        Route::get('/low-stock', [VendorDashboardController::class, 'lowStock']);
        Route::get('/analytics', [VendorDashboardController::class, 'analytics']);
    });

    Route::post('/v1/reviews/{review}/reply', [ReviewReplyController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/v1/vendor/onboard', [VendorController::class, 'onboard']);
    Route::get('/v1/vendor/status', [VendorController::class, 'status']);
    Route::post('/v1/order-items/{orderItem}/reviews', [ReviewController::class, 'store']);
    Route::put('/v1/reviews/{review}', [ReviewController::class, 'update']);
    Route::get('/v1/products/{product:slug}/review-eligibility', [ReviewController::class, 'reviewEligibility']);
});

Route::post('/v1/webhooks/stripe', [StripeWebhookController::class, 'handle']);

Route::get('/v1/categories', [CategoryController::class, 'index']);
Route::get('/v1/products/featured', [ProductController::class, 'featured']); // must precede {product:slug}
Route::get('/v1/products/{product:slug}', [ProductController::class, 'show']);
Route::get('/v1/products', [ProductController::class, 'index']);
Route::get('/v1/products/{product:slug}/reviews', [ReviewController::class, 'index']);

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

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/v1/checkout', [CheckoutController::class, 'store']);
    Route::get('/v1/checkout/{paymentIntentId}/status', [CheckoutController::class, 'status']);
    Route::get('/v1/orders', [OrderController::class, 'index']);
    Route::get('/v1/orders/{order}', [OrderController::class, 'show']);
    Route::get('/v1/notifications', [NotificationController::class, 'index']);
    Route::post('/v1/notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('v1/admin')->group(function () {
    Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);

    Route::get('/vendors', [AdminVendorController::class, 'index']);
    Route::post('/vendors/{vendor}/suspend', [AdminVendorController::class, 'suspend']);
    Route::post('/vendors/{vendor}/reactivate', [AdminVendorController::class, 'reactivate']);

    Route::get('/products', [AdminProductController::class, 'index']);
    Route::post('/products/{product}/flag', [AdminProductController::class, 'flag']);
    Route::post('/products/{product}/unflag', [AdminProductController::class, 'unflag']);
    Route::post('/products/{product}/deactivate', [AdminProductController::class, 'deactivate']);

    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::post('/orders/{order}/refund', [AdminOrderController::class, 'refund']);
    Route::post('/orders/{order}/release-funds', [AdminOrderController::class, 'releaseFunds']);
});

Route::post('/v1/webhooks/stripe-payments', [StripePaymentWebhookController::class, 'handle']);

