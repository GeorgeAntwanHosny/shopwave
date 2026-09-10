<?php

namespace Tests\Feature\Checkout;

use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VendorOrderIndexTest extends TestCase
{
    public function test_vendor_sees_only_orders_they_received(): void
    {
        $vendor = Vendor::factory()->create();
        $other = Vendor::factory()->create();
        $mine = Order::factory()->create(['vendor_id' => $vendor->id]);
        Order::factory()->create(['vendor_id' => $other->id]);
        Sanctum::actingAs($vendor->user);

        $response = $this->getJson('/api/v1/vendor/orders');

        $ids = collect($response->json('data.orders'))->pluck('id');
        $this->assertEquals([$mine->id], $ids->all());
    }

    public function test_vendor_orders_can_be_filtered_by_status(): void
    {
        $vendor = Vendor::factory()->create();
        $paid = Order::factory()->create(['vendor_id' => $vendor->id, 'status' => 'paid']);
        Order::factory()->create(['vendor_id' => $vendor->id, 'status' => 'refunded']);
        Sanctum::actingAs($vendor->user);

        $response = $this->getJson('/api/v1/vendor/orders?status=paid');

        $ids = collect($response->json('data.orders'))->pluck('id');
        $this->assertEquals([$paid->id], $ids->all());
    }

    public function test_non_vendor_cannot_view_received_orders(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/vendor/orders');

        $response->assertStatus(403);
    }
}
