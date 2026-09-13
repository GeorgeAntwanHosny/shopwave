<?php

namespace Tests\Feature\Review;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewStoreTest extends TestCase
{
    public function test_user_can_review_a_delivered_order_item(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'fulfillment_status' => 'delivered']);
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/order-items/{$orderItem->id}/reviews", [
            'rating' => 5,
            'comment' => 'Great product!',
        ]);
        $response->assertStatus(201)->assertJsonPath('data.rating', 5);
        $this->assertDatabaseHas('reviews', ['order_item_id' => $orderItem->id, 'rating' => 5]);
    }

    public function test_cannot_review_an_item_that_has_not_been_delivered(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'fulfillment_status' => 'processing']);
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/order-items/{$orderItem->id}/reviews", ['rating' => 4]);

        $response->assertStatus(422);
    }

    public function test_cannot_review_the_same_item_twice(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'fulfillment_status' => 'delivered']);
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);
        Review::factory()->create(['order_item_id' => $orderItem->id, 'product_id' => $orderItem->product_id]);
        Sanctum::actingAs($user);

        $response = $this->postJson("/api/v1/order-items/{$orderItem->id}/reviews", ['rating' => 4]);

        $response->assertStatus(422);
    }

    public function test_non_owner_cannot_review_the_item(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $owner->id, 'fulfillment_status' => 'delivered']);
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);
        Sanctum::actingAs($other);

        $response = $this->postJson("/api/v1/order-items/{$orderItem->id}/reviews", ['rating' => 4]);

        $response->assertStatus(403);
    }
}
