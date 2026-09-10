<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 5, 100);
        $qty = fake()->numberBetween(1, 3);

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fake()->words(3, true),
            'price' => $price,
            'quantity' => $qty,
            'subtotal' => $price * $qty,
        ];
    }
}
