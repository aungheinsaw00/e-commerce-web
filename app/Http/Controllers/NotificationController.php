<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    /**
     * List all notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $notifications = $this->notificationService->getAllForUser($request->user());
        $unreadCount   = $this->notificationService->unreadCount($request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'notifications' => $notifications,
                'unreadCount' => $unreadCount,
            ]);
        }

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, Notification $notification)
    {
        $this->authorize('update', $notification);

        $this->notificationService->markAsRead($notification);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Notification marked as read.']);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllRead(Request $request)
    {
        $this->notificationService->markAllAsRead($request->user());

        if ($request->expectsJson()) {
            return response()->json(['message' => 'All notifications marked as read.']);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
