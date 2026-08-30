<?php

namespace App\Http\Controllers;

use App\Actions\Vendor\BecomeVendorAction;
use App\Http\Requests\Vendor\BecomeVendorRequest;
use App\Http\Responses\ApiResponse;
use App\Services\StripeConnectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function onboard(BecomeVendorRequest $request, BecomeVendorAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $request->validated()['shop_name']);

        return ApiResponse::success($result, 'Vendor onboarding started.', 201);
    }

    public function status(Request $request, StripeConnectService $stripeConnectService): JsonResponse
    {
        $vendor = $request->user()->vendor;

        if (! $vendor) {
            return ApiResponse::error('This account is not registered as a vendor.', null, 404);
        }

        // Don't just trust the cached DB flag — webhooks can be delayed or
        // missed entirely (in local dev, the CLI listener has to be running
        // at the exact moment onboarding completes). Reconcile with
        // Stripe's live state every time this is hit: it's one cheap API
        // call, and it's exactly the moment the user is waiting to know.
        $account = $stripeConnectService->fetchAccountStatus($vendor->stripe_account_id);
        $isComplete = $stripeConnectService->isOnboardingComplete($account);

        if ($isComplete !== $vendor->stripe_onboarding_complete) {
            $vendor->update(['stripe_onboarding_complete' => $isComplete]);
        }

        $onboardingUrl = $isComplete
            ? null
            : $stripeConnectService->createOnboardingLink($vendor->stripe_account_id)->url;

        return ApiResponse::success([
            'vendor' => $vendor->fresh(),
            'onboarding_url' => $onboardingUrl,
        ], 'Vendor status retrieved.');
    }
}
