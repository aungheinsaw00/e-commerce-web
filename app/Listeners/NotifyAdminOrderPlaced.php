<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAdminOrderPlaced implements ShouldQueue
{
    /**
     * Dispatch only after the DB transaction has fully committed.
     */
    public bool $afterCommit = true;

    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $userName = $order->user ? $order->user->name : 'a user';
            $total    = number_format((float) $order->total, 2);

            Notification::create([
                'user_id' => $admin->id,
                'type'    => Notification::TYPE_ORDER_PLACED,
                'title'   => 'New Order Placed',
                'message' => "Order #{$order->id} has been placed by {$userName} for {$total} {$order->currency}.",
                'metadata' => [
                    'order_id'  => $order->id,
                    'user_id'   => $order->user_id,
                    'total'     => $order->total,
                    'currency'  => $order->currency,
                ],
            ]);
        }
    }
}
