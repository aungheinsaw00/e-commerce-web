<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 10, 500);
        $discount = fake()->randomFloat(2, 0, $subtotal * 0.2);
        return [
            'user_id' => User::factory(),
            'status' => 'pending',
            'subtotal' => $subtotal,
            'discount_total' => $discount,
            'total' => $subtotal - $discount,
            'currency' => 'USD',
            'notes' => null,
        ];
    }
}
