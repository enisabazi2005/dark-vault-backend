<?php

namespace App\Http\Controllers;

use App\Events\UnreadMessagesEvent;
use App\Http\Resources\DarkUserResource;
use App\Models\DarkUsers;
use App\Models\FriendRequests;
use App\Models\Notification;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DarkUserController extends Controller
{
    public function index()
    {
        $darkUsers = DarkUsers::all();

        return DarkUserResource::collection($darkUsers);
    }

    public function getMyUser() { 
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json('Success');
    }

    public function show($id)
    {
        $user = DarkUsers::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    public function updateStatus(Request $request)
{
    $request->validate([
        'status' => 'required|in:online,offline,away,do_not_disturb',
    ]);

    $user = Auth::user();

    if (!$user) {
        return response()->json(['error' => 'No User found'], 404);
    }
    if (in_array($request->status, ['online', 'offline'])) {
        $unreadMessages = Notification::where('dark_user_id', $user->id)
            ->where('is_read', false)
            ->get();
    
        Log::info('Fetched unread messages for user: ' . $user->id, ['unreadMessagesCount' => $unreadMessages->count()]);
    
        if ($unreadMessages->count() > 0) {
            broadcast(new UnreadMessagesEvent($unreadMessages, $user->id));
        }
    }

    $statuses = ['online' => false, 'offline' => false, 'away' => false, 'do_not_disturb' => false];
    $statuses[$request->status] = true;
    
    $user->update($statuses);

    return response()->json(['message' => 'Status updated successfully']);
}

    
    public function getStatus()
    {
        $user = Auth::user();

        return response()->json([
            'online' => $user->online,
            'offline' => $user->offline,
            'away' => $user->away,
            'do_not_disturb' => $user->do_not_disturb,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validatedData = $request->validate([
            'name' => 'string|max:255|nullable',
            'lastname' => 'string|max:255|nullable',
            'email' => 'email|max:255|unique:dark_users,email,' . $user->id . '|nullable',
            'current_password' => 'required_with:password|string|min:6',
            'password' => 'string|min:6|confirmed|nullable',
            'gender' => 'in:male,female,other|nullable',
            'birthdate' => 'date|nullable',
            'picture' => 'image|mimes:jpeg,png,jpg,gif|max:2048|nullable',
        ]);

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['error' => 'Current password is incorrect'], 400);
            }
            $validatedData['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('profile_pictures', $fileName, 'public');
            $validatedData['picture'] = $filePath;
        }

        $filteredData = array_filter($validatedData, function ($value) {
            return !is_null($value);
        });

        $user->update($filteredData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ]);
    }
    public function getUserWithFriends($request_id) { 
        // Retrieve the user by their request_id
        $user = DarkUsers::where('request_id', $request_id)->first();
    
        if (!$user) { 
            return response()->json(['message' => 'User not found'], 400);
        }
    
        // Retrieve accepted friends for the user
        $friendRequests = FriendRequests::where('dark_user_id', $user->id)
            ->where('is_accepted', true)
            ->get(['friend']);
    
        // Check if the user has any friends
        if ($friendRequests->isEmpty()) { 
            return response()->json([
                'name' => $user->name,
                'lastname' => $user->lastname,
                'friends_count' => 0,
                'friends' => [],
                'message' => 'No friends found'
            ]);
        }
    
        // Decode the 'friend' field to get an array of friend IDs
        $friendIds = [];
        foreach ($friendRequests as $request) {
            $friendIds = array_merge($friendIds, json_decode($request->friend, true)); // Decoding the JSON
        }
    
        // Retrieve friends' details by their IDs
        $friends = DarkUsers::whereIn('id', $friendIds)
            ->get(['id', 'name', 'lastname', 'request_id', 'gender', 'birthdate', 'age', 'picture', 'online' , 'offline' , 'away' , 'do_not_disturb']); 
    
        // Return the user details along with their friends
        return response()->json([
            'name' => $user->name,
            'lastname' => $user->lastname,
            'friends_count' => $friends->count(),
            'friends' => $friends
        ]);
    }

    public function makeOffline(Request $request, $id) { 
    //    $request->validate([
    //     'user_id' => 'required|numeric|exists:dark_users,id', 
    //    ]);
       
       $user = DarkUsers::find($id);
        // $user = DarkUsers::where('id', $id)->get();
       if(!$user) { 
        return response()->json('No user found!');
       }

       $user->update([
        'offline' => true,
        'online' => false,
        'away' => false,
        'do_not_disturb' => false,
       ]);

       Log::info('success', ['user_id' => $user->id]);

       return response()->json(['message' => 'User marked as offline'], 200);
    }
    
}
