<?php

namespace Tests\Feature\VendorDashboard;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Vendor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductAnalyticsTest extends TestCase
{
    public function test_it_aggregates_units_and_revenue_per_product(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id]);
        $order = Order::factory()->create(['vendor_id' => $vendor->id, 'status' => 'paid']);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => 'Widget', 'quantity' => 3, 'subtotal' => 30]);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id, 'product_name' => 'Widget', 'quantity' => 2, 'subtotal' => 20]);
        Sanctum::actingAs($vendor->user);

        $response = $this->getJson('/api/v1/vendor/dashboard/analytics');

        $row = collect($response->json('data'))->firstWhere('product_name', 'Widget');
        $this->assertEquals(5, $row['units_sold']);
        $this->assertEquals('50.00', $row['revenue']);
    }
}
