<?php

namespace App\Http\Controllers;

use App\Actions\Checkout\CreateCheckoutAction;
use App\Http\Controllers\Concerns\ResolvesCartIdentity;
use App\Http\Responses\ApiResponse;
use App\Models\CheckoutSession;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use ResolvesCartIdentity;

    public function store(Request $request, CreateCheckoutAction $action, CartService $cartService): JsonResponse
    {
        $identity = $this->resolveCart($request, $cartService);
        $result = $action->execute($request->user(), $identity['key']);

        return ApiResponse::success($result, 'Checkout started.', 201);
    }

    public function status(string $paymentIntentId, Request $request): JsonResponse
    {
        $checkoutSession = CheckoutSession::where('stripe_payment_intent_id', $paymentIntentId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $checkoutSession) {
            return ApiResponse::error('Checkout not found.', null, 404);
        }

        $orders = $checkoutSession->status === 'completed'
            ? $checkoutSession->orders()->with(['items', 'vendor'])->get()
            : collect();

        return ApiResponse::success([
            'status' => $checkoutSession->status,
            'orders' => $orders,
        ], 'Checkout status retrieved.');
    }
}
