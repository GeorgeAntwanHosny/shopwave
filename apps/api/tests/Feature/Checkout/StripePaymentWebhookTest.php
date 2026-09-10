<?php

namespace Tests\Feature\Checkout;

use App\Models\CheckoutSession;
use App\Services\StripePaymentService;
use Stripe\PaymentIntent;
use Tests\TestCase;

class StripePaymentWebhookTest extends TestCase
{
    public function test_it_rejects_an_invalid_signature(): void
    {
        $response = $this->call(
            'POST',
            '/api/v1/webhooks/stripe-payments',
            [], [], [],
            ['HTTP_Stripe-Signature' => 'invalid', 'CONTENT_TYPE' => 'application/json'],
            json_encode(['type' => 'payment_intent.succeeded'])
        );

        $response->assertStatus(400);
    }

    public function test_payment_intent_succeeded_processes_the_checkout(): void
    {
        $checkoutSession = CheckoutSession::factory()->create([
            'stripe_payment_intent_id' => 'pi_webhook_test',
            'status' => 'pending',
        ]);

        $event = (object) [
            'type' => 'payment_intent.succeeded',
            'data' => (object) ['object' => PaymentIntent::constructFrom(['id' => 'pi_webhook_test'])],
        ];

        $this->mock(StripePaymentService::class, function ($mock) use ($event) {
            $mock->shouldReceive('constructWebhookEvent')->once()->andReturn($event);
        });

        $response = $this->postJson('/api/v1/webhooks/stripe-payments', [], ['Stripe-Signature' => 'irrelevant-mocked']);

        $response->assertStatus(200);
        $this->assertEquals('completed', $checkoutSession->fresh()->status);
    }

    public function test_payment_intent_failed_marks_the_checkout_failed(): void
    {
        $checkoutSession = CheckoutSession::factory()->create([
            'stripe_payment_intent_id' => 'pi_failed_test',
            'status' => 'pending',
        ]);

        $event = (object) [
            'type' => 'payment_intent.payment_failed',
            'data' => (object) ['object' => PaymentIntent::constructFrom(['id' => 'pi_failed_test'])],
        ];

        $this->mock(StripePaymentService::class, function ($mock) use ($event) {
            $mock->shouldReceive('constructWebhookEvent')->once()->andReturn($event);
        });

        $response = $this->postJson('/api/v1/webhooks/stripe-payments', [], ['Stripe-Signature' => 'irrelevant-mocked']);

        $response->assertStatus(200);
        $this->assertEquals('failed', $checkoutSession->fresh()->status);
    }
}
