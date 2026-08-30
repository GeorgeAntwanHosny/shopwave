<?php

namespace App\Actions\Vendor;

use App\Models\Vendor;
use App\Services\StripeConnectService;

class HandleStripeAccountUpdatedAction
{
    public function __construct(protected StripeConnectService $stripeConnectService)
    {
    }

    public function execute(\Stripe\V2\Core\Account $account): void
    {
        $vendor = Vendor::where('stripe_account_id', $account->id)->first();

        if (! $vendor) {
            return;
        }

        $vendor->update([
            'stripe_onboarding_complete' => $this->stripeConnectService->isOnboardingComplete($account),
        ]);
    }
}
