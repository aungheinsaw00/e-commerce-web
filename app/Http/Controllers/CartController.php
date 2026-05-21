<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {}

    public function index(Request $request)
    {
        $cart = $this->cartService->getOrCreateCart($request->user());
        $summary = $this->cartService->getCartSummary($cart);

        return view('cart.index', $summary);
    }

    public function add(Request $request)
    {
        $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->cartService->getOrCreateCart($request->user());
        $this->cartService->addItem($cart, $request->integer('variant_id'), $request->integer('quantity'));

        return redirect()->route('cart.index')->with('success', 'Item added to cart.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $this->cartService->getOrCreateCart($request->user());
        $this->cartService->updateQuantity($cart, $request->integer('variant_id'), $request->integer('quantity'));

        return redirect()->route('cart.index');
    }

    public function remove(Request $request)
    {
        $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
        ]);

        $cart = $this->cartService->getOrCreateCart($request->user());
        $this->cartService->removeItem($cart, $request->integer('variant_id'));

        return redirect()->route('cart.index');
    }
}