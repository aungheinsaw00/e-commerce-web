<?php

namespace Database\Factories;

use App\Models\RefundRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class RefundRequestFactory extends Factory
{
    protected $model = RefundRequest::class;

    public function definition(): array
    {
        return [
            'reason' => $this->faker->sentence(),
            'status' => RefundRequest::STATUS_PENDING,
            'admin_note'  => null,
            'reviewed_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state([
            'status'      => RefundRequest::STATUS_APPROVED,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status'      => RefundRequest::STATUS_REJECTED,
            'reviewed_at' => now(),
        ]);
    }
}
