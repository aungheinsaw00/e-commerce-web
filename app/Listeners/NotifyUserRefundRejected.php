<?php

namespace App\Listeners;

use App\Events\RefundRejected;
use App\Models\Notification;

class NotifyUserRefundRejected
{
    public bool $afterCommit = true;

    public function __construct(private \App\Services\NotificationService $notificationService) {}

    public function handle(RefundRejected $event): void
    {
        $refund = $event->refundRequest;
        $user = $refund->user;
        if (!$user) return;

        $this->notificationService->createForUser(
            $user,
            Notification::TYPE_REFUND_REJECTED,
            'Your Refund Request Was Rejected',
            "Your refund request for Order #{$refund->order_id} has been rejected." .
                ($refund->admin_note ? " Reason: {$refund->admin_note}" : ''),
            [
                'refund_request_id' => $refund->id,
                'order_id'          => $refund->order_id,
            ]
        );
    }
}
