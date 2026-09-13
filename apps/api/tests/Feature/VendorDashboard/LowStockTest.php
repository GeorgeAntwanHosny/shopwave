<?php

namespace Tests\Feature\VendorDashboard;

use App\Models\Product;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LowStockTest extends TestCase
{
    public function test_it_lists_only_low_stock_active_products(): void
    {
        $vendor = Vendor::factory()->create();
        $low = Product::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true, 'stock_quantity' => 2]);
        Product::factory()->create(['vendor_id' => $vendor->id, 'is_active' => true, 'stock_quantity' => 20]);
        Product::factory()->create(['vendor_id' => $vendor->id, 'is_active' => false, 'stock_quantity' => 1]);
        Sanctum::actingAs($vendor->user);

        $response = $this->getJson('/api/v1/vendor/dashboard/low-stock');

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertEquals([$low->id], $ids->all());
    }
}
