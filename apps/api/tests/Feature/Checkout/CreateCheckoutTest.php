<?php

namespace Tests\Feature\Checkout;

use App\Models\Product;
use App\Models\User;
use App\Services\StripePaymentService;
use Illuminate\Support\Facades\Redis;
use Laravel\Sanctum\Sanctum;
use Stripe\PaymentIntent;
use Tests\TestCase;

class CreateCheckoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushdb();
    }

    public function test_authenticated_user_can_start_checkout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $product = Product::factory()->create(['price' => 25, 'stock_quantity' => 10]);

        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2]);

        $paymentIntent = PaymentIntent::constructFrom(['id' => 'pi_test123', 'client_secret' => 'pi_test123_secret_abc']);
        $this->mock(StripePaymentService::class, function ($mock) use ($paymentIntent) {
            $mock->shouldReceive('createPaymentIntent')->once()->andReturn($paymentIntent);
        });

        $response = $this->postJson('/api/v1/checkout');

        $response->assertStatus(201)
            ->assertJsonPath('data.payment_intent_id', 'pi_test123')
            ->assertJsonPath('data.client_secret', 'pi_test123_secret_abc');

        $this->assertDatabaseHas('checkout_sessions', ['stripe_payment_intent_id' => 'pi_test123', 'status' => 'pending']);
    }

    public function test_checkout_rejects_an_empty_cart(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/checkout');

        $response->assertStatus(422);
    }

    public function test_guest_cannot_start_checkout(): void
    {
        $response = $this->postJson('/api/v1/checkout');

        $response->assertStatus(401);
    }
}
