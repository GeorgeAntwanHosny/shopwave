<?php

namespace Tests\Feature\Checkout;

use App\Models\Order;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderShowTest extends TestCase
{
    public function test_owner_can_view_their_order(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200)->assertJsonPath('data.id', $order->id);
    }

    public function test_non_owner_cannot_view_the_order(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $owner->id]);
        Sanctum::actingAs($other);

        $response = $this->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(403);
    }
}
