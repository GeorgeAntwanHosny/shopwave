<?php

namespace Tests\Feature\Vendor;

use App\Models\User;
use App\Models\Vendor;
use App\Services\StripeConnectService;
use Stripe\V2\Core\Account;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    public function test_webhook_rejects_invalid_signature(): void
    {
        $response = $this->call(
            'POST',
            '/api/v1/webhooks/stripe',
            [],
            [],
            [],
            ['HTTP_Stripe-Signature' => 'invalid', 'CONTENT_TYPE' => 'application/json'],
            json_encode(['type' => 'v2.core.account[requirements].updated'])
        );

        $response->assertStatus(400)->assertJson(['success' => false]);
    }

    public function test_account_requirements_updated_event_refreshes_vendor_status(): void
    {
        $notification = (object) [
            'type' => 'v2.core.account[requirements].updated',
            'relatedObject' => (object) ['id' => 'acct_test123'],
        ];

        $account = Account::constructFrom([
            'id' => 'acct_test123',
            'configuration' => [
                'merchant' => ['capabilities' => ['card_payments' => ['status' => 'active']]],
                'recipient' => ['capabilities' => ['stripe_balance' => ['stripe_transfers' => ['status' => 'active']]]],
            ],
        ]);

        $this->mock(StripeConnectService::class, function ($mock) use ($notification, $account) {
            $mock->shouldReceive('parseWebhookEvent')->once()->andReturn($notification);
            $mock->shouldReceive('fetchAccountStatus')->once()->with('acct_test123')->andReturn($account);
            $mock->shouldReceive('isOnboardingComplete')->once()->with($account)->andReturn(true);
        });

        $vendor = Vendor::factory()->create([
            'user_id' => User::factory(),
            'stripe_account_id' => 'acct_test123',
            'stripe_onboarding_complete' => false,
        ]);

        $response = $this->postJson('/api/v1/webhooks/stripe', [], [
            'Stripe-Signature' => 'irrelevant-because-service-is-mocked',
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertTrue($vendor->fresh()->stripe_onboarding_complete);
    }
}
