<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class CartAddItemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushdb();
    }

    public function test_guest_can_add_an_item_and_receives_a_cart_token(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'stock_quantity' => 10, 'price' => 20]);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.item_count', 2)
            ->assertJsonPath('data.grand_total', '40.00');

        $this->assertNotNull($response->json('data.cart_token'));
    }

    public function test_adding_more_than_stock_is_rejected_and_rolled_back(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'stock_quantity' => 3]);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $product->id,
            'quantity' => 5,
        ], ['X-Cart-Token' => 'test-token-1']);

        $response->assertStatus(422);

        $cart = $this->getJson('/api/v1/cart', ['X-Cart-Token' => 'test-token-1']);
        $cart->assertJsonPath('data.item_count', 0);
    }

    public function test_repeated_adds_for_the_same_product_accumulate_quantity(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'stock_quantity' => 10, 'price' => 5]);
        $token = 'test-token-2';

        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2], ['X-Cart-Token' => $token]);
        $response = $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 3], ['X-Cart-Token' => $token]);

        $response->assertStatus(200)->assertJsonPath('data.item_count', 5);
    }
}
