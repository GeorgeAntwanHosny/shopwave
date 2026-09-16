<?php

namespace Tests\Feature\Broadcasting;

use App\Events\OrderStatusChanged;
use App\Models\Order;
use App\Models\Vendor;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderStatusBroadcastTest extends TestCase
{
    public function test_changing_fulfillment_status_dispatches_order_status_changed(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create(['vendor_id' => $vendor->id, 'fulfillment_status' => 'processing']);
        Sanctum::actingAs($vendor->user);

        $this->putJson("/api/v1/vendor/orders/{$order->id}", ['fulfillment_status' => 'shipped']);

        Event::assertDispatched(OrderStatusChanged::class, fn ($e) => $e->order->id === $order->id);
    }

    public function test_setting_the_same_status_again_does_not_redispatch(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create(['vendor_id' => $vendor->id, 'fulfillment_status' => 'shipped']);
        Sanctum::actingAs($vendor->user);

        $this->putJson("/api/v1/vendor/orders/{$order->id}", ['fulfillment_status' => 'shipped']);

        Event::assertNotDispatched(OrderStatusChanged::class);
    }
}
