<?php

namespace App\Http\Controllers;

use App\Events\UnreadMessagesEvent;
use App\Http\Resources\DarkUserResource;
use App\Models\DarkUsers;
use App\Models\FriendRequests;
use App\Models\Notification;
use App\Models\WeeklyOnlineTimes;
use Carbon\Carbon;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

    if($user->has_pro) { 
        if($request->status === 'online') { 
            $user->update(['last_active_at' => Carbon::now()]);
        } else if($request->status === 'offline') { 
            if($user->last_active_at) { 
                $now = Carbon::now();
    
                $lastActive = Carbon::parse($user->last_active_at); 
    
                $minutesOnline = $lastActive->diffInMinutes($now);
    
                $day = $now->format('l');
    
                $record = WeeklyOnlineTimes::create([
                    'dark_users_id' => $user->id,
                    'day' => $day,
                ]);
    
                $record->minutes_online += $minutesOnline;
    
                $record->save();
            }
        }
    };
    
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
        $user = DarkUsers::where('request_id', $request_id)->first();
    
        if (!$user) { 
            return response()->json(['message' => 'User not found'], 400);
        }
    
        $friendRequests = FriendRequests::where('dark_user_id', $user->id)
            ->where('is_accepted', true)
            ->get(['friend']);
    
        if ($friendRequests->isEmpty()) { 
            return response()->json([
                'name' => $user->name,
                'lastname' => $user->lastname,
                'friends_count' => 0,
                'friends' => [],
                'message' => 'No friends found'
            ]);
        }
    
        $friendIds = [];
        foreach ($friendRequests as $request) {
            $friendIds = array_merge($friendIds, json_decode($request->friend, true)); // Decoding the JSON
        }
    
        $friends = DarkUsers::whereIn('id', $friendIds)->get();

        return response()->json([
            'name' => $user->name,
            'lastname' => $user->lastname,
            'friends_count' => $friends->count(),
            'friends' => $friends
        ]);
    }

    public function makeOffline($id)
    {
        $user = DarkUsers::find($id);
    
        if (!$user) {
            return response()->json('No user found!');
        }
    
        $user->update([
            'offline' => true,
            'online' => false,
            'away' => false,
            'do_not_disturb' => false,
        ]);
    
        if($user->has_pro) { 
            if ($user->last_active_at) {
                $now = Carbon::now();
                $lastActive = Carbon::parse($user->last_active_at);
                $minutesOnline = $lastActive->diffInMinutes($now);
                $day = $now->format('l');
    
                $record = WeeklyOnlineTimes::create([
                    'dark_users_id' => $user->id,
                    'day' => $day,
                ]);
        
                $record->minutes_online += $minutesOnline;
                $record->save();
                
                Log::info(['message saved' => $record]);
            }
        }
    
        Log::info('User marked offline', ['user_id' => $user->id]);
    
        return response()->json(['message' => 'User marked as offline'], 200);
    }

    public function updateView()
    {
        $user = Auth::user();

        if($user->view) { 
            return response()->json(['message' => 'Cant view again'], 400);
        }

        $user->update(['view' => true]);

        return response()->json(['message' => 'User updated successfully $user->view']);
    }
    
    public function downloadPng($path)
    {
        $fullPath = storage_path('app/public/' . $path);
        
        if (!file_exists($fullPath)) {
            return response()->json(['error' => 'File not found at: ' . $fullPath], 404);
        }
        
        return response()->download($fullPath, basename($path));
    }
}
