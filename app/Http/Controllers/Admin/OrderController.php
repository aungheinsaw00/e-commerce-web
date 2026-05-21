<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {}

    public function index(Request $request)
    {
        $query = Order::with('user', 'latestPayment')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('user', 'orderItems', 'payments');

        return view('admin.orders.show', compact('order'));
    }

    public function verifyPayment(Payment $payment)
    {
        $this->paymentService->verifyPayment($payment);

        return redirect()->back()->with('success', 'Payment verified. Order marked as paid.');
    }

    public function rejectPayment(Payment $payment)
    {
        $this->paymentService->rejectPayment($payment);

        return redirect()->back()->with('success', 'Payment rejected. Order cancelled.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:processing,shipped,completed'],
        ]);

        $order->update(['status' => $request->input('status')]);

        return redirect()->back()->with('success', 'Order status updated.');
    }
}
