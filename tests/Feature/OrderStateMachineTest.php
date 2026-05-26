<?php

namespace Tests\Feature;

use App\Exceptions\InvalidOrderTransitionException;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private function createOrderForUser(User $user, int $stock = 50): Order
    {
        $category = Category::factory()->create();
        $product  = Product::factory()->create(['category_id' => $category->id, 'base_price' => 10.00]);
        $variant  = ProductVariant::factory()->create(['product_id' => $product->id]);
        Inventory::factory()->create([
            'variant_id'       => $variant->id,
            'stock_quantity'   => $stock,
            'reserved_quantity' => 0,
        ]);

        $cart = app(CartService::class)->getOrCreateCart($user);
        app(CartService::class)->addItem($cart, $variant->id, 1);

        return app(OrderService::class)->createFromCart($user, $cart, PaymentMethod::factory()->create()->id);
    }

    public function test_valid_transition_pending_payment_to_pending(): void
    {
        $user  = User::factory()->create();
        $order = $this->createOrderForUser($user);

        $this->assertEquals(Order::STATUS_PENDING_PAYMENT, $order->status);

        app(OrderService::class)->updateStatus($order, Order::STATUS_PENDING);

        $this->assertEquals(Order::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_valid_transition_pending_to_paid(): void
    {
        $user  = User::factory()->create();
        $order = $this->createOrderForUser($user);

        app(OrderService::class)->updateStatus($order, Order::STATUS_PENDING);
        app(OrderService::class)->updateStatus($order->fresh(), Order::STATUS_PAID);

        $this->assertEquals(Order::STATUS_PAID, $order->fresh()->status);
    }

    public function test_valid_transition_paid_to_shipped(): void
    {
        $user  = User::factory()->create();
        $order = $this->createOrderForUser($user);

        app(OrderService::class)->updateStatus($order, Order::STATUS_PENDING);
        app(OrderService::class)->updateStatus($order->fresh(), Order::STATUS_PAID);
        app(OrderService::class)->updateStatus($order->fresh(), Order::STATUS_SHIPPED);

        $this->assertEquals(Order::STATUS_SHIPPED, $order->fresh()->status);
    }

    public function test_invalid_transition_throws_exception(): void
    {
        $user  = User::factory()->create();
        $order = $this->createOrderForUser($user);

        // Cannot go from pending_payment directly to shipped (backward/invalid)
        $this->expectException(InvalidOrderTransitionException::class);

        app(OrderService::class)->updateStatus($order, Order::STATUS_SHIPPED);
    }

    public function test_cannot_transition_from_cancelled(): void
    {
        $user  = User::factory()->create();
        $order = $this->createOrderForUser($user);

        app(OrderService::class)->cancelOrder($order);

        $this->expectException(InvalidOrderTransitionException::class);

        app(OrderService::class)->updateStatus($order->fresh(), Order::STATUS_PAID);
    }

    public function test_cannot_transition_from_completed(): void
    {
        $user  = User::factory()->create();
        $order = $this->createOrderForUser($user);

        $order->update(['status' => Order::STATUS_COMPLETED]);

        $this->expectException(InvalidOrderTransitionException::class);

        app(OrderService::class)->updateStatus($order->fresh(), Order::STATUS_SHIPPED);
    }

    public function test_status_history_recorded_on_transition(): void
    {
        $user  = User::factory()->create();
        $order = $this->createOrderForUser($user);

        // The creation itself records the first history entry
        $this->assertDatabaseHas('order_status_histories', [
            'order_id'  => $order->id,
            'to_status' => Order::STATUS_PENDING_PAYMENT,
        ]);

        app(OrderService::class)->updateStatus($order, Order::STATUS_PENDING, 'Payment verified manually.');

        $this->assertDatabaseHas('order_status_histories', [
            'order_id'    => $order->id,
            'from_status' => Order::STATUS_PENDING_PAYMENT,
            'to_status'   => Order::STATUS_PENDING,
        ]);
    }

    public function test_cancel_from_pending_payment(): void
    {
        $user  = User::factory()->create();
        $order = $this->createOrderForUser($user);

        app(OrderService::class)->cancelOrder($order);

        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->status);
    }

    public function test_cancel_from_processing(): void
    {
        $user  = User::factory()->create();
        $order = $this->createOrderForUser($user);

        // Move to paid/processing state
        app(OrderService::class)->updateStatus($order, Order::STATUS_PENDING);
        app(OrderService::class)->updateStatus($order->fresh(), Order::STATUS_PAID);
        app(OrderService::class)->updateStatus($order->fresh(), Order::STATUS_PROCESSING);

        // Should be cancellable from processing
        app(OrderService::class)->cancelOrder($order->fresh());
        $this->assertEquals(Order::STATUS_CANCELLED, $order->fresh()->status);
    }
}
