<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function getUnreadMessages() { 
        $user = Auth::user();

        $unreadMessages = Notification::where('dark_user_id', $user->id)
        ->where('is_read', false)
        ->join('dark_users', 'dark_users.id', '=', 'notifications.sender_id')  
        ->select('notifications.*', 'dark_users.name as sender_name', 'dark_users.lastname as sender_lastname')  
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
