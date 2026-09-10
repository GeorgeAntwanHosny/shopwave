<?php

namespace Tests\Feature\Checkout;

use App\Models\Order;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorOrderShowTest extends TestCase
{
    public function test_vendor_can_view_an_order_they_received(): void
    {
        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create(['vendor_id' => $vendor->id]);
        Sanctum::actingAs($vendor->user);

        $response = $this->getJson("/api/v1/vendor/orders/{$order->id}");

        $response->assertStatus(200)->assertJsonPath('data.id', $order->id);
    }

    public function test_vendor_cannot_view_another_vendors_order(): void
    {
        $owner = Vendor::factory()->create();
        $otherVendor = Vendor::factory()->create();
        $order = Order::factory()->create(['vendor_id' => $owner->id]);
        Sanctum::actingAs($otherVendor->user);

        $response = $this->getJson("/api/v1/vendor/orders/{$order->id}");

        $response->assertStatus(403);
    }
}
