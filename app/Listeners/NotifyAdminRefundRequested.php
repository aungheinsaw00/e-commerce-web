<?php

namespace App\Listeners;

use App\Events\RefundRequested;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminRefundRequested implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(RefundRequested $event): void
    {
        $refund = $event->refundRequest;
        $order  = $refund->order;

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $orderId = $order ? $order->id : 'N/A';

            Notification::create([
                'user_id'  => $admin->id,
                'type'     => Notification::TYPE_REFUND_REQUESTED,
                'title'    => "Refund Requested — Order #{$orderId}",
                'message'  => "A refund has been requested for Order #{$orderId}. Reason: {$refund->reason}",
                'metadata' => [
                    'refund_request_id' => $refund->id,
                    'order_id'          => $refund->order_id,
                    'user_id'           => $refund->user_id,
                ],
            ]);
        }
    }
}
