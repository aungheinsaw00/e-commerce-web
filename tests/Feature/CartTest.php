<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function createVariantWithStock(int $stock = 50): ProductVariant
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        Inventory::factory()->create(['variant_id' => $variant->id, 'stock_quantity' => $stock]);
        return $variant;
    }

    public function test_add_item_to_cart(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariantWithStock();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addItem($cart, $variant->id, 3);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);
    }

    public function test_add_same_item_increments_quantity(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariantWithStock();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addItem($cart, $variant->id, 2);
        $cartService->addItem($cart, $variant->id, 3);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 5,
        ]);
    }

    public function test_update_cart_item_quantity(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariantWithStock();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addItem($cart, $variant->id, 2);
        $cartService->updateQuantity($cart, $variant->id, 7);

        $this->assertDatabaseHas('cart_items', [
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 7,
        ]);
    }

    public function test_remove_item_from_cart(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariantWithStock();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addItem($cart, $variant->id, 2);
        $cartService->removeItem($cart, $variant->id);

        $this->assertDatabaseMissing('cart_items', [
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
        ]);
    }

    public function test_clear_cart_removes_all_items(): void
    {
        $user = User::factory()->create();
        $v1 = $this->createVariantWithStock();
        $v2 = $this->createVariantWithStock();

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addItem($cart, $v1->id, 1);
        $cartService->addItem($cart, $v2->id, 2);
        $cartService->clearCart($cart);

        $this->assertEquals(0, $cart->items()->count());
    }

    public function test_cart_summary_calculates_totals(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'base_price' => 25.00,
        ]);
        $variant = ProductVariant::factory()->create(['product_id' => $product->id]);
        Inventory::factory()->create(['variant_id' => $variant->id, 'stock_quantity' => 100]);

        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart($user);
        $cartService->addItem($cart, $variant->id, 4);

        $summary = $cartService->getCartSummary($cart);

        $this->assertEquals(100.00, $summary['subtotal']);
        $this->assertEquals(100.00, $summary['total']);
        $this->assertCount(1, $summary['items']);
    }

    public function test_guest_cannot_access_cart_page(): void
    {
        $this->get('/cart')->assertRedirect('/login');
    }
}
