<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('friend-request.{requestId}', function ($user, $requestId) {
    return $user->request_id === $requestId;
});

Broadcast::channel('friend-accept.{requestId}', function ($user, $requestId) {
    return $user->request_id === $requestId;
});

Broadcast::channel('friend-remove.{requestId}', function ($user, $requestId) {
    return $user->request_id === $requestId;
});
Broadcast::channel('chatroom.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id; // Optional check
});
Broadcast::channel('chat.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
Broadcast::channel('chat.{receiverId}', function ($user, $receiverId) {
    return (string) $user->request_id === $receiverId;
});
Broadcast::channel('profile-viewed.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    return true;
});
Broadcast::channel('group.{groupId}', function ($user, $groupId) {
    return true;
});