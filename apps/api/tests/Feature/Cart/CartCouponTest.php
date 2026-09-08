<?php

namespace Tests\Feature\Cart;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class CartCouponTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushdb();
    }

    public function test_percentage_coupon_discounts_only_its_own_vendors_items(): void
    {
        $vendorA = Vendor::factory()->create();
        $vendorB = Vendor::factory()->create();
        $productA = Product::factory()->create(['vendor_id' => $vendorA->id, 'price' => 100, 'stock_quantity' => 10]);
        $productB = Product::factory()->create(['vendor_id' => $vendorB->id, 'price' => 50, 'stock_quantity' => 10]);
        $coupon = Coupon::factory()->create(['vendor_id' => $vendorA->id, 'type' => 'percentage', 'value' => 10]);
        $token = 'test-token-7';

        $this->postJson('/api/v1/cart/items', ['product_id' => $productA->id, 'quantity' => 1], ['X-Cart-Token' => $token]);
        $this->postJson('/api/v1/cart/items', ['product_id' => $productB->id, 'quantity' => 1], ['X-Cart-Token' => $token]);

        $response = $this->postJson('/api/v1/cart/coupon', ['code' => $coupon->code], ['X-Cart-Token' => $token]);

        $response->assertStatus(200)->assertJsonPath('data.grand_total', '140.00');
    }

    public function test_expired_coupon_is_rejected(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'stock_quantity' => 10]);
        $coupon = Coupon::factory()->create(['vendor_id' => $vendor->id, 'expires_at' => now()->subDay()]);
        $token = 'test-token-8';
        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], ['X-Cart-Token' => $token]);

        $response = $this->postJson('/api/v1/cart/coupon', ['code' => $coupon->code], ['X-Cart-Token' => $token]);

        $response->assertStatus(422);
    }

    public function test_coupon_below_minimum_order_amount_is_rejected(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'price' => 10, 'stock_quantity' => 10]);
        $coupon = Coupon::factory()->create(['vendor_id' => $vendor->id, 'min_order_amount' => 100]);
        $token = 'test-token-9';
        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], ['X-Cart-Token' => $token]);

        $response = $this->postJson('/api/v1/cart/coupon', ['code' => $coupon->code], ['X-Cart-Token' => $token]);

        $response->assertStatus(422);
    }

    public function test_removing_a_coupon(): void
    {
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'price' => 100, 'stock_quantity' => 10]);
        $coupon = Coupon::factory()->create(['vendor_id' => $vendor->id, 'type' => 'fixed', 'value' => 20]);
        $token = 'test-token-10';
        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], ['X-Cart-Token' => $token]);
        $this->postJson('/api/v1/cart/coupon', ['code' => $coupon->code], ['X-Cart-Token' => $token]);

        $response = $this->deleteJson('/api/v1/cart/coupon', [], ['X-Cart-Token' => $token]);

        $response->assertStatus(200)->assertJsonPath('data.grand_total', '100.00');
    }
}
