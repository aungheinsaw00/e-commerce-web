<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        $price = fake()->randomFloat(2, 2, 50);
        $quantity = fake()->numberBetween(1, 5);
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'variant_id' => ProductVariant::factory(),
            'product_name_snapshot' => fake()->words(3, true),
            'sku_snapshot' => strtoupper(fake()->bothify('??-####')),
            'unit_price' => $price,
            'discount_amount' => 0,
            'final_price' => $price,
            'quantity' => $quantity,
        ];
    }
}
