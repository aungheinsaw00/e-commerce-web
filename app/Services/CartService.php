<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getOrCreateCart(?User $user, ?string $sessionId = null): Cart
    {
        if ($user) {
            return Cart::firstOrCreate(['user_id' => $user->id]);
        }

        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    public function addItem(Cart $cart, int $variantId, int $quantity = 1): CartItem
    {
        $item = CartItem::where('cart_id', $cart->id)
            ->where('variant_id', $variantId)
            ->first();

        if ($item) {
            $item->increment('quantity', $quantity);
            return $item->fresh();
        }

        return CartItem::create([
            'cart_id' => $cart->id,
            'variant_id' => $variantId,
            'quantity' => $quantity,
        ]);
    }

    public function updateQuantity(Cart $cart, int $variantId, int $quantity): CartItem
    {
        $item = CartItem::where('cart_id', $cart->id)
            ->where('variant_id', $variantId)
            ->firstOrFail();

        $item->update(['quantity' => $quantity]);

        return $item->fresh();
    }

    public function removeItem(Cart $cart, int $variantId): void
    {
        CartItem::where('cart_id', $cart->id)
            ->where('variant_id', $variantId)
            ->delete();
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }

    public function mergeGuestCart(string $sessionId, User $user): Cart
    {
        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);
        $guestCart = Cart::where('session_id', $sessionId)->first();

        if (!$guestCart) {
            return $userCart;
        }

        DB::transaction(function () use ($userCart, $guestCart) {
            foreach ($guestCart->items as $guestItem) {
                $existing = $userCart->items()
                    ->where('variant_id', $guestItem->variant_id)
                    ->first();

                if ($existing) {
                    $existing->increment('quantity', $guestItem->quantity);
                } else {
                    CartItem::create([
                        'cart_id' => $userCart->id,
                        'variant_id' => $guestItem->variant_id,
                        'quantity' => $guestItem->quantity,
                    ]);
                }
            }

            $guestCart->items()->delete();
            $guestCart->delete();
        });

        return $userCart->fresh(['items.variant.product']);
    }

    public function getCartSummary(Cart $cart): array
    {
        $cart->load('items.variant.product.discounts', 'items.variant.inventory');

        $items = [];
        $subtotal = 0;
        $discountTotal = 0;

        foreach ($cart->items as $cartItem) {
            $variant = $cartItem->variant;
            $product = $variant->product;
            $price = $variant->effective_price;
            $lineTotal = $price * $cartItem->quantity;

            $discount = $this->calculateDiscount($product, $cartItem->quantity, $price);
            $discountAmount = $discount['amount'];
            $finalLineTotal = $lineTotal - $discountAmount;

            $items[] = [
                'cart_item' => $cartItem,
                'variant' => $variant,
                'product' => $product,
                'unit_price' => $price,
                'quantity' => $cartItem->quantity,
                'line_total' => $lineTotal,
                'discount_amount' => $discountAmount,
                'discount_info' => $discount['info'],
                'final_line_total' => $finalLineTotal,
                'available_stock' => $variant->available_stock,
            ];

            $subtotal += $lineTotal;
            $discountTotal += $discountAmount;
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'total' => $subtotal - $discountTotal,
        ];
    }

    private function calculateDiscount($product, int $quantity, float $unitPrice): array
    {
        $discount = $product->discounts()
            ->active()
            ->where('min_quantity', '<=', $quantity)
            ->orderByDesc('value')
            ->first();

        if (!$discount) {
            // Check category-level discounts
            $discount = \App\Models\Discount::active()
                ->forCategory($product->category_id)
                ->where('min_quantity', '<=', $quantity)
                ->orderByDesc('value')
                ->first();
        }

        if (!$discount) {
            return ['amount' => 0, 'info' => null];
        }

        $lineTotal = $unitPrice * $quantity;

        if ($discount->type === 'percentage') {
            $amount = $lineTotal * ($discount->value / 100);
        } else {
            $amount = min($discount->value * $quantity, $lineTotal);
        }

        return [
            'amount' => round($amount, 2),
            'info' => [
                'name' => $discount->name,
                'type' => $discount->type,
                'value' => $discount->value,
            ],
        ];
    }
}
