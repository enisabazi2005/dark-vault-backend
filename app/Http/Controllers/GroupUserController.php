<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DarkUsers;
use App\Models\GroupUser;
use App\Models\GroupAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupUserController extends Controller
{
    public function createGroup(Request $request)
    {
        // Validate the request data
        $request->validate([
            'title' => 'required|string|max:255',
            'users_invited' => 'required|array',
        ]);

        // Find the user who is creating the group
        $user = DarkUsers::where('request_id', $request->request_id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        // Generate a unique code for the group
        $code = rand(100000, 999999);

        // Ensure the code is unique
        while (GroupUser::where('code', $code)->exists()) {
            $code = rand(100000, 999999);
        }

        // Generate the unique group link
        $groupLink = url("/group/{$code}");

        // Create the group with the admin as the only member, and invited users are not in the group yet
        $group = GroupUser::create([
            'dark_user_id' => $user->id,
            'title' => $request->title,
            'code' => $code,
            'users_in_group' => [$user->id],
            'users_invited' => $request->users_invited ?? [],
            'users_answered' => [],
            'admin_id' => $user->id,
            'semi_admin_id' => null,
            'group_link' => $groupLink,  // Save the group link here
        ]);

        // Now, insert records in the `group_answers` table for each invited user
        foreach ($request->users_invited as $invitedUserId) {
            // Ensure the invited user exists
            $invitedUser = DarkUsers::find($invitedUserId);

            if ($invitedUser) {
                // Insert into `group_answers` table with the accepted status set to false by default
                GroupAnswer::create([
                    'group_id' => $group->id,
                    'user_id' => $invitedUser->id,
                    'accepted' => false,  // False by default (not accepted yet)
                ]);
            }
        }

        // Return a success response with the group and the unique link
        return response()->json([
            'message' => 'Group created successfully',
            'group' => $group,
            'group_link' => $groupLink,  // Include the unique link
        ]);
    }

    public function inviteToGroup(Request $request)
    {
        // Validate input
        $request->validate([
            'group_id' => 'required|exists:group_users,id',
            'user_id' => 'required|exists:dark_users,id',
        ]);

        // Get the requesting user (who is inviting)
        $user = DarkUsers::where('id', $request->user_id)->first();

        $myUser = Auth::user();

        if ($myUser->id === $user->id) {
            return response()->json(['message' => 'You cannot invite yourself'], 400);
        }

        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        // Find the group
        $group = GroupUser::find($request->group_id);



        // Check if the user is already in the group or already invited
        if (in_array($request->user_id, $group->users_in_group) || in_array($request->user_id, $group->users_invited)) {
            return response()->json(['message' => 'User is already in the group or invited'], 400);
        }

        // Add user to `users_invited` list
        $group->users_invited = array_merge($group->users_invited, [$request->user_id]);
        $group->save();

        // Insert into `group_answers` so they can accept/decline
        GroupAnswer::create([
            'group_id' => $group->id,
            'user_id' => $request->user_id,
            'accepted' => false, // Default false
        ]);

        return response()->json([
            'message' => 'User invited successfully',
            'group' => $group,
        ]);
    }

    public function editGroup(Request $request, $groupId)
    {
        $group = GroupUser::find($groupId);

        if (!$group) {
            return response()->json(['message' => 'Group not found'], 400);
        }

        $user = DarkUsers::where('request_id', $request->request_id)->first();

        if (!$user || $group->admin_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($request->has('title')) {
            $group->title = $request->title;
        }

        if ($request->has('users_invited')) {
            $group->users_invited = array_unique(array_merge($group->users_invited ?? [], $request->users_invited));
        }

        if ($request->has('users_in_group')) {
            $group->users_in_group = array_unique(array_merge($group->users_in_group ?? [], $request->users_in_group));
        }

        $group->save();

        return response()->json(['message' => 'Group updated successfully', 'group' => $group]);
    }

    public function getGroups(Request $request)
    {
        $user = DarkUsers::where('request_id', $request->request_id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        // Fetch groups where the user is a member
        $groups = GroupUser::where('dark_user_id', $user->id)->get();

        return response()->json($groups);
    }
    public function getPendingGroups(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        // Find groups where the user is invited
        $groups = GroupUser::whereJsonContains('users_invited', (int) $user->id)->get();

        if ($groups->isEmpty()) {
            return response()->json(['message' => 'No pending group invites found'], 200);
        }

        // Format the response with group_id included
        $formattedGroups = $groups->map(function ($group) use ($user) {
            return [
                'group_id' => $group->id,
                'group_name' => $group->title,
                'invited_by' => $group->dark_user_id, // Who created the group
                'code' => $group->code,
                'users_in_group' => $group->users_in_group,
                'invited' => $user->id, // The authenticated user's ID
                'admin_id' => $group->admin_id,
            ];
        });

        return response()->json($formattedGroups);
    }


    public function removeUser(Request $request, $groupId)
    {
        $user = Auth::user(); // Get authenticated user
        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        // Find the group
        $group = GroupUser::find($groupId);
        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        // Check if the authenticated user is the admin
        if ($group->admin_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate request input
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $userIdToRemove = $request->user_id;

        // Remove user from `users_in_group` if they exist
        if (in_array($userIdToRemove, $group->users_in_group)) {
            $group->users_in_group = array_values(array_diff($group->users_in_group, [$userIdToRemove]));
        }

        // Remove user from `users_invited` if they exist
        if (in_array($userIdToRemove, $group->users_invited)) {
            $group->users_invited = array_values(array_diff($group->users_invited, [$userIdToRemove]));
        }

        // Add user to `users_answered` (they've responded to the invite)
        if (in_array($userIdToRemove, $group->users_answered)) {
            $group->users_answered = array_values(array_diff($group->users_answered, [$userIdToRemove]));
        }

        // Save the updated group information
        $group->save();

        // Remove invitation record from group_answers
        $groupAnswer = GroupAnswer::where('group_id', $groupId)
            ->where('user_id', $userIdToRemove)
            ->first();

        if ($groupAnswer) {
            $groupAnswer->delete();  // Remove the group answer (invitation)
        }

        return response()->json(['message' => 'User removed from group successfully']);
    }
    public function getUserGroups(Request $request)
    {
        $user = DarkUsers::where('request_id', $request->request_id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        // Fetch groups where the user is in `users_in_group` but is NOT the admin
        $groups = GroupUser::whereJsonContains('users_in_group', $user->id)
            ->where('admin_id', '!=', $user->id)
            ->get();

        return response()->json($groups);
    }

    public function promoteToSemiAdmin(Request $request, $groupId)
{
    $user = Auth::user(); // Get authenticated user
    if (!$user) {
        return response()->json(['message' => 'User not found'], 400);
    }

    // Find the group
    $group = GroupUser::find($groupId);
    if (!$group) {
        return response()->json(['message' => 'Group not found'], 404);
    }

    // Check if the authenticated user is the admin
    if ($group->admin_id !== $user->id) {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Validate request input
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
    }

    $userIdToPromote = $request->user_id;

    // Check if the user exists in the group
    if (!in_array($userIdToPromote, $group->users_in_group)) {
        return response()->json(['message' => 'User is not in the group'], 400);
    }

    // Promote the user to semi-admin in group_answers
    $groupAnswer = GroupAnswer::where('group_id', $groupId)
                               ->where('user_id', $userIdToPromote)
                               ->first();

    if ($groupAnswer) {
        $groupAnswer->semi_admin = true; // Update semi_admin status
        $groupAnswer->save();
    }

    // Optionally, update the semi_admin_id in the GroupUser table if needed
    $group->semi_admin_id = $userIdToPromote;
    $group->save();

    return response()->json(['message' => 'User promoted to semi-admin successfully']);
}
}
