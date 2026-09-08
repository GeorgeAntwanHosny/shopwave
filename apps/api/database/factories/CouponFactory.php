<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'code' => strtoupper(fake()->unique()->bothify('SAVE##??')),
            'type' => 'percentage',
            'value' => 10,
            'min_order_amount' => null,
            'max_uses' => null,
            'used_count' => 0,
            'starts_at' => null,
            'expires_at' => null,
            'is_active' => true,
        ];
    }
}
