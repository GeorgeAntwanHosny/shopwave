<?php

namespace App\Actions\Vendor;

use App\Models\User;
use App\Models\Vendor;
use App\Services\StripeConnectService;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BecomeVendorAction
{
    public function __construct(protected StripeConnectService $stripeConnectService)
    {
    }

    /**
     * @return array{vendor: Vendor, onboarding_url: string}
     */
    public function execute(User $user, string $shopName): array
    {
        if ($user->vendor()->exists()) {
            throw ValidationException::withMessages([
                'shop_name' => ['This account is already registered as a vendor.'],
            ]);
        }

        $stripeAccount = $this->stripeConnectService->createConnectedAccount($user->email, $shopName);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => $shopName,
            'shop_slug' => $this->uniqueSlug($shopName),
            'stripe_account_id' => $stripeAccount->id,
            'stripe_onboarding_complete' => false,
        ]);

        $onboardingLink = $this->stripeConnectService->createOnboardingLink($stripeAccount->id);

        return [
            'vendor' => $vendor,
            'onboarding_url' => $onboardingLink->url,
        ];
    }

    protected function uniqueSlug(string $shopName): string
    {
        $base = Str::slug($shopName);
        $slug = $base;
        $suffix = 1;

        while (Vendor::where('shop_slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
