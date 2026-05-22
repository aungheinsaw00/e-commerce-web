<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {}

    public function index(Request $request)
    {
        $query = Order::with('user', 'latestPayment', 'paymentMethod')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('user', 'orderItems', 'payments', 'paymentMethod', 'latestPayment');

        return view('admin.orders.show', compact('order'));
    }

    public function downloadProof(Payment $payment): StreamedResponse
    {
        if (!$payment->proof_path || !Storage::disk('private')->exists($payment->proof_path)) {
            abort(404);
        }

        return Storage::disk('private')->download($payment->proof_path);
    }

    public function markPaid(Order $order)
    {
        if ($order->status !== Order::STATUS_PENDING_PAYMENT) {
            return redirect()->back()
                ->with('error', 'Only orders awaiting payment can be marked as paid.');
        }

        $payment = $order->latestPayment
            ?? $this->paymentService->initiatePayment($order);

        $this->paymentService->verifyPayment($payment);

        return redirect()->back()->with('success', 'Order marked as paid.');
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
