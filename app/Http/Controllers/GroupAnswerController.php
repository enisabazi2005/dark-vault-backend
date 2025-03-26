<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\GroupAnswer;
use App\Models\GroupUser;
use App\Models\DarkUsers;
use Illuminate\Support\Facades\Auth;

class GroupAnswerController extends Controller
{
    public function respondToInvite(Request $request, $groupId)
    {
        // Validate incoming request data
        $validator = Validator::make($request->all(), [
            'accepted' => 'required|boolean',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }
    
        $user = Auth::user(); // Get the authenticated user
        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }
    
        $accepted = $request->accepted;
    
        // Find the group
        $groupUser = GroupUser::find($groupId);
        if (!$groupUser) {
            return response()->json(['message' => 'Group not found'], 404);
        }
    
        // Check if the user is actually invited
        if (!in_array($user->id, $groupUser->users_invited)) {
            return response()->json(['message' => 'Invite not found'], 404);
        }
    
        // Find the GroupAnswer entry
        $groupAnswer = GroupAnswer::where('group_id', $groupId)
            ->where('user_id', $user->id)
            ->first();
    
        if (!$groupAnswer) {
            return response()->json(['message' => 'Group answer not found'], 404);
        }
    
        // Update the accepted field in GroupAnswer
        $groupAnswer->accepted = $accepted;
        $groupAnswer->save();
    
        // If accepted, move the user to `users_in_group` and update the invite status
        if ($accepted) {
            // Remove user from `users_invited`
            $groupUser->users_invited = array_values(array_diff($groupUser->users_invited, [$user->id]));
            // Add user to `users_in_group`
            $groupUser->users_in_group = array_values(array_merge($groupUser->users_in_group, [$user->id]));
        } else {
            // If declined, remove the user from `users_invited` only
            $groupUser->users_invited = array_values(array_diff($groupUser->users_invited, [$user->id]));
        }
    
        // Add the user to `users_answered` (they've responded to the invite)
        $groupUser->users_answered = array_values(array_merge($groupUser->users_answered, [$user->id]));
    
        // Save the updated group information
        $groupUser->save();
    
        return response()->json(['message' => 'Response recorded successfully']);
    }
    
    public function getPendingGroups(Request $request)
{
    $user = DarkUsers::where('request_id', $request->request_id)->first();

    if (!$user) {
        return response()->json(['message' => 'User not found'], 400);
    }

    // Find groups where the user is still in the `users_invited` array
    $pendingGroups = GroupUser::whereJsonContains('users_invited', $user->id)->get();

    return response()->json($pendingGroups);
}

}