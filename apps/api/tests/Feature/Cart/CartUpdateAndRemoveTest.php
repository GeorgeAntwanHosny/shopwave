<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class CartUpdateAndRemoveTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushdb();
    }

    public function test_updating_quantity_to_zero_removes_the_item(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $token = 'test-token-5';
        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 3], ['X-Cart-Token' => $token]);

        $response = $this->putJson("/api/v1/cart/items/{$product->id}", ['quantity' => 0], ['X-Cart-Token' => $token]);

        $response->assertStatus(200)->assertJsonPath('data.item_count', 0);
    }

    public function test_removing_an_item_directly(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $token = 'test-token-6';
        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 1], ['X-Cart-Token' => $token]);

        $response = $this->deleteJson("/api/v1/cart/items/{$product->id}", [], ['X-Cart-Token' => $token]);

        $response->assertStatus(200)->assertJsonPath('data.item_count', 0);
    }
}
