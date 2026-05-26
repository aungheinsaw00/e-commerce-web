<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;

class NotificationPolicy
{
    /**
     * Users can only mark their own notifications as read.
     */
    public function update(User $user, Notification $notification): bool
    {
        return $notification->user_id === $user->id;
    }
}
