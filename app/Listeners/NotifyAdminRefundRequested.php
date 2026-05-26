<?php

namespace App\Listeners;

use App\Events\RefundRequested;
use App\Models\Notification;
use App\Models\User;

class NotifyAdminRefundRequested
{
    public bool $afterCommit = true;

    public function __construct(private \App\Services\NotificationService $notificationService) {}

    public function handle(RefundRequested $event): void
    {
        $refund = $event->refundRequest;
        $order  = $refund->order;
        $orderId = $order ? $order->id : 'N/A';

        $this->notificationService->createForAdmins(
            Notification::TYPE_REFUND_REQUESTED,
            "Refund Requested — Order #{$orderId}",
            "A refund has been requested for Order #{$orderId}. Reason: {$refund->reason}",
            [
                'refund_request_id' => $refund->id,
                'order_id'          => $refund->order_id,
                'user_id'           => $refund->user_id,
            ]
        );
    }
}
