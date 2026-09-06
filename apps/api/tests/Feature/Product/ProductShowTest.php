<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    public function test_it_shows_an_active_product_by_slug(): void
    {
        $product = Product::factory()->create(['is_active' => true]);

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertStatus(200)->assertJsonPath('data.id', $product->id);
    }

    public function test_it_returns_404_for_an_inactive_product(): void
    {
        $product = Product::factory()->create(['is_active' => false]);

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertStatus(404);
    }
}
