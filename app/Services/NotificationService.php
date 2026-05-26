<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    /**
     * Create a notification for a specific user.
     */
    public function createForUser(
        User $user,
        string $type,
        string $title,
        string $message,
        array $metadata = [],
    ): Notification {
        return Notification::create([
            'user_id'  => $user->id,
            'type'     => $type,
            'title'    => $title,
            'message'  => $message,
            'metadata' => $metadata ?: null,
        ]);
    }

    /**
     * Broadcast a notification to all admin users.
     */
    public function createForAdmins(
        string $type,
        string $title,
        string $message,
        array $metadata = [],
    ): void {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $this->createForUser($admin, $type, $title, $message, $metadata);
        }
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->markAsRead();
    }

    /**
     * Mark all of a user's unread notifications as read.
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::forUser($user)
            ->unread()
            ->update(['read_at' => now()]);
    }

    /**
     * Get paginated unread notifications for a user.
     */
    public function getUnread(User $user, int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Notification::forUser($user)
            ->unread()
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Get all notifications (read + unread) for a user, paginated.
     */
    public function getAllForUser(User $user, int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Notification::forUser($user)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Count unread notifications for a user (for badge display).
     */
    public function unreadCount(User $user): int
    {
        return Notification::forUser($user)->unread()->count();
    }
}
