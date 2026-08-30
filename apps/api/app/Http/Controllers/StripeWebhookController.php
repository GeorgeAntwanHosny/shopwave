<?php

namespace App\Http\Controllers;

use App\Actions\Vendor\HandleStripeAccountUpdatedAction;
use App\Http\Responses\ApiResponse;
use App\Services\StripeConnectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    /**
     * Handle v2 thin-event webhook notifications. We only act on
     * v2.core.account[requirements].updated — the event Stripe recommends
     * listening to for onboarding/requirements state changes.
     *
     * @see https://docs.stripe.com/connect/marketplace/tasks/onboard
     */
    public function handle(
        Request $request,
        StripeConnectService $stripeConnectService,
        HandleStripeAccountUpdatedAction $action
    ): JsonResponse {
        try {
            $eventNotification = $stripeConnectService->parseWebhookEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', '')
            );
        } catch (\Exception $e) {
            return ApiResponse::error('Invalid webhook signature.', null, 400);
        }

        if ($eventNotification->type === 'v2.core.account[requirements].updated') {
            $accountId = $eventNotification->relatedObject->id ?? null;

            if ($accountId) {
                $account = $stripeConnectService->fetchAccountStatus($accountId);
                $action->execute($account);
            }
        }

        return ApiResponse::success(null, 'Webhook handled.');
    }
}
