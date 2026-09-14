<?php

namespace Tests\Feature\Checkout;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderIndexTest extends TestCase
{
    public function test_user_sees_only_their_own_orders(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $mine = Order::factory()->create(['user_id' => $user->id]);
        Order::factory()->create(['user_id' => $other->id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/orders');

        $ids = collect($response->json('data.orders'))->pluck('id');
        $this->assertEquals([$mine->id], $ids->all());
    }

    public function test_orders_can_be_filtered_by_status(): void
    {
        $user = User::factory()->create();
        $paid = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
        Order::factory()->create(['user_id' => $user->id, 'status' => 'refunded']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/orders?status=paid');

        $ids = collect($response->json('data.orders'))->pluck('id');
        $this->assertEquals([$paid->id], $ids->all());
    }

    public function test_orders_can_be_filtered_by_date_range(): void
    {
        $user = User::factory()->create();
        $recent = Order::factory()->create(['user_id' => $user->id, 'created_at' => now()]);
        Order::factory()->create(['user_id' => $user->id, 'created_at' => now()->subDays(30)]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/orders?date_from='.now()->subDay()->toDateString());

        $ids = collect($response->json('data.orders'))->pluck('id');
        $this->assertEquals([$recent->id], $ids->all());
    }

    public function test_order_items_include_review_status(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'fulfillment_status' => 'delivered']);
        $reviewedItem = OrderItem::factory()->create(['order_id' => $order->id]);
        $unreviewedItem = OrderItem::factory()->create(['order_id' => $order->id]);
        Review::factory()->create(['order_item_id' => $reviewedItem->id, 'product_id' => $reviewedItem->product_id]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/orders');

        $items = collect($response->json('data.orders.0.items'));
        $this->assertNotNull($items->firstWhere('id', $reviewedItem->id)['review']);
        $this->assertNull($items->firstWhere('id', $unreviewedItem->id)['review']);
    }
}
