<?php

namespace App\Http\Controllers;

use App\Models\BlockedUsers;
use App\Models\DarkUsers;
use App\Models\FriendRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BlockedUsersController extends Controller
{
    public function blockUsers($request_id)
    {
        $user = Auth::user();
        $blockedUser = DarkUsers::where('request_id', $request_id)->first();

        if (!$blockedUser) {
            return response()->json(['message' => 'User not found'], 400);
        }

        FriendRequests::where(function ($query) use ($user, $blockedUser) {
            $query->where('dark_user_id', $user->id)
                ->where('request_friend_id', $blockedUser->request_id);
        })->orWhere(function ($query) use ($user, $blockedUser) {
            $query->where('dark_user_id', $blockedUser->id)
                ->where('request_friend_id', $user->request_id);
        })->delete();

        BlockedUsers::updateOrCreate(
            ['blocker_id' => $user->id, 'blocked_id' => $blockedUser->id],
            ['created_at' => now()]
        );

        return response()->json(['message' => 'User blocked successfully']);
    }
    public function getBlockedUsers()
    {
        $user = Auth::user();

        $blockedUsers = BlockedUsers::where('blocker_id', $user->id)
            ->with('blockedUser') 
            ->get()
            ->map(function ($blockedUser) {
                return $blockedUser->blockedUser;  
            });

        if ($blockedUsers->isEmpty()) {
            return response()->json(['message' => 'No blocked users found'], 200);
        }

        return response()->json($blockedUsers, 200);  
    }

    public function getUsersWhoBlockedMe()
    {
        $userId = Auth::user()->id;

        $blockedByUsers = DB::table('blocked_users')
            ->where('blocked_users.blocked_id', $userId)
            ->join('dark_users', 'blocked_users.blocker_id', '=', 'dark_users.id')
            ->select('dark_users.id', 'dark_users.name', 'dark_users.picture')
            ->get();

        return response()->json($blockedByUsers);
    }
}
