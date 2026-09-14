<?php

namespace Tests\Feature\Review;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewEligibilityTest extends TestCase
{
    public function test_it_returns_reviewable_items_and_my_reviews_for_a_product(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $deliveredOrder = Order::factory()->create(['user_id' => $user->id, 'fulfillment_status' => 'delivered']);
        $reviewableItem = OrderItem::factory()->create(['order_id' => $deliveredOrder->id, 'product_id' => $product->id]);

        $reviewedOrder = Order::factory()->create(['user_id' => $user->id, 'fulfillment_status' => 'delivered']);
        $reviewedItem = OrderItem::factory()->create(['order_id' => $reviewedOrder->id, 'product_id' => $product->id]);
        $review = Review::factory()->create(['order_item_id' => $reviewedItem->id, 'product_id' => $product->id, 'user_id' => $user->id]);

        $processingOrder = Order::factory()->create(['user_id' => $user->id, 'fulfillment_status' => 'processing']);
        OrderItem::factory()->create(['order_id' => $processingOrder->id, 'product_id' => $product->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson("/api/v1/products/{$product->slug}/review-eligibility");

        $response->assertStatus(200);
        $reviewableIds = collect($response->json('data.reviewable_items'))->pluck('order_item_id');
        $this->assertEquals([$reviewableItem->id], $reviewableIds->all());

        $myReviewIds = collect($response->json('data.my_reviews'))->pluck('id');
        $this->assertEquals([$review->id], $myReviewIds->all());
    }

    public function test_guest_cannot_access_review_eligibility(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->slug}/review-eligibility");

        $response->assertStatus(401);
    }
}
