<?php

namespace App\Listeners;

use App\Events\RefundRejected;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyUserRefundRejected implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(RefundRejected $event): void
    {
        $refund = $event->refundRequest;

        Notification::create([
            'user_id'  => $refund->user_id,
            'type'     => Notification::TYPE_REFUND_REJECTED,
            'title'    => 'Your Refund Request Was Rejected',
            'message'  => "Your refund request for Order #{$refund->order_id} has been rejected." .
                ($refund->admin_note ? " Reason: {$refund->admin_note}" : ''),
            'metadata' => [
                'refund_request_id' => $refund->id,
                'order_id'          => $refund->order_id,
            ],
        ]);
    }
}
