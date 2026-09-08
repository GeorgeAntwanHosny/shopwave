<?php

namespace Tests\Feature\Cart;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class CartMergeOnLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Redis::flushdb();
    }

    public function test_guest_cart_merges_into_user_cart_on_login(): void
    {
        $user = User::factory()->create(['password' => Hash::make('Password123!')]);
        $product = Product::factory()->create(['stock_quantity' => 10]);
        $guestToken = 'test-guest-merge-token';

        $this->postJson('/api/v1/cart/items', ['product_id' => $product->id, 'quantity' => 2], ['X-Cart-Token' => $guestToken]);

        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ], ['X-Cart-Token' => $guestToken]);

        $response = $this->getJson('/api/v1/cart', ['Authorization' => 'Bearer '.$user->createToken('test')->plainTextToken]);

        $response->assertStatus(200)->assertJsonPath('data.item_count', 2);
    }
}
