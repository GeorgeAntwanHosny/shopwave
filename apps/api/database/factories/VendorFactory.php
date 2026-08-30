<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VendorFactory extends Factory
{
    public function definition(): array
    {
        $shopName = fake()->company();

        return [
            'user_id' => User::factory(),
            'shop_name' => $shopName,
            'shop_slug' => Str::slug($shopName).'-'.fake()->unique()->randomNumber(5),
            'stripe_account_id' => 'acct_'.fake()->unique()->bothify('??########'),
            'stripe_onboarding_complete' => false,
            'average_rating' => 0,
            'rating_count' => 0,
        ];
    }
}
