<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Models\Notification;
use App\Models\User;

class NotifyAdminOrderPlaced
{
    /**
     * Dispatch only after the DB transaction has fully committed.
     */
    public bool $afterCommit = true;

    public function __construct(private \App\Services\NotificationService $notificationService) {}

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        $userName = $order->user ? $order->user->name : 'a user';
        $total    = number_format((float) $order->total, 2);

        $this->notificationService->createForAdmins(
            Notification::TYPE_ORDER_PLACED,
            'New Order Placed',
            "Order #{$order->id} has been placed by {$userName} for {$total} {$order->currency}.",
            [
                'order_id'  => $order->id,
                'user_id'   => $order->user_id,
                'total'     => $order->total,
                'currency'  => $order->currency,
                'route'     => '/admin/orders/{id}',
            ]
        );
    }
}
