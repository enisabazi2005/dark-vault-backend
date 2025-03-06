<?php

namespace App\Http\Controllers;

use App\Filament\Resources\FriendRequestsResource;
use App\Models\DarkUsers;
use App\Models\FriendRequests;
// use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class FriendReqestsController extends Controller
{
    public function index() {
        $friendRequests = FriendRequests::all();

        return FriendRequestsResource::collection($friendRequests);
    }

    public function sendRequest(Request $request, $request_id) { 
        // $sender = DarkUsers::where('request_id', $request->sender_request_id)->first();
        // $sender = Auth::user()->id;
        $sender = Auth::user();
        // dd($sender);
        // $reciever = DarkUsers::where('request_id', $request->reciever_request_id)->first();
        $reciever = DarkUsers::where('request_id', $request_id)->first();


        if(!$sender || !$reciever) { 
            return response()->json(['message' => 'Invalid Request Id'], 400);
        }

        $existingRequest = FriendRequests::where('dark_user_id', $reciever->id)
        ->where('request_friend_id', $sender->request_id) // ✅ Now this works
        ->first();
    

        if($existingRequest) { 
            return response()->json(['message' => 'Friend Request already has been sent'] , 400);
        }

        FriendRequests::create([
            'dark_user_id' => $reciever->id,
            'request_friend_id' => $sender->request_id,
            'is_accepted' => null,
            'pending' => json_encode([$sender->id]),
        ]);

        return response()->json(['message' => 'Friend Request Sent']);
    }

    public function respondRequest(Request $request, $senderRequestId) { 
        $receiver = Auth::user(); 
    
        $sender = DarkUsers::where('request_id', $senderRequestId)->first();
    
        if (!$receiver || !$sender) { 
            return response()->json(['message' => 'Invalid request id'], 400);
        }
    
        // Find the friend request where the receiver is the authenticated user
        $friendRequest = FriendRequests::where('dark_user_id', $receiver->id)
            ->where('request_friend_id', $sender->request_id)
            ->first();
    
        if (!$friendRequest) { 
            return response()->json(['message' => 'No friend request found'], 400);
        }
    
        if ($request->action === 'accept') { 
            // Ensure 'friend' field is an array before merging
            $receiverFriends = json_decode($friendRequest->friend, true) ?? [];
            $senderFriends = FriendRequests::where('dark_user_id', $sender->id)
                ->where('request_friend_id', $receiver->request_id)
                ->first();
    
            // Update receiver's friend list
            $receiverFriends[] = $sender->id;
            $friendRequest->update([
                'is_accepted' => true,
                'friend' => json_encode($receiverFriends),
                'pending' => null,
            ]);
    
            // Update sender's friend list (create if not exists)
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
            // Simply delete the friend request without adding them as a friend
            $friendRequest->delete();
    
            return response()->json(['message' => 'Friend Request Rejected']);
        }
    
        return response()->json(['message' => 'Invalid action'], 400);
    }
    
    
    public function getFriends(Request $request) { 
        // Retrieve the user by their request_id
        $user = DarkUsers::where('request_id', $request->request_id)->first();
    
        if (!$user) { 
            return response()->json(['message' => 'User not found'], 400);
        }
    
        // Retrieve accepted friends for the user
        $friendRequests = FriendRequests::where('dark_user_id', $user->id)
            ->where('is_accepted', true)
            ->get(['friend']);
    
        // Check if the user has any friends
        if ($friendRequests->isEmpty()) { 
            return response()->json(['message' => 'No friends found']);
        }
    
        // Decode the 'friend' field to get an array of friend IDs
        $friendIds = [];
        foreach ($friendRequests as $request) {
            $friendIds = array_merge($friendIds, json_decode($request->friend, true)); // Decoding the JSON
        }
    
        // Retrieve friends' details by their IDs
        $friends = DarkUsers::whereIn('id', $friendIds)->get(['id', 'name', 'lastname', 'request_id', 'gender', 'birthdate', 'age','picture']); 
    
        // Return the list of friends with more details
        return response()->json($friends);
    }
    
    public function getPendingRequest(Request $request) {
        // Retrieve the user based on the request_id
        $user = DarkUsers::where('request_id', $request->request_id)->first();
        
        // Check if user is found
        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }
    
        // Get the pending requests, joining with the users table to get friend details
        $pendingRequest = FriendRequests::where('dark_user_id', $user->id)
                                        ->whereNull('is_accepted')
                                        ->with('requestFriend') // Ensure it's eager loading
                                        ->get(['request_friend_id']);
    
        // Check if any pending request is found
        if ($pendingRequest->isEmpty()) {
            return response()->json(['message' => 'No pending requests']);
        }
    
        // Prepare the response data with friend details
        $pendingRequestDetails = $pendingRequest->map(function($request) {
            if ($request->requestFriend) {
                $friend = $request->requestFriend;
                return [
                    'request_friend_id' => $friend->request_id,
                    'friend_id' => $friend->id,
                    'friend_name' => $friend->name,
                ];
            } else {
                // Log the issue where requestFriend is null
                Log::info('No related friend found for request_friend_id: ' . $request->request_friend_id);
                return [
                    'request_friend_id' => $request->request_friend_id,
                    'friend_id' => null,
                    'friend_name' => null,
                ];
            }
        });
    
        return response()->json($pendingRequestDetails);
    }
    
    
}
