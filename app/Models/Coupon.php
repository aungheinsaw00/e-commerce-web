<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    const TYPE_PERCENTAGE = 'percentage';
    const TYPE_FIXED      = 'fixed';

    protected $fillable = [
        'code', 'type', 'value', 'min_spend',
        'usage_limit', 'per_user_limit', 'used_count',
        'expires_at', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value'      => 'decimal:2',
            'min_spend'  => 'decimal:2',
            'is_active'  => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isUsageLimitReached(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    public function isPerUserLimitReached(User $user): bool
    {
        if ($this->per_user_limit === null) {
            return false;
        }

        $userUsageCount = $this->usages()->where('user_id', $user->id)->count();

        return $userUsageCount >= $this->per_user_limit;
    }

    public function isMinSpendMet(float $cartTotal): bool
    {
        return $cartTotal >= (float) $this->min_spend;
    }

    /**
     * Calculate the discount amount for a given cart total.
     */
    public function calculateDiscount(float $cartTotal): float
    {
        if ($this->type === self::TYPE_PERCENTAGE) {
            return round($cartTotal * ((float) $this->value / 100), 2);
        }

        // Fixed discount cannot exceed cart total
        return min((float) $this->value, $cartTotal);
    }
}
