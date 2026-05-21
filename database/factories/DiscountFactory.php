<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true) . ' discount',
            'product_id' => Product::factory(),
            'category_id' => null,
            'type' => 'percentage',
            'value' => fake()->randomFloat(2, 5, 25),
            'min_quantity' => 1,
            'starts_at' => null,
            'ends_at' => null,
            'is_active' => true,
        ];
    }
}
