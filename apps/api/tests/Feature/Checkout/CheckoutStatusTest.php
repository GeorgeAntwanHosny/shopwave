<?php

namespace Tests\Feature\Checkout;

use App\Models\CheckoutSession;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckoutStatusTest extends TestCase
{
    public function test_it_returns_pending_status_before_webhook_processing(): void
    {
        $user = User::factory()->create();
        CheckoutSession::factory()->create(['user_id' => $user->id, 'stripe_payment_intent_id' => 'pi_status_test', 'status' => 'pending']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/checkout/pi_status_test/status');

        $response->assertStatus(200)->assertJsonPath('data.status', 'pending');
    }

    public function test_another_users_checkout_session_is_not_visible(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        CheckoutSession::factory()->create(['user_id' => $owner->id, 'stripe_payment_intent_id' => 'pi_owner_only']);
        Sanctum::actingAs($other);

        $response = $this->getJson('/api/v1/checkout/pi_owner_only/status');

        $response->assertStatus(404);
    }
}
