<?php

namespace App\Http\Controllers;

use App\Models\DarkUsers;
use App\Models\GroupMessage;
use App\Models\GroupUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\GroupUse;
use App\Events\GroupMessageEvent;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReportMessageMail;
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

    public function getSingleMessage($messageGroupId)
    {
        $user = Auth::user();

        if(!$user) { 
            return response()->json(['message' => 'No user found'], 404);
        }

        $groupMessage = GroupMessage::where('id', $messageGroupId)->first();

        if(!$groupMessage) { 
            return response()->json(['message' => 'No group message found'], 400);
        }

        return response()->json($groupMessage);
    }

    public function reportGroupMessage(Request $request)
    {
        $user = Auth::user();
        $messageId = $request->input('message_id');
        $reason = $request->input('reason');
    
        $validReasons = ['harrasment', 'racism', 'innapropriate_words', 'fake_account'];
        if (!in_array($reason, $validReasons)) {
            return response()->json(['message' => 'Invalid reason provided.'], 422);
        }
    
        $message = GroupMessage::find($messageId);
        if(!$message) {
            return response()->json(['message' => 'No group message found'], 400);
        }
    
        $group = GroupUser::where('id', $message->group_id)->first();
        if(!$group) {
            return response()->json(['message' => 'No group has been found'], 400);
        }
    
        if($group->semi_admin_id !== $user->id) {
            return response()->json(['message' => "You are not authorized"], 403);
        }
    
        $suspect = DarkUsers::find($message->sent_by);
        if(!$suspect) {
            return response()->json(['message' => 'No suspect found'], 400);
        }
    
        $groupOwner = DarkUsers::find($group->admin_id);
        if (!$groupOwner) {
            return response()->json(['message' => 'Group owner not found'], 400);
        }
    
        Mail::to($group->owner_email)->send(
            new ReportMessageMail(
                $groupOwner,
                $user,
                $suspect,
                $message->message,
                $user->email,
                $reason // ✅ new param
            )
        );
    
        return response()->json(['success' => 'Report sent to the group owner.']);
    }
    
}
