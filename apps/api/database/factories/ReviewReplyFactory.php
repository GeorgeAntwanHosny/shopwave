<?php

namespace Database\Factories;

use App\Models\Review;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewReplyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'review_id' => Review::factory(),
            'vendor_id' => Vendor::factory(),
            'reply' => fake()->sentence(),
        ];
    }
}
