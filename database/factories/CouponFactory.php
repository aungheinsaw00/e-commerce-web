<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code'           => strtoupper($this->faker->unique()->lexify('??????')),
            'type'           => $this->faker->randomElement([Coupon::TYPE_PERCENTAGE, Coupon::TYPE_FIXED]),
            'value'          => $this->faker->randomFloat(2, 5, 50),
            'min_spend'      => 0,
            'usage_limit'    => null,
            'per_user_limit' => null,
            'used_count'     => 0,
            'expires_at'     => null,
            'is_active'      => true,
        ];
    }

    public function percentage(float $value = 10.00): static
    {
        return $this->state(['type' => Coupon::TYPE_PERCENTAGE, 'value' => $value]);
    }

    public function fixed(float $value = 10.00): static
    {
        return $this->state(['type' => Coupon::TYPE_FIXED, 'value' => $value]);
    }

    public function expired(): static
    {
        return $this->state(['expires_at' => now()->subDay()]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
