<?php

namespace Tests\Feature\VendorDashboard;

use App\Models\Order;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RevenueChartTest extends TestCase
{
    public function test_it_returns_thirty_days_zero_filled(): void
    {
        $vendor = Vendor::factory()->create();
        Order::factory()->create(['vendor_id' => $vendor->id, 'status' => 'paid', 'vendor_payout_amount' => 42, 'created_at' => now()]);
        Sanctum::actingAs($vendor->user);

        $response = $this->getJson('/api/v1/vendor/dashboard/revenue-chart');

        $response->assertStatus(200);
        $series = $response->json('data');
        $this->assertCount(30, $series);
        $this->assertEquals('42.00', collect($series)->last()['revenue']);
    }
}
