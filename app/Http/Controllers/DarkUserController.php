<?php

namespace App\Http\Controllers;

use App\Http\Resources\DarkUserResource;
use App\Models\DarkUsers;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DarkUserController extends Controller
{
    public function index()
    {
        $darkUsers = DarkUsers::all();

        return DarkUserResource::collection($darkUsers);
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
            return response()->json(['No User has been found', 404]);
        }
        $user->update([
            'online' => false,
            'offline' => false,
            'away' => false,
            'do_not_disturb' => false,
            $request->status => true,
        ]);

        return response()->json(['Status updated successfully', 200]);
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
}
