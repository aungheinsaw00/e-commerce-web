<?php

namespace App\Listeners;

use App\Events\RefundApproved;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyUserRefundApproved implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(RefundApproved $event): void
    {
        $refund = $event->refundRequest;

        Notification::create([
            'user_id'  => $refund->user_id,
            'type'     => Notification::TYPE_REFUND_APPROVED,
            'title'    => 'Your Refund Has Been Approved',
            'message'  => "Your refund request for Order #{$refund->order_id} has been approved." .
                ($refund->admin_note ? " Note: {$refund->admin_note}" : ''),
            'metadata' => [
                'refund_request_id' => $refund->id,
                'order_id'          => $refund->order_id,
            ],
        ]);
    }
}
