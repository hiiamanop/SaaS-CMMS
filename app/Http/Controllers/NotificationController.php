<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = auth()->user()->notifications()->latest()->paginate(20);
        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Notification $notification)
    {
        if ($notification->user_id !== auth()->id()) abort(403);
        $notification->markAsRead();
        return back()->with('success', 'Marked as read.');
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications()->update(['is_read' => true, 'read_at' => now()]);
        return back()->with('success', 'All notifications marked as read.');
    }

    public function getUnread()
    {
        $notifications = auth()->user()->unreadNotifications()->latest()->take(10)->get();
        $count = auth()->user()->unreadNotifications()->count();
        return response()->json(['notifications' => $notifications, 'count' => $count]);
    }
}
