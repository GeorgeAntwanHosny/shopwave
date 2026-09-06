<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    public function test_it_lists_only_active_products(): void
    {
        Product::factory()->create(['is_active' => true, 'name' => 'Visible Product']);
        Product::factory()->create(['is_active' => false, 'name' => 'Hidden Product']);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
        $names = collect($response->json('data.products'))->pluck('name');
        $this->assertTrue($names->contains('Visible Product'));
        $this->assertFalse($names->contains('Hidden Product'));
    }

    public function test_it_filters_by_price_range(): void
    {
        Product::factory()->create(['price' => 10, 'is_active' => true]);
        $expensive = Product::factory()->create(['price' => 300, 'is_active' => true]);

        $response = $this->getJson('/api/v1/products?price_min=100');

        $response->assertStatus(200);
        $ids = collect($response->json('data.products'))->pluck('id');
        $this->assertCount(1, $ids);
        $this->assertTrue($ids->contains($expensive->id));
    }

    public function test_search_matches_on_partial_word_substring(): void
    {
        $match = Product::factory()->create(['name' => 'Wireless Gadget Pro', 'is_active' => true]);
        Product::factory()->create(['name' => 'Kitchen Blender', 'is_active' => true]);

        $response = $this->getJson('/api/v1/products?q=gadg');

        $response->assertStatus(200);
        $ids = collect($response->json('data.products'))->pluck('id');
        $this->assertTrue($ids->contains($match->id));
        $this->assertCount(1, $ids);
    }

    public function test_search_matches_on_a_single_character(): void
    {
        // Regression test: to_tsquery prefix search silently dropped very
        // short terms, making single-character search return nothing.
        // ILIKE substring matching should not have this problem.
        $match = Product::factory()->create(['name' => 'Zebra Print Scarf', 'is_active' => true]);
        Product::factory()->create(['name' => 'Kitchen Blender', 'is_active' => true]);

        $response = $this->getJson('/api/v1/products?q=Z');

        $response->assertStatus(200);
        $ids = collect($response->json('data.products'))->pluck('id');
        $this->assertTrue($ids->contains($match->id));
    }
}
