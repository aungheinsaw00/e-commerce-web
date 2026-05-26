<?php

namespace App\Listeners;

use App\Events\RefundApproved;
use App\Models\Notification;

class NotifyUserRefundApproved
{
    public bool $afterCommit = true;

    public function __construct(private \App\Services\NotificationService $notificationService) {}

    public function handle(RefundApproved $event): void
    {
        $refund = $event->refundRequest;
        $user = $refund->user;
        if (!$user) return;

        $this->notificationService->createForUser(
            $user,
            Notification::TYPE_REFUND_APPROVED,
            'Your Refund Has Been Approved',
            "Your refund request for Order #{$refund->order_id} has been approved." .
                ($refund->admin_note ? " Note: {$refund->admin_note}" : ''),
            [
                'refund_request_id' => $refund->id,
                'order_id'          => $refund->order_id,
            ]
        );
    }
}
