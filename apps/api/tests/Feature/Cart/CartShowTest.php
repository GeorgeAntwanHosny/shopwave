<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class CartShowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushdb();
    }

    public function test_cart_groups_items_by_vendor_with_subtotals(): void
    {
        $vendorA = Vendor::factory()->create(['shop_name' => 'Shop A']);
        $vendorB = Vendor::factory()->create(['shop_name' => 'Shop B']);
        $productA = Product::factory()->create(['vendor_id' => $vendorA->id, 'price' => 10, 'stock_quantity' => 10]);
        $productB = Product::factory()->create(['vendor_id' => $vendorB->id, 'price' => 25, 'stock_quantity' => 10]);
        $token = 'test-token-3';

        $this->postJson('/api/v1/cart/items', ['product_id' => $productA->id, 'quantity' => 2], ['X-Cart-Token' => $token]);
        $this->postJson('/api/v1/cart/items', ['product_id' => $productB->id, 'quantity' => 1], ['X-Cart-Token' => $token]);

        $response = $this->getJson('/api/v1/cart', ['X-Cart-Token' => $token]);

        $response->assertStatus(200)->assertJsonPath('data.grand_total', '45.00');
        $this->assertCount(2, $response->json('data.vendors'));
    }

    public function test_a_deactivated_product_shows_as_unavailable_instead_of_crashing(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'stock_quantity' => 5]);
        $token = 'test-token-4';
        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], ['X-Cart-Token' => $token]);

        $product->update(['is_active' => false]);

        $response = $this->getJson('/api/v1/cart', ['X-Cart-Token' => $token]);

        $response->assertStatus(200)->assertJsonPath('data.grand_total', '0.00');
        $this->assertCount(1, $response->json('data.unavailable_items'));
    }
}
