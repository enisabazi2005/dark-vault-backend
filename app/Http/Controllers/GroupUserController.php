<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DarkUsers;
use App\Models\GroupUser;
use App\Models\GroupAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Events\WebRtcOfferEvent;

class GroupUserController extends Controller
{

    public function sendSignal(Request $request, $to)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:offer,answer,candidate',
            'from' => 'required|integer|in:1,16',
            'to' => 'required|integer|in:1,16',
            'group_id' => 'required|integer|in:8',
            'offer' => 'nullable|array',
            'answer' => 'nullable|array',
            'candidate' => 'nullable|array'
        ]);
    
        // Ensure 'to' matches the route parameter
        if ((string) $request->to !== (string) $to) {
            Log::warning("Mismatched 'to' parameter: request={$request->to}, route={$to}");
            return response()->json(['success' => false, 'error' => 'Mismatched recipient'], 400);
        }
    
        // Prevent self-signaling
        if ((string) $request->from === (string) $request->to) {
            Log::warning("Self-signaling attempt: from={$request->from}, to={$request->to}");
            return response()->json(['success' => false, 'error' => 'Cannot send signal to self'], 400);
        }
    
        $data = [
            'type' => $request->type,
            'from' => $request->from,
            'to' => $request->to,
            'group_id' => $request->group_id,
        ];
    
        switch ($request->type) {
            case 'offer':
                $request->validate([
                    'offer.type' => 'required|string|in:offer',
                    'offer.sdp' => 'required|string'
                ]);
                $data['offer'] = $request->offer;
                break;
    
            case 'answer':
                $request->validate([
                    'answer.type' => 'required|string|in:answer',
                    'answer.sdp' => 'required|string'
                ]);
                $data['answer'] = $request->answer;
                break;
    
            case 'candidate':
                $request->validate([
                    'candidate.candidate' => 'required|string',
                    'candidate.sdpMid' => 'required|string',
                    'candidate.sdpMLineIndex' => 'required|integer'
                ]);
                $data['candidate'] = $request->candidate;
                break;
        }
    
        Log::info("Broadcasting signal to private-user.{$to}: ", $data);
    
        // You can chain ->toOthers() if needed
        broadcast(new WebRtcOfferEvent($data));
    
        return response()->json(['success' => true]);
    }

    public function createGroup(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'users_invited' => 'required|array',
        ]);

        $user = DarkUsers::where('request_id', $request->request_id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        $code = rand(100000, 999999);

        while (GroupUser::where('code', $code)->exists()) {
            $code = rand(100000, 999999);
        }

        $groupLink = $code;

        $group = GroupUser::create([
            'dark_user_id' => $user->id,
            'title' => $request->title,
            'code' => $code,
            'users_in_group' => [$user->id],
            'users_invited' => $request->users_invited ?? [],
            'users_answered' => [],
            'admin_id' => $user->id,
            'semi_admin_id' => null,
            'group_link' => $groupLink,
        ]);

        foreach ($request->users_invited as $invitedUserId) {
            $invitedUser = DarkUsers::find($invitedUserId);

            if ($invitedUser) {
                GroupAnswer::create([
                    'group_id' => $group->id,
                    'user_id' => $invitedUser->id,
                    'accepted' => false,
                ]);
            }
        }

        return response()->json([
            'message' => 'Group created successfully',
            'group' => $group,
            'group_link' => $groupLink,
        ]);
    }

    public function inviteToGroup(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:group_users,id',
            'user_id' => 'required|exists:dark_users,id',
        ]);

        $user = DarkUsers::where('id', $request->user_id)->first();

        $myUser = Auth::user();

        if ($myUser->id === $user->id) {
            return response()->json(['message' => 'You cannot invite yourself'], 400);
        }

        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        $group = GroupUser::find($request->group_id);

        if (in_array($request->user_id, $group->users_in_group) || in_array($request->user_id, $group->users_invited)) {
            return response()->json(['message' => 'User is already in the group or invited'], 400);
        }

        $group->users_invited = array_merge($group->users_invited, [$request->user_id]);
        $group->save();

        GroupAnswer::create([
            'group_id' => $group->id,
            'user_id' => $request->user_id,
            'accepted' => false,
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

        $groups = GroupUser::where('dark_user_id', $user->id)->get();

        return response()->json($groups);
    }
    public function getPendingGroups(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        $groups = GroupUser::whereJsonContains('users_invited', (int) $user->id)->get();

        if ($groups->isEmpty()) {
            return response()->json(['message' => 'No pending group invites found'], 200);
        }

        $formattedGroups = $groups->map(function ($group) use ($user) {
            return [
                'group_id' => $group->id,
                'group_name' => $group->title,
                'invited_by' => $group->dark_user_id,
                'code' => $group->code,
                'users_in_group' => $group->users_in_group,
                'invited' => $user->id,
                'admin_id' => $group->admin_id,
            ];
        });

        return response()->json($formattedGroups);
    }


    public function removeUser(Request $request, $groupId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        $group = GroupUser::find($groupId);
        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        if ($group->admin_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $userIdToRemove = $request->user_id;

        if (in_array($userIdToRemove, $group->users_in_group)) {
            $group->users_in_group = array_values(array_diff($group->users_in_group, [$userIdToRemove]));
        }

        if (in_array($userIdToRemove, $group->users_invited)) {
            $group->users_invited = array_values(array_diff($group->users_invited, [$userIdToRemove]));
        }

        if (in_array($userIdToRemove, $group->users_answered)) {
            $group->users_answered = array_values(array_diff($group->users_answered, [$userIdToRemove]));
        }

        $group->save();

        $groupAnswer = GroupAnswer::where('group_id', $groupId)
            ->where('user_id', $userIdToRemove)
            ->first();

        if ($groupAnswer) {
            $groupAnswer->delete();
        }

        return response()->json(['message' => 'User removed from group successfully']);
    }
    public function getUserGroups(Request $request)
    {
        $user = DarkUsers::where('request_id', $request->request_id)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        $groups = GroupUser::whereJsonContains('users_in_group', $user->id)
            ->where('admin_id', '!=', $user->id)
            ->get();

        return response()->json($groups);
    }

    public function promoteToSemiAdmin(Request $request, $groupId)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 400);
        }

        $group = GroupUser::find($groupId);
        if (!$group) {
            return response()->json(['message' => 'Group not found'], 404);
        }

        if ($group->admin_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid data', 'errors' => $validator->errors()], 400);
        }

        $userIdToPromote = $request->user_id;

        if (!in_array($userIdToPromote, $group->users_in_group)) {
            return response()->json(['message' => 'User is not in the group'], 400);
        }

        $groupAnswer = GroupAnswer::where('group_id', $groupId)
            ->where('user_id', $userIdToPromote)
            ->first();

        if ($groupAnswer) {
            $groupAnswer->semi_admin = true;
            $groupAnswer->save();
        }

        $group->semi_admin_id = $userIdToPromote;
        $group->save();

        return response()->json(['message' => 'User promoted to semi-admin successfully']);
    }

    public function viewGroup($code)
    {

        $group = GroupUser::where('code', $code)->first();

        if (!$group) {
            return abort(404, 'Group not found');
        }

        $usersInGroup = DarkUsers::whereIn('id', $group->users_in_group)->get();

        return view('group.show', [
            'group' => $group,
            'users' => $usersInGroup,
            'authId' => auth()->id()
        ]);
    }
    public function getGroupByCode($code)
{
    $group = GroupUser::where('code', $code)->first();

    if (!$group) {
        return response()->json(['message' => 'Group not found'], 404);
    }

    // Load admin and semi-admin user info
    $admin = DarkUsers::find($group->admin_id);
    $semiAdmin = $group->semi_admin_id ? DarkUsers::find($group->semi_admin_id) : null;

    // Get invited users
    $invitedUsers = DarkUsers::whereIn('id', $group->users_invited ?? [])->get();

    // Get users in group
    $inGroupUsers = DarkUsers::whereIn('id', $group->users_in_group ?? [])->get();

    return response()->json([
        'group' => $group,
        'admin' => $admin,
        'semi_admin' => $semiAdmin,
        'users_invited' => $invitedUsers,
        'users_in_group' => $inGroupUsers,
    ]);
}

}
