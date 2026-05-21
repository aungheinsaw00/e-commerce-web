<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly CartService $cartService,
    ) {}

    public function createFromCart(User $user, Cart $cart, ?string $notes = null): Order
    {
        return DB::transaction(function () use ($user, $cart, $notes) {
            $summary = $this->cartService->getCartSummary($cart);

            if (empty($summary['items'])) {
                throw new \RuntimeException('Cart is empty.');
            }

            // Validate stock availability for all items
            foreach ($summary['items'] as $item) {
                if (!$this->inventoryService->checkAvailability($item['variant']->id, $item['quantity'])) {
                    throw new \RuntimeException("Insufficient stock for {$item['product']->name} ({$item['variant']->sku})");
                }
            }

            $order = Order::create([
                'user_id' => $user->id,
                'status' => Order::STATUS_PENDING,
                'subtotal' => $summary['subtotal'],
                'discount_total' => $summary['discount_total'],
                'total' => $summary['total'],
                'notes' => $notes,
            ]);

            foreach ($summary['items'] as $item) {
                $order->orderItems()->create([
                    'product_id' => $item['product']->id,
                    'variant_id' => $item['variant']->id,
                    'product_name_snapshot' => $item['product']->name,
                    'sku_snapshot' => $item['variant']->sku,
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] / max($item['quantity'], 1),
                    'final_price' => ($item['final_line_total']) / max($item['quantity'], 1),
                    'quantity' => $item['quantity'],
                ]);

                // Reserve stock
                $this->inventoryService->reserveStock(
                    $item['variant']->id,
                    $item['quantity'],
                    $order->id
                );
            }

            $this->cartService->clearCart($cart);

            return $order->load('orderItems');
        });
    }

    public function cancelOrder(Order $order): void
    {
        if (!$order->isCancellable()) {
            throw new \RuntimeException('Order cannot be cancelled in its current state.');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->orderItems as $item) {
                if ($item->variant_id) {
                    $this->inventoryService->releaseStock(
                        $item->variant_id,
                        $item->quantity,
                        $order->id
                    );
                }
            }

            $order->update(['status' => Order::STATUS_CANCELLED]);
        });
    }

    public function markAsPaid(Order $order, Payment $payment): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->orderItems as $item) {
                if ($item->variant_id) {
                    $this->inventoryService->deductStock(
                        $item->variant_id,
                        $item->quantity,
                        $order->id
                    );
                }
            }

            $order->update(['status' => Order::STATUS_PAID]);
        });
    }

    public function updateStatus(Order $order, string $status): void
    {
        $order->update(['status' => $status]);
    }
}
