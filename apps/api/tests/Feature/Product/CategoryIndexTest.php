<?php

namespace Tests\Feature\Product;

use App\Models\Category;
use Tests\TestCase;

class CategoryIndexTest extends TestCase
{
    public function test_it_returns_the_nested_category_tree(): void
    {
        $parent = Category::factory()->create(['parent_id' => null]);
        Category::factory()->create(['parent_id' => $parent->id]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)->assertJson(['success' => true]);

        $node = collect($response->json('data'))->firstWhere('id', $parent->id);
        $this->assertCount(1, $node['children']);
    }
}
