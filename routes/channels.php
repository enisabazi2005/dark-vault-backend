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
