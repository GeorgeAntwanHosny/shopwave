<?php

namespace Tests\Feature\Vendor;

use App\Actions\Vendor\HandleStripeAccountUpdatedAction;
use App\Models\User;
use App\Models\Vendor;
use Stripe\V2\Core\Account;
use Tests\TestCase;

class HandleStripeAccountUpdatedActionTest extends TestCase
{
    public function test_it_marks_onboarding_complete_when_both_capabilities_are_active(): void
    {
        $vendor = Vendor::factory()->create([
            'user_id' => User::factory(),
            'stripe_account_id' => 'acct_test123',
            'stripe_onboarding_complete' => false,
        ]);

        $account = Account::constructFrom([
            'id' => 'acct_test123',
            'configuration' => [
                'merchant' => ['capabilities' => ['card_payments' => ['status' => 'active']]],
                'recipient' => ['capabilities' => ['stripe_balance' => ['stripe_transfers' => ['status' => 'active']]]],
            ],
        ]);

        (new HandleStripeAccountUpdatedAction(new \App\Services\StripeConnectService()))->execute($account);

        $this->assertTrue($vendor->fresh()->stripe_onboarding_complete);
    }

    public function test_it_leaves_onboarding_incomplete_when_a_capability_is_inactive(): void
    {
        $vendor = Vendor::factory()->create([
            'user_id' => User::factory(),
            'stripe_account_id' => 'acct_test456',
            'stripe_onboarding_complete' => false,
        ]);

        $account = Account::constructFrom([
            'id' => 'acct_test456',
            'configuration' => [
                'merchant' => ['capabilities' => ['card_payments' => ['status' => 'pending']]],
                'recipient' => ['capabilities' => ['stripe_balance' => ['stripe_transfers' => ['status' => 'active']]]],
            ],
        ]);

        (new HandleStripeAccountUpdatedAction(new \App\Services\StripeConnectService()))->execute($account);

        $this->assertFalse($vendor->fresh()->stripe_onboarding_complete);
    }

    public function test_it_does_nothing_for_an_unknown_account_id(): void
    {
        $account = Account::constructFrom(['id' => 'acct_does_not_exist']);

        (new HandleStripeAccountUpdatedAction(new \App\Services\StripeConnectService()))->execute($account);

        $this->assertTrue(true); // no exception thrown
    }
}
