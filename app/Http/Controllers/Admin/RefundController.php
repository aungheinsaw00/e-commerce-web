<?php

namespace App\Http\Controllers\Admin;

use App\Events\RefundApproved;
use App\Events\RefundRejected;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RefundRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    /**
     * List all pending (and recent) refund requests.
     */
    public function index(Request $request)
    {
        $query = RefundRequest::with('order', 'user')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $refundRequests = $query->paginate(20)->withQueryString();

        return view('admin.refunds.index', compact('refundRequests'));
    }

    /**
     * Admin approves a refund request.
     */
    public function approve(Request $request, RefundRequest $refundRequest)
    {
        if (!$refundRequest->isPending()) {
            return redirect()->back()->with('error', 'This refund request has already been reviewed.');
        }

        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $refundRequest) {
            $refundRequest->update([
                'status'      => RefundRequest::STATUS_APPROVED,
                'admin_note'  => $request->input('admin_note'),
                'reviewed_at' => now(),
            ]);

            // Update order status to refunded
            $refundRequest->order->update(['status' => Order::STATUS_REFUNDED]);
        });

        event(new RefundApproved($refundRequest->fresh()));

        return redirect()->back()->with('success', 'Refund request approved and order marked as refunded.');
    }

    /**
     * Admin rejects a refund request.
     */
    public function reject(Request $request, RefundRequest $refundRequest)
    {
        if (!$refundRequest->isPending()) {
            return redirect()->back()->with('error', 'This refund request has already been reviewed.');
        }

        $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $refundRequest) {
            $refundRequest->update([
                'status'      => RefundRequest::STATUS_REJECTED,
                'admin_note'  => $request->input('admin_note'),
                'reviewed_at' => now(),
            ]);

            // Revert order status to completed
            $refundRequest->order->update(['status' => Order::STATUS_COMPLETED]);
        });

        event(new RefundRejected($refundRequest->fresh()));

        return redirect()->back()->with('success', 'Refund request rejected.');
    }
}
