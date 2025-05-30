<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function getUnreadMessages()
    {
    $user = Auth::user();

    $unreadMessages = Notification::where('notifications.dark_user_id', $user->id)
        ->where('notifications.is_read', false)
        ->join('dark_users as sender', 'sender.id', '=', 'notifications.sender_id') // joining using request_id
        ->select(
            'notifications.*',
            'sender.name as sender_name',
            'sender.lastname as sender_lastname'
        )
        ->get();

        return response()->json($unreadMessages);
    }
    public function markNotificationRead() { 
        $user = Auth::user();

        Notification::where('dark_user_id', $user->id)
        ->update(['is_read' => true]);

        return response()->json(['message' => 'Notifications marked as read']);
    }
}
