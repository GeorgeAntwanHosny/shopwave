<?php

namespace Tests\Feature\Review;

use App\Models\Product;
use App\Models\Review;
use App\Models\Vendor;
use Tests\TestCase;

class RatingRecalculationTest extends TestCase
{
    public function test_creating_reviews_updates_product_and_vendor_aggregate_rating(): void
    {
        $vendor = Vendor::factory()->create(['average_rating' => 0, 'rating_count' => 0]);
        $product = Product::factory()->create(['vendor_id' => $vendor->id, 'average_rating' => 0, 'rating_count' => 0]);

        Review::factory()->create(['product_id' => $product->id, 'vendor_id' => $vendor->id, 'rating' => 4]);
        Review::factory()->create(['product_id' => $product->id, 'vendor_id' => $vendor->id, 'rating' => 2]);

        $this->assertEquals(3.0, (float) $product->fresh()->average_rating);
        $this->assertEquals(2, $product->fresh()->rating_count);
        $this->assertEquals(3.0, (float) $vendor->fresh()->average_rating);
        $this->assertEquals(2, $vendor->fresh()->rating_count);
    }
}
