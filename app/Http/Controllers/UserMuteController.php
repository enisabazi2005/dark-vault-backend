<?php

namespace App\Http\Controllers;

use App\Models\UserMute;
use Illuminate\Http\Request;

class UserMuteController extends Controller
{
    public function muteUnmuteUser(Request $request)
    {
        $validated = $request->validate([
            'dark_users_id' => 'required|exists:dark_users,id',
            'muted_id' => 'required|exists:dark_users,id',
            'muted' => 'required|boolean',
        ]);

        $userMute = UserMute::where('dark_users_id', $validated['dark_users_id'])
                            ->where('muted_id', $validated['muted_id'])
                            ->first();

        if ($userMute) {
            $userMute->muted = $validated['muted'];
            $userMute->save();
        } else {
            UserMute::create($validated);
        }

        return response()->json(['message' => 'User mute status updated successfully']);
    }

    public function getMutedUsers(Request $request)
    {
    $validated = $request->validate([
        'dark_users_id' => 'required|exists:dark_users,id',
    ]);

    $mutedUsers = UserMute::where('dark_users_id', $validated['dark_users_id'])
                          ->where('muted', true)
                          ->pluck('muted_id');

    return response()->json(['muted_users' => $mutedUsers]);
    }

}
