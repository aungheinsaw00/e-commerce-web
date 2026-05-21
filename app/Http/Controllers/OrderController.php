<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly CartService $cartService,
        private readonly PaymentService $paymentService,
    ) {}

    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('orderItems', 'latestPayment')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('orders', compact('orders'));
    }

    public function create(Request $request)
    {
        $cart = $this->cartService->getOrCreateCart($request->user());
        $summary = $this->cartService->getCartSummary($cart);

        return view('orders.create', compact('summary'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_proof' => ['required', 'image', 'max:5120'], // 5MB max
        ]);

        $cart = $this->cartService->getOrCreateCart($request->user());

        try {
            $order = $this->orderService->createFromCart(
                $request->user(),
                $cart,
                $request->input('notes')
            );

            $payment = $this->paymentService->initiatePayment($order, 'transfer');

            if ($request->hasFile('payment_proof')) {
                $this->paymentService->uploadProof($payment, $request->file('payment_proof'));
            }

            return redirect()->route('orders.index')
                ->with('success', 'Order placed successfully! Your payment proof is being reviewed.');

        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')
                ->with('error', $e->getMessage());
        }
    }
}