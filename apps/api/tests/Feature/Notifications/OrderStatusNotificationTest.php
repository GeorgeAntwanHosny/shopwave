<?php

namespace Tests\Feature\Notifications;

use App\Models\Order;
use App\Models\Vendor;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderStatusNotificationTest extends TestCase
{
    public function test_changing_fulfillment_status_notifies_the_customer(): void
    {
        Notification::fake();

        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create(['vendor_id' => $vendor->id, 'fulfillment_status' => 'processing']);
        Sanctum::actingAs($vendor->user);

        $this->putJson("/api/v1/vendor/orders/{$order->id}", ['fulfillment_status' => 'shipped']);

        Notification::assertSentTo($order->user, OrderStatusChangedNotification::class);
    }

    public function test_setting_the_same_status_again_does_not_renotify(): void
    {
        Notification::fake();

        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create(['vendor_id' => $vendor->id, 'fulfillment_status' => 'shipped']);
        Sanctum::actingAs($vendor->user);

        $this->putJson("/api/v1/vendor/orders/{$order->id}", ['fulfillment_status' => 'shipped']);

        Notification::assertNotSentTo($order->user, OrderStatusChangedNotification::class);
    }
}
