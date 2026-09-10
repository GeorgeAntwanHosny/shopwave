<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CheckoutSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'stripe_payment_intent_id' => 'pi_'.fake()->unique()->bothify('##########'),
            'cart_snapshot' => ['vendors' => [], 'unavailable_items' => [], 'grand_total' => '0.00', 'item_count' => 0],
            'status' => 'pending',
        ];
    }
}
