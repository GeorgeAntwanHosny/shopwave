<?php

namespace Tests\Feature\Vendor;

use App\Models\User;
use App\Models\Vendor;
use App\Services\StripeConnectService;
use Laravel\Sanctum\Sanctum;
use Stripe\V2\Core\Account;
use Stripe\V2\Core\AccountLink;
use Tests\TestCase;

class VendorStatusTest extends TestCase
{
    public function test_incomplete_vendor_receives_a_fresh_onboarding_link(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create([
            'user_id' => $user->id,
            'stripe_account_id' => 'acct_test123',
            'stripe_onboarding_complete' => false,
        ]);
        Sanctum::actingAs($user);

        $account = Account::constructFrom(['id' => 'acct_test123']);
        $accountLink = AccountLink::constructFrom(['url' => 'https://connect.stripe.com/setup/resume123']);

        $this->mock(StripeConnectService::class, function ($mock) use ($account, $accountLink) {
            $mock->shouldReceive('fetchAccountStatus')->once()->andReturn($account);
            $mock->shouldReceive('isOnboardingComplete')->once()->andReturn(false);
            $mock->shouldReceive('createOnboardingLink')->once()->andReturn($accountLink);
        });

        $response = $this->getJson('/api/v1/vendor/status');

        $response->assertStatus(200)
            ->assertJsonPath('data.vendor.id', $vendor->id)
            ->assertJsonPath('data.onboarding_url', 'https://connect.stripe.com/setup/resume123');
    }

    public function test_status_check_self_heals_when_stripe_reports_onboarding_now_complete(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create([
            'user_id' => $user->id,
            'stripe_account_id' => 'acct_test123',
            'stripe_onboarding_complete' => false, // stale — webhook never arrived
        ]);
        Sanctum::actingAs($user);

        $account = Account::constructFrom(['id' => 'acct_test123']);

        $this->mock(StripeConnectService::class, function ($mock) use ($account) {
            $mock->shouldReceive('fetchAccountStatus')->once()->andReturn($account);
            $mock->shouldReceive('isOnboardingComplete')->once()->andReturn(true);
        });

        $response = $this->getJson('/api/v1/vendor/status');

        $response->assertStatus(200)
            ->assertJsonPath('data.vendor.stripe_onboarding_complete', true)
            ->assertJsonPath('data.onboarding_url', null);

        $this->assertTrue($vendor->fresh()->stripe_onboarding_complete);
    }

    public function test_non_vendor_receives_404(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/vendor/status');

        $response->assertStatus(404)->assertJson(['success' => false]);
    }

    public function test_guest_cannot_view_vendor_status(): void
    {
        $response = $this->getJson('/api/v1/vendor/status');

        $response->assertStatus(401);
    }
}
