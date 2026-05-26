<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\UserSpending;
use App\Services\AnalyticsService;
use App\Services\CartService;
use App\Services\DiscountService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function createCompletedOrder(User $user, float $price = 100.00): Order
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id, 'base_price' => $price]);
        $variant  = ProductVariant::factory()->create(['product_id' => $product->id]);
        Inventory::factory()->create([
            'variant_id'       => $variant->id,
            'stock_quantity'   => 50,
            'reserved_quantity' => 0,
        ]);

        $cart = app(CartService::class)->getOrCreateCart($user);
        app(CartService::class)->addItem($cart, $variant->id, 1);

        $order = app(OrderService::class)->createFromCart($user, $cart, PaymentMethod::factory()->create()->id);
        $order->update(['status' => Order::STATUS_COMPLETED, 'total' => $price]);

        return $order->fresh();
    }

    // ----------------------------------------------------------------
    // Monthly Earnings
    // ----------------------------------------------------------------

    public function test_monthly_earnings_sums_only_valid_statuses(): void
    {
        $user     = User::factory()->create();
        $now      = now();
        $service  = app(AnalyticsService::class);

        // Create a completed order (valid)
        $this->createCompletedOrder($user, 100.00);

        // Create a cancelled order (should NOT count)
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id, 'base_price' => 200.00]);
        $variant  = ProductVariant::factory()->create(['product_id' => $product->id]);
        Inventory::factory()->create(['variant_id' => $variant->id, 'stock_quantity' => 50, 'reserved_quantity' => 0]);
        $cart = app(CartService::class)->getOrCreateCart($user);
        app(CartService::class)->addItem($cart, $variant->id, 1);
        $cancelledOrder = app(OrderService::class)->createFromCart($user, $cart, PaymentMethod::factory()->create()->id);
        $cancelledOrder->update(['status' => Order::STATUS_CANCELLED, 'total' => 200.00]);

        $earnings = $service->getMonthlyEarnings($now->year, $now->month);

        // Only the completed $100 order should count
        $this->assertEquals(100.00, $earnings);
    }

    public function test_pending_orders_excluded_from_earnings(): void
    {
        $user    = User::factory()->create();
        $now     = now();
        $service = app(AnalyticsService::class);

        // Only pending_payment order — should not count
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id, 'base_price' => 50.00]);
        $variant  = ProductVariant::factory()->create(['product_id' => $product->id]);
        Inventory::factory()->create(['variant_id' => $variant->id, 'stock_quantity' => 50, 'reserved_quantity' => 0]);
        $cart = app(CartService::class)->getOrCreateCart($user);
        app(CartService::class)->addItem($cart, $variant->id, 1);
        app(OrderService::class)->createFromCart($user, $cart, PaymentMethod::factory()->create()->id);

        $earnings = $service->getMonthlyEarnings($now->year, $now->month);

        $this->assertEquals(0.00, $earnings);
    }

    // ----------------------------------------------------------------
    // User Spending
    // ----------------------------------------------------------------

    public function test_user_spending_aggregation(): void
    {
        $user1   = User::factory()->create();
        $user2   = User::factory()->create();
        $service = app(AnalyticsService::class);

        $this->createCompletedOrder($user1, 150.00);
        $this->createCompletedOrder($user2, 250.00);

        $spending = $service->getUserSpending();

        // Both users should appear
        $this->assertCount(2, $spending);

        // user2 spends more, should come first (ordered by total_spent DESC)
        $this->assertEquals($user2->id, $spending->first()->user_id);
    }

    // ----------------------------------------------------------------
    // Member Tier
    // ----------------------------------------------------------------

    public function test_bronze_tier_for_new_user(): void
    {
        $user    = User::factory()->create();
        $service = app(DiscountService::class);

        $this->assertEquals(UserSpending::TIER_BRONZE, $service->getMemberTier($user));
    }

    public function test_tier_computed_correctly_on_spending_update(): void
    {
        $user    = User::factory()->create();
        $service = app(DiscountService::class);

        // Bronze → Silver threshold is 1000
        $service->updateUserSpending($user, 1500.00);
        $this->assertEquals(UserSpending::TIER_SILVER, $service->getMemberTier($user));
    }

    public function test_gold_tier_for_high_spender(): void
    {
        $user    = User::factory()->create();
        $service = app(DiscountService::class);

        $service->updateUserSpending($user, 6000.00);
        $this->assertEquals(UserSpending::TIER_GOLD, $service->getMemberTier($user));
    }

    public function test_tier_accumulates_across_orders(): void
    {
        $user    = User::factory()->create();
        $service = app(DiscountService::class);

        $service->updateUserSpending($user, 800.00);
        $this->assertEquals(UserSpending::TIER_BRONZE, $service->getMemberTier($user));

        $service->updateUserSpending($user, 300.00); // Now total is 1100 → silver
        $this->assertEquals(UserSpending::TIER_SILVER, $service->getMemberTier($user));
    }

    public function test_tier_discount_rates(): void
    {
        $user    = User::factory()->create();
        $service = app(DiscountService::class);

        // Bronze
        $this->assertEquals(0.00, $service->getTierDiscountRate($user));

        // Silver
        $service->updateUserSpending($user, 1000.00);
        $this->assertEquals(0.02, $service->getTierDiscountRate($user));

        // Gold
        $service->updateUserSpending($user, 4000.01); // Total now > 5000
        $this->assertEquals(0.05, $service->getTierDiscountRate($user));
    }
}
