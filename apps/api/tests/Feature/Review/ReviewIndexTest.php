<?php

namespace Tests\Feature\Review;

use App\Models\Product;
use App\Models\Review;
use Tests\TestCase;

class ReviewIndexTest extends TestCase
{
    public function test_it_lists_reviews_for_a_product(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->create(['product_id' => $product->id]);

        $response = $this->getJson("/api/v1/products/{$product->slug}/reviews");

        $response->assertStatus(200);
        $ids = collect($response->json('data.reviews'))->pluck('id');
        $this->assertTrue($ids->contains($review->id));

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'product_id' => $product->id,
        ]);
    }
}
