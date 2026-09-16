<?php

namespace Tests\Feature\Notifications;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\NewReviewPostedNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReviewNotificationTest extends TestCase
{
    public function test_submitting_a_review_notifies_the_vendor(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'vendor_id' => $vendor->id, 'fulfillment_status' => 'delivered']);
        $orderItem = OrderItem::factory()->create(['order_id' => $order->id]);
        Sanctum::actingAs($user);

        $this->postJson("/api/v1/order-items/{$orderItem->id}/reviews", ['rating' => 5]);

        Notification::assertSentTo($vendor, NewReviewPostedNotification::class);
    }
}
