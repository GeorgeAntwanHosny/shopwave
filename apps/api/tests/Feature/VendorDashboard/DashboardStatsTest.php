<?php

namespace Tests\Feature\VendorDashboard;

use App\Models\Order;
use App\Models\Product;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardStatsTest extends TestCase
{
    public function test_it_returns_correct_aggregate_stats(): void
    {
        $vendor = Vendor::factory()->create();
        Order::factory()->create(['vendor_id' => $vendor->id, 'status' => 'paid', 'vendor_payout_amount' => 90, 'transferred_at' => now()]);
        Order::factory()->create(['vendor_id' => $vendor->id, 'status' => 'paid', 'vendor_payout_amount' => 10, 'transferred_at' => null]);
        Product::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true, 'stock_quantity' => 3]);
        Product::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true, 'stock_quantity' => 50]);
        Sanctum::actingAs($vendor->user);

        $response = $this->getJson('/api/v1/vendor/dashboard/stats');

        $response->assertStatus(200)
            ->assertJsonPath('data.total_revenue', '100.00')
            ->assertJsonPath('data.orders_count', 2)
            ->assertJsonPath('data.average_order_value', '50.00')
            ->assertJsonPath('data.pending_payouts_count', 1)
            ->assertJsonPath('data.low_stock_count', 1);
    }
}
