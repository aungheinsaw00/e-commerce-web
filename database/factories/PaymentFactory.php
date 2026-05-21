<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'provider' => 'transfer',
            'status' => 'pending',
            'transaction_id' => null,
            'amount' => fake()->randomFloat(2, 10, 500),
            'currency' => 'USD',
            'proof_path' => null,
            'proof_hash' => null,
            'paid_at' => null,
            'metadata' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'verified',
            'paid_at' => now(),
        ]);
    }
}
