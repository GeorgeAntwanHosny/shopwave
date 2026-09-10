<?php

namespace App\Http\Controllers;

use App\Actions\Checkout\CreateVendorTransfersAction;
use App\Actions\Checkout\ProcessSuccessfulCheckoutAction;
use App\Actions\Checkout\SendOrderConfirmationEmailAction;
use App\Http\Responses\ApiResponse;
use App\Models\CheckoutSession;
use App\Services\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripePaymentWebhookController extends Controller
{
    /**
     * Classic v1 snapshot-event webhook for payment_intent.* events — a
     * deliberately different verification path (and signing secret) than
     * StripeWebhookController, which handles v2 thin events for Connect
     * account lifecycle. See StripePaymentService::constructWebhookEvent().
     */
    public function handle(
        Request $request,
        StripePaymentService $stripePaymentService,
        ProcessSuccessfulCheckoutAction $processAction,
        CreateVendorTransfersAction $transfersAction,
        SendOrderConfirmationEmailAction $emailAction
    ): JsonResponse {
        try {
            $event = $stripePaymentService->constructWebhookEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', '')
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Invalid webhook signature.', null, 400);
        }

        $paymentIntent = $event->data->object;
        $checkoutSession = CheckoutSession::where('stripe_payment_intent_id', $paymentIntent->id)->first();

        if (! $checkoutSession) {
            return ApiResponse::success(null, 'Webhook handled.');
        }

        if ($event->type === 'payment_intent.succeeded') {
            $orders = $processAction->execute($checkoutSession);
            $transfersAction->execute($orders);
            $emailAction->execute($orders);
        } elseif ($event->type === 'payment_intent.payment_failed') {
            $checkoutSession->update(['status' => 'failed']);
        }

        return ApiResponse::success(null, 'Webhook handled.');
    }
}
