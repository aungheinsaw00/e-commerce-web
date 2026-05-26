<?php

namespace App\Services;

use App\Exceptions\CouponException;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use App\Models\UserSpending;
use Illuminate\Support\Facades\DB;

class DiscountService
{
    // ----------------------------------------------------------------
    // Coupon System
    // ----------------------------------------------------------------

    /**
     * Validate a coupon code for a given user and cart total.
     * All checks are authoritative — final validation happens inside recordCouponUsage.
     *
     * @throws CouponException
     */
    public function validateCoupon(string $code, User $user, float $cartTotal): Coupon
    {
        $coupon = Coupon::active()->where('code', strtoupper(trim($code)))->first();

        if (!$coupon) {
            throw CouponException::notFound($code);
        }

        if ($coupon->isExpired()) {
            throw CouponException::expired();
        }

        if ($coupon->isUsageLimitReached()) {
            throw CouponException::usageLimitReached();
        }

        if ($coupon->isPerUserLimitReached($user)) {
            throw CouponException::perUserLimitReached();
        }

        if (!$coupon->isMinSpendMet($cartTotal)) {
            throw CouponException::minimumSpendNotMet((float) $coupon->min_spend);
        }

        return $coupon;
    }

    /**
     * Calculate the discount amount for a coupon applied to a cart total.
     */
    public function applyCoupon(Coupon $coupon, float $cartTotal): float
    {
        return $coupon->calculateDiscount($cartTotal);
    }

    /**
     * Atomically record a coupon usage and increment its used_count.
     * Uses lockForUpdate() to prevent race-condition overuse under concurrency.
     *
     * @throws CouponException
     */
    public function recordCouponUsage(Coupon $coupon, User $user, Order $order): CouponUsage
    {
        return DB::transaction(function () use ($coupon, $user, $order) {
            // Re-fetch with lock to prevent concurrent overuse
            $locked = Coupon::lockForUpdate()->findOrFail($coupon->id);

            if ($locked->isUsageLimitReached()) {
                throw CouponException::usageLimitReached();
            }

            if ($locked->isPerUserLimitReached($user)) {
                throw CouponException::perUserLimitReached();
            }

            $locked->increment('used_count');

            return CouponUsage::create([
                'coupon_id' => $locked->id,
                'user_id'   => $user->id,
                'order_id'  => $order->id,
            ]);
        });
    }

    // ----------------------------------------------------------------
    // Member Tier System
    // ----------------------------------------------------------------

    /**
     * Get the member tier for a user, reading from user_spending table.
     */
    public function getMemberTier(User $user): string
    {
        $spending = UserSpending::where('user_id', $user->id)->first();

        if (!$spending) {
            return UserSpending::TIER_BRONZE;
        }

        return $spending->tier;
    }

    /**
     * Get the automatic tier discount multiplier for a user.
     * Returns a discount percentage (0–1 scale).
     *
     * Bronze: 0%, Silver: 2%, Gold: 5%
     */
    public function getTierDiscountRate(User $user): float
    {
        return match ($this->getMemberTier($user)) {
            UserSpending::TIER_GOLD   => 0.05,
            UserSpending::TIER_SILVER => 0.02,
            default                   => 0.00,
        };
    }

    /**
     * Update a user's total_spent and tier after an order completes.
     * MUST be called inside a DB transaction with lockForUpdate on the spending record.
     */
    public function updateUserSpending(User $user, float $orderTotal): UserSpending
    {
        return DB::transaction(function () use ($user, $orderTotal) {
            $spending = UserSpending::where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($spending) {
                $newTotal = (float) $spending->total_spent + $orderTotal;
                $spending->update([
                    'total_spent' => $newTotal,
                    'tier'        => UserSpending::computeTier($newTotal),
                ]);
            } else {
                $spending = UserSpending::create([
                    'user_id'     => $user->id,
                    'total_spent' => $orderTotal,
                    'tier'        => UserSpending::computeTier($orderTotal),
                ]);
            }

            return $spending->fresh();
        });
    }
}
