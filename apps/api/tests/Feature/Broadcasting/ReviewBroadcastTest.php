<?php

namespace Tests\Feature\Broadcasting;

use App\Events\NewReviewPosted;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewBroadcastTest extends TestCase
{
    public function test_submitting_a_review_dispatches_new_review_posted(): void
    {
        Event::fake([NewReviewPosted::class]);

        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'fulfillment_status' => 'delivered']);
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/order-items/{$orderItem->id}/reviews", ['rating' => 5]);

        Event::assertDispatched(NewReviewPosted::class);
    }
}
