<?php

namespace App\Http\Controllers;

use App\Models\DarkUsers;
use App\Models\GroupMessage;
use App\Models\GroupUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\GroupUse;
use App\Events\GroupMessageEvent;
class GroupMessageController extends Controller
{
    public function sendMessage(Request $request, $groupId)
    {
        $request->validate([
            'message' => ['required', 'string', 'min:1', 'max:100']
        ]);

        $user = Auth::user();  

        if (!$user) {
            return response()->json(['message' => 'No user found'], 400);
        }

        $groupUser = GroupUser::where('id', $groupId)
            ->where(function ($query) use ($user) {
                $query->whereJsonContains('users_in_group', $user->id);  
            })
            ->first();

        if (!$groupUser) {
            return response()->json(['message' => 'User is not in this group'], 400);
        }

        $groupId = $groupUser->id;  

        $message = GroupMessage::create([
            'group_id' => $groupId,  
            'message' => $request->message,
            'sent_by' => $user->id,
            'sent_at' => now(),
        ]);

        broadcast(new GroupMessageEvent($message)); 

        return response()->json(['message' => $request->message], 200);
    }


    public function getMessagess($groupId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'No user found'], 400);
        }

        $group = GroupUser::where('id', $groupId)
            ->where(function ($query) use ($user) {
                $query->where('dark_user_id', $user->id)
                    ->orWhereJsonContains('users_in_group', $user->id);
            })
            ->first();


        if (!$group) {
            return response()->json(['message' => 'No group found'], 400);
        }

        $message = GroupMessage::where('group_id', $groupId)->get();

        return response()->json($message);
    }

    public function getUsersInGroup($groupId)
    {
        $group = GroupUser::findOrFail($groupId);

        $userIds = $group->users_in_group;

        $users = DarkUsers::whereIn('id', $userIds)->get();

        return response()->json($users);
    }
}
