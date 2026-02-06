<?php

namespace App\Http\Controllers;

use App\Models\MessageDelivery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications.
     */
    public function index(): View
    {
        $user = auth()->user();

        $notifications = MessageDelivery::forUser($user->id)
            ->with('message')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $unreadCount = MessageDelivery::unreadForUser($user->id)->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(MessageDelivery $delivery): RedirectResponse
    {
        // Ensure the user owns this delivery
        if ($delivery->recipient_user_id !== auth()->id()) {
            abort(403);
        }

        $delivery->markAsRead();

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): RedirectResponse
    {
        MessageDelivery::unreadForUser(auth()->id())
            ->update([
                'status' => 'read',
                'read_at' => now(),
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
