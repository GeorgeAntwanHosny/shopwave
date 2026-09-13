<?php

namespace Tests\Feature\Checkout;

use App\Models\Order;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorOrderUpdateTest extends TestCase
{
    public function test_vendor_can_update_fulfillment_status_and_tracking(): void
    {
        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create(['vendor_id' => $vendor->id, 'fulfillment_status' => 'processing']);
        Sanctum::actingAs($vendor->user);

        $response = $this->putJson("/api/v1/vendor/orders/{$order->id}", [
            'fulfillment_status' => 'shipped',
            'tracking_number' => '1Z999AA10123456784',
            'carrier' => 'UPS',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.fulfillment_status', 'shipped')
            ->assertJsonPath('data.tracking_number', '1Z999AA10123456784');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'fulfillment_status' => 'shipped',
            'tracking_number' => '1Z999AA10123456784',
            'carrier' => 'UPS',
        ]);
    }

    public function test_non_owner_cannot_update_the_order(): void
    {
        $owner = Vendor::factory()->create();
        $other = Vendor::factory()->create();
        $order = Order::factory()->create(['vendor_id' => $owner->id]);
        Sanctum::actingAs($other->user);

        $response = $this->putJson("/api/v1/vendor/orders/{$order->id}", ['fulfillment_status' => 'shipped']);

        $response->assertStatus(403);
    }

    public function test_invalid_fulfillment_status_is_rejected(): void
    {
        $vendor = Vendor::factory()->create();
        $order = Order::factory()->create(['vendor_id' => $vendor->id]);
        Sanctum::actingAs($vendor->user);

        $response = $this->putJson("/api/v1/vendor/orders/{$order->id}", ['fulfillment_status' => 'not_a_real_status']);

        $response->assertStatus(422);
    }
}
