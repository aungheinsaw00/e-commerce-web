<?php

namespace Tests\Feature;

use App\Exceptions\CouponException;
use App\Models\Coupon;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\DiscountService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    private function createCoupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'code'        => 'TEST10',
            'type'        => Coupon::TYPE_PERCENTAGE,
            'value'       => 10.00,
            'min_spend'   => 0,
            'usage_limit' => null,
            'is_active'   => true,
        ], $overrides));
    }

    private function createOrderForUser(User $user): Order
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id, 'base_price' => 100.00]);
        $variant  = ProductVariant::factory()->create(['product_id' => $product->id]);
        Inventory::factory()->create([
            'variant_id'       => $variant->id,
            'stock_quantity'   => 50,
            'reserved_quantity' => 0,
        ]);

        $cart = app(CartService::class)->getOrCreateCart($user);
        app(CartService::class)->addItem($cart, $variant->id, 1);

        return app(OrderService::class)->createFromCart($user, $cart, PaymentMethod::factory()->create()->id);
    }

    public function test_valid_percentage_coupon_applies_discount(): void
    {
        $user    = User::factory()->create();
        $coupon  = $this->createCoupon(['code' => 'SAVE10', 'type' => Coupon::TYPE_PERCENTAGE, 'value' => 10.00]);
        $service = app(DiscountService::class);

        $validated = $service->validateCoupon('SAVE10', $user, 100.00);
        $discount  = $service->applyCoupon($validated, 100.00);

        $this->assertEquals(10.00, $discount);
    }

    public function test_valid_fixed_coupon_applies_discount(): void
    {
        $user    = User::factory()->create();
        $coupon  = $this->createCoupon(['code' => 'FIXED20', 'type' => Coupon::TYPE_FIXED, 'value' => 20.00]);
        $service = app(DiscountService::class);

        $validated = $service->validateCoupon('FIXED20', $user, 100.00);
        $discount  = $service->applyCoupon($validated, 100.00);

        $this->assertEquals(20.00, $discount);
    }

    public function test_fixed_coupon_cannot_exceed_cart_total(): void
    {
        $user    = User::factory()->create();
        $coupon  = $this->createCoupon(['code' => 'FIXED200', 'type' => Coupon::TYPE_FIXED, 'value' => 200.00]);
        $service = app(DiscountService::class);

        $validated = $service->validateCoupon('FIXED200', $user, 50.00);
        $discount  = $service->applyCoupon($validated, 50.00);

        $this->assertEquals(50.00, $discount); // Capped at cart total
    }

    public function test_expired_coupon_throws_exception(): void
    {
        $user   = User::factory()->create();
        $coupon = $this->createCoupon([
            'code'       => 'EXPIRED',
            'expires_at' => now()->subDay(),
        ]);
        $service = app(DiscountService::class);

        $this->expectException(CouponException::class);
        $service->validateCoupon('EXPIRED', $user, 100.00);
    }

    public function test_usage_limit_reached_throws_exception(): void
    {
        $user   = User::factory()->create();
        $coupon = $this->createCoupon([
            'code'        => 'MAXED',
            'usage_limit' => 1,
            'used_count'  => 1,
        ]);
        $service = app(DiscountService::class);

        $this->expectException(CouponException::class);
        $service->validateCoupon('MAXED', $user, 100.00);
    }

    public function test_per_user_limit_enforced(): void
    {
        $user   = User::factory()->create();
        $coupon = $this->createCoupon([
            'code'           => 'PERUSER',
            'per_user_limit' => 1,
        ]);
        $order  = $this->createOrderForUser($user);

        // Record one usage for this user
        \App\Models\CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id'   => $user->id,
            'order_id'  => $order->id,
        ]);

        $service = app(DiscountService::class);

        $this->expectException(CouponException::class);
        $service->validateCoupon('PERUSER', $user, 100.00);
    }

    public function test_minimum_spend_not_met_throws_exception(): void
    {
        $user   = User::factory()->create();
        $coupon = $this->createCoupon([
            'code'      => 'MINSPEND',
            'min_spend' => 200.00,
        ]);
        $service = app(DiscountService::class);

        $this->expectException(CouponException::class);
        $service->validateCoupon('MINSPEND', $user, 50.00);
    }

    public function test_coupon_not_found_throws_exception(): void
    {
        $user    = User::factory()->create();
        $service = app(DiscountService::class);

        $this->expectException(CouponException::class);
        $service->validateCoupon('DOESNOTEXIST', $user, 100.00);
    }

    public function test_record_coupon_usage_increments_used_count(): void
    {
        $user   = User::factory()->create();
        $coupon = $this->createCoupon(['code' => 'USETEST', 'usage_limit' => 5]);
        $order  = $this->createOrderForUser($user);
        $service = app(DiscountService::class);

        $service->recordCouponUsage($coupon, $user, $order);

        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    public function test_concurrent_usage_respects_limit_via_lock(): void
    {
        // This test simulates the race condition check by calling recordCouponUsage
        // when the coupon is already at its limit (what lockForUpdate prevents concurrently).
        $user    = User::factory()->create();
        $coupon  = $this->createCoupon(['code' => 'RACETEST', 'usage_limit' => 1, 'used_count' => 1]);
        $order   = $this->createOrderForUser($user);
        $service = app(DiscountService::class);

        $this->expectException(CouponException::class);
        $service->recordCouponUsage($coupon, $user, $order);
    }

    public function test_inactive_coupon_is_rejected(): void
    {
        $user   = User::factory()->create();
        $coupon = $this->createCoupon(['code' => 'INACTIVE', 'is_active' => false]);
        $service = app(DiscountService::class);

        $this->expectException(CouponException::class);
        $service->validateCoupon('INACTIVE', $user, 100.00);
    }
}
