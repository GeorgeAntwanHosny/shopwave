<?php

namespace Tests\Feature\Vendor;

use App\Models\User;
use App\Services\StripeConnectService;
use Laravel\Sanctum\Sanctum;
use Stripe\V2\Core\Account;
use Stripe\V2\Core\AccountLink;
use Tests\TestCase;

class VendorOnboardTest extends TestCase
{
    public function test_authenticated_user_can_start_vendor_onboarding(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $account = Account::constructFrom(['id' => 'acct_test123']);
        $accountLink = AccountLink::constructFrom(['url' => 'https://connect.stripe.com/setup/test123']);

        $this->mock(StripeConnectService::class, function ($mock) use ($account, $accountLink) {
            $mock->shouldReceive('createConnectedAccount')->once()->andReturn($account);
            $mock->shouldReceive('createOnboardingLink')->once()->andReturn($accountLink);
        });

        $response = $this->postJson('/api/v1/vendor/onboard', ['shop_name' => "Jane's Boutique"]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.vendor.stripe_account_id', 'acct_test123')
            ->assertJsonPath('data.onboarding_url', 'https://connect.stripe.com/setup/test123');

        $this->assertDatabaseHas('vendors', [
            'user_id' => $user->id,
            'shop_name' => "Jane's Boutique",
            'stripe_account_id' => 'acct_test123',
        ]);
    }

    public function test_guest_cannot_start_vendor_onboarding(): void
    {
        $response = $this->postJson('/api/v1/vendor/onboard', ['shop_name' => 'Test Shop']);

        $response->assertStatus(401);
    }
}
