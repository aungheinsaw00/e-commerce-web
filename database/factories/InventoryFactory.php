<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'variant_id' => ProductVariant::factory(),
            'stock_quantity' => fake()->numberBetween(10, 200),
            'reserved_quantity' => 0,
            'low_stock_threshold' => 5,
        ];
    }
}
