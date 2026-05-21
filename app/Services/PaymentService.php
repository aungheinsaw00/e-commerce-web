<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PaymentService
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    public function initiatePayment(Order $order, string $provider = 'transfer'): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'provider' => $provider,
            'status' => Payment::STATUS_PENDING,
            'amount' => $order->total,
            'currency' => $order->currency,
        ]);
    }

    public function uploadProof(Payment $payment, UploadedFile $file): Payment
    {
        $hash = hash_file('sha256', $file->getRealPath());

        // Check for duplicate proof
        $duplicate = Payment::where('proof_hash', $hash)
            ->where('id', '!=', $payment->id)
            ->exists();

        if ($duplicate) {
            throw new \RuntimeException('This payment proof has already been submitted for another order.');
        }

        $path = $file->store('payment-proofs', 'private');

        $payment->update([
            'proof_path' => $path,
            'proof_hash' => $hash,
        ]);

        return $payment->fresh();
    }

    public function verifyPayment(Payment $payment): void
    {
        $payment->update([
            'status' => Payment::STATUS_VERIFIED,
            'paid_at' => now(),
        ]);

        $this->orderService->markAsPaid($payment->order, $payment);
    }

    public function rejectPayment(Payment $payment): void
    {
        $payment->update([
            'status' => Payment::STATUS_FAILED,
        ]);

        $this->orderService->cancelOrder($payment->order);
    }
}
