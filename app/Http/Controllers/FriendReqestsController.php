<?php

namespace App\Http\Controllers;

use App\Events\FriendRequestAccepted;
use App\Events\FriendRequestRemoved;
use App\Events\FriendRequestSent;
use App\Filament\Resources\FriendRequestsResource;
use App\Models\BlockedUsers;
use App\Models\DarkUsers;
// use Illuminate\Container\Attributes\Log;
use App\Models\FriendRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FriendReqestsController extends Controller
{
    public function index()
    {
        $friendRequests = FriendRequests::all();

        return FriendRequestsResource::collection($friendRequests);
    }

    public function sendRequest(Request $request, $request_id)
    {
        // $sender = Auth::user();
        $sender = DarkUsers::find(Auth::user()->id);
        $reciever = DarkUsers::where('request_id', $request_id)->first();


        if (!$sender || !$reciever) {
            return response()->json(['message' => 'Invalid Request Id'], 400);
        }

        $blocked = BlockedUsers::where('blocker_id', $reciever->id)
            ->where('blocked_id', $sender->id)
            ->exists(); 

        if ($blocked) {
            return response()->json(['message' => 'User blocked you'], 400);
        }

        $blockedBySender = BlockedUsers::where('blocker_id', $sender->id)
            ->where('blocked_id', $reciever->id)
            ->exists();

        if ($blockedBySender) {
            return response()->json(['message' => 'User blocked you'], 400);
        }

        $existingRequest = FriendRequests::where('dark_user_id', $reciever->id)
            ->where('request_friend_id', $sender->request_id) 
            ->first();


        if ($existingRequest) {
            return response()->json(['message' => 'Friend Request already has been sent'], 400);
        }

        event(new FriendRequestSent($sender, $reciever));

        FriendRequests::create([
            'dark_user_id' => $reciever->id,
            'request_friend_id' => $sender->request_id,
            'is_accepted' => null,
            'pending' => json_encode([$sender->id]),
        ]);

        return response()->json(['message' => 'Friend Request Sent']);
    }

    public function unfriend($request_id)
    {
        // $user = Auth::user();
        $user = DarkUsers::find(Auth::user()->id);
        $friend = DarkUsers::where('request_id', $request_id)->first();

        if (!$friend) {
            return response()->json(['message' => 'User not found'], 400);
        }

        FriendRequests::where(function ($query) use ($user, $friend) {
            $query->where('dark_user_id', $user->id)
                ->where('request_friend_id', $friend->request_id);
        })->orWhere(function ($query) use ($user, $friend) {
            $query->where('dark_user_id', $friend->id)
                ->where('request_friend_id', $user->request_id);
        })->delete();

        event(new FriendRequestRemoved($user, $friend));


        return response()->json(['message' => 'Friend removed successfully']);
    }

    public function respondRequest(Request $request, $senderRequestId)
    {
        // $receiver = Auth::user();
        $receiver = DarkUsers::find(Auth::user()->id);

        $sender = DarkUsers::where('request_id', $senderRequestId)->first();

        if (!$receiver || !$sender) {
            return response()->json(['message' => 'Invalid request id'], 400);
        }

        $friendRequest = FriendRequests::where('dark_user_id', $receiver->id)
            ->where('request_friend_id', $sender->request_id)
            ->first();

        if (!$friendRequest) {
            return response()->json(['message' => 'No friend request found'], 400);
        }

        if ($request->action === 'accept') {
            $receiverFriends = json_decode($friendRequest->friend, true) ?? [];
            $senderFriends = FriendRequests::where('dark_user_id', $sender->id)
                ->where('request_friend_id', $receiver->request_id)
                ->first();

            $receiverFriends[] = $sender->id;
            $friendRequest->update([
                'is_accepted' => true,
                'friend' => json_encode($receiverFriends),
                'pending' => null,
            ]);
            event(new FriendRequestAccepted($sender, $receiver));
            if (!$senderFriends) {
                FriendRequests::create([
                    'dark_user_id' => $sender->id,
                    'request_friend_id' => $receiver->request_id,
                    'is_accepted' => true,
                    'friend' => json_encode([$receiver->id]),
                    'pending' => null,
                ]);
            } else {
                $existingFriends = json_decode($senderFriends->friend, true) ?? [];
                $existingFriends[] = $receiver->id;
                $senderFriends->update([
                    'is_accepted' => true,
                    'friend' => json_encode($existingFriends),
                    'pending' => null,
                ]);
            }


            return response()->json(['message' => 'Friend Request Accepted']);
        } elseif ($request->action === 'reject') {
            $friendRequest->delete();

            return response()->json(['message' => 'Friend Request Rejected']);
        }

        return response()->json(['message' => 'Invalid action'], 400);
    }


    public function getFriends(Request $request)
    {
        $user = DarkUsers::where('request_id', $request->request_id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        $friendRequests = FriendRequests::where('dark_user_id', $user->id)
            ->where('is_accepted', true)
            ->get(['friend']);

        if ($friendRequests->isEmpty()) {
            // return response()->json(['message' => 'No friends found']);
            return response()->json([]); // Return empty array instead of message
        }

        $friendIds = [];
        foreach ($friendRequests as $request) {
            $friendIds = array_merge($friendIds, json_decode($request->friend, true)); 
        }

        $friends = DarkUsers::whereIn('id', $friendIds)->get(['id', 'name', 'lastname', 'request_id', 'gender', 'birthdate', 'age', 'picture']);

        return response()->json($friends);
    }

    public function getPendingRequest(Request $request)
    {
        $user = DarkUsers::where('request_id', $request->request_id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        $pendingRequest = FriendRequests::where('dark_user_id', $user->id)
            ->whereNull('is_accepted')
            ->with('requestFriend') 
            ->get(['request_friend_id']);

        if ($pendingRequest->isEmpty()) {
            // return response()->json(['message' => 'No pending requests']);
            return response()->json([]); // Return empty array instead of message

        }

        $pendingRequestDetails = $pendingRequest->map(function ($request) {
            if ($request->requestFriend) {
                $friend = $request->requestFriend;
                return [
                    'request_friend_id' => $friend->request_id,
                    'friend_id' => $friend->id,
                    'friend_name' => $friend->name,
                    'friend_picture_url' => $friend->picture,
                ];
            } else {
                Log::info('No related friend found for request_friend_id: ' . $request->request_friend_id);
                return [
                    'request_friend_id' => $request->request_friend_id,
                    'friend_id' => null,
                    'friend_name' => null,
                    'friend_picture_url' => null,
                ];
            }
        });

        return response()->json($pendingRequestDetails);
    }
}
