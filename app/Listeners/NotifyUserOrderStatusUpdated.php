<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyUserOrderStatusUpdated implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(OrderStatusUpdated $event): void
    {
        $order = $event->order;

        // Load the order owner
        $user = $order->user;
        if (!$user) {
            return;
        }

        $from = ucfirst(str_replace('_', ' ', $event->fromStatus));
        $to   = ucfirst(str_replace('_', ' ', $event->toStatus));

        Notification::create([
            'user_id' => $user->id,
            'type'    => Notification::TYPE_ORDER_STATUS_UPDATED,
            'title'   => "Order #{$order->id} Status Updated",
            'message' => "Your order #{$order->id} status has changed from '{$from}' to '{$to}'.",
            'metadata' => [
                'order_id'    => $order->id,
                'from_status' => $event->fromStatus,
                'to_status'   => $event->toStatus,
            ],
        ]);
    }
}
