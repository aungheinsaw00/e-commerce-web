<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All auth users can see their own orders (filtered in controller)
    }

    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin() || $user->id === $order->user_id;
    }

    public function updateStatus(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    public function cancel(User $user, Order $order): bool
    {
        return ($user->id === $order->user_id && $order->isCancellable()) || $user->isAdmin();
    }
}
