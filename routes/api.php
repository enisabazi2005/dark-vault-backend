<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlockedUsersController;
use App\Http\Controllers\DarkUserController;
use App\Http\Controllers\ForgotPasswordControler;
use App\Http\Controllers\FriendReqestsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PusherController;
use App\Http\Controllers\StoreEmailController;
use App\Http\Controllers\StoreNotesController;
use App\Http\Controllers\StorePasswordController;
use App\Http\Controllers\StorePrivateInfoController;
use App\Http\Controllers\UserMuteController;
use App\Http\Middleware\Authenticate;
use App\Mail\PasswordResetMail;
use App\Models\FriendRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\GroupUserController;
use App\Http\Controllers\GroupAnswerController; 
use App\Http\Controllers\BackgroundColorController;
use App\Http\Controllers\ChatBotController;
use App\Models\MessageReactions;

Route::post('/register', [AuthController::class, 'register']); // done in native
Route::post('/login', [AuthController::class, 'login']); // done in native
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']); // done in native

Route::apiResource('/friend-requests', FriendRequests::class); // done in native
Route::get('/users', [DarkUserController::class, 'index']); // done in native
Route::post('/send-message', [PusherController::class, 'store']); // done in native
Route::get('/messages/{senderRequestId}/{receiverRequestId}', [PusherController::class , 'getMessages']); // done in native

Route::get('/message-status', [PusherController::class , 'getMessageStatus']); // done in native
Route::post('/message/{id}/react', [PusherController::class, 'react']); // done in native
Route::get('/reactions/grouped', function () {
    return MessageReactions::all()->groupBy('message_id');
});
Route::get('/password-reset/track/{code}/{email}', [ForgotPasswordControler::class, 'track']); // done in native
Route::get('/password-reset/verify/{code}/{email}', [ForgotPasswordControler::class, 'verify']); // done in native
Route::get('/check-verification-status', [ForgotPasswordControler::class, 'checkVerificationStatus']); // done in native

Route::post('/chatbot', [ChatBotController::class , 'respond']);
Route::post('/typing', function (Request $request) {
    event(new \App\Events\TypingIndicator(
        $request->sender_id,
        $request->receiver_id,
        $request->is_typing
    ));
    return response()->json(['status' => 'Typing status sent']);
}); // done in native

Route::post('/setOffline/{id}', [DarkUserController::class , 'makeOffline']);


Route::post('/send-signal/{to}', [GroupUserController::class, 'sendSignal']);
Route::middleware(['auth:sanctum', Authenticate::class])->group(function () {
    Route::delete('/message/{messageId}/react', [PusherController::class, 'deleteReaction']); // done in native

    Route::post('/ping', [DarkUserController::class , 'ping']);
    // Route::post('/setOffline', [DarkUserController::class , 'makeOffline']);
    // Route::post('/setOffline', [DarkUserController::class , 'makeOffline']);

    Route::get('/background', [BackgroundColorController::class, 'getBackground']); // done in native
    Route::post('/background', [BackgroundColorController::class, 'updateBackground']); // done in native

    Route::patch('/groups/{groupId}/remove-user', [GroupUserController::class, 'removeUser']); // done in native
    Route::get('/groups/pending', [GroupUserController::class, 'getPendingGroups']); // done in native
    Route::patch('/groups/{groupId}/respond', [GroupAnswerController::class, 'respondToInvite']); // done in native
    Route::get('/groups/member', [GroupUserController::class, 'getUserGroups']); // done in native


    Route::post('/profile-viewed', [PusherController::class , 'profileViewed']);

    Route::post('/groups/create', [GroupUserController::class, 'createGroup']); // done in native
    Route::post('groups/invite', [GroupUserController::class, 'inviteToGroup']); // done in native
    Route::post('group/{groupId}/promote', [GroupUserController::class, 'promoteToSemiAdmin']); // done in native
    Route::patch('/groups/edit/{groupId}', [GroupUserController::class, 'editGroup']); // done in native
    Route::get('/groups', [GroupUserController::class, 'getGroups']); // done in native

    Route::post('/mark-as-seen', [PusherController::class, 'markAsSeen']);  // done in native

    Route::post('/remove-friend/{request_id}', action: [FriendReqestsController::class, 'unfriend']); // done in native
    Route::get('/unfriended-users', [FriendReqestsController::class , 'getUnfriendedUsers']); // done in native
    Route::post('/block-user/{request_id}', [BlockedUsersController::class, 'blockUsers']); // done in native
    Route::get('/blocked-users', [BlockedUsersController::class, 'getBlockedUsers']); // done in native
    Route::get('/blocked-by', [BlockedUsersController::class, 'getUsersWhoBlockedMe']); // done in native
    Route::post('/unblock-user/{request_id}', [BlockedUsersController::class, 'unblockUsers']); // done in native


    Route::get('/dark-user/{request_id}/get-friends', [DarkUserController::class, 'getUserWithFriends']); // done in native

    Route::post('/mute-unmute', [UserMuteController::class, 'muteUnmuteUser']); // done in native
    Route::get('/muted-users', [UserMuteController::class , 'getMutedUsers']);

    Route::get('/get-unread-messages', [NotificationController::class , 'getUnreadMessages']); // done in native
    Route::post('/mark-notifications-read', [NotificationController::class, 'markNotificationRead']); // done in native

    Route::post('/update-settings', [DarkUserController::class, 'updateSettings']);// done in native

    Route::post('/update-status', [DarkUserController::class, 'updateStatus']);// done in native
    Route::get('/get-status', [DarkUserController::class, 'getStatus']);// done in native

    Route::post('/friend-request/{request_id}/send', [FriendReqestsController::class , 'sendRequest']); // done in native
    Route::post('/friend-request/{senderRequestId}/respond', [FriendReqestsController::class, 'respondRequest']); // done in native
    Route::get('/friend-request/{request_id}/friends', [FriendReqestsController::class, 'getFriends']); // done in native
    Route::get('/friend-request/{request_id}/pending', [FriendReqestsController::class, 'getPendingRequest']); // done in native

    Route::get('users/{id}', [DarkUserController::class, 'show']); // done in native
    Route::get('/get-my-user' , [DarkUserController::class, 'getMyUser']);
    
    Route::post('/store-password', [StorePasswordController::class, 'store']); // done in native
    Route::get('/store-passwords', [StorePasswordController::class, 'index']); // done in native
    Route::put('/store-password/{id}', [StorePasswordController::class, 'update']); // done in native
    Route::delete('/store-password/{id}', [StorePasswordController::class, 'destroy']); // done in native

    Route::post('/store-email', [StoreEmailController::class, 'store']); // done in native
    Route::get('/store-emails', [StoreEmailController::class, 'index']); // done in native
    Route::put('/store-email/{id}', [StoreEmailController::class, 'update']); // done in native
    Route::delete('/store-email/{id}', [StoreEmailController::class, 'destroy']); // done in native

    Route::post('/store-private-info', [StorePrivateInfoController::class, 'store']); // done in native
    Route::get('/store-private-infos', [StorePrivateInfoController::class, 'index']); // done in native
    Route::put('/store-private-info/{id}', [StorePrivateInfoController::class, 'update']); // done in native
    Route::delete('/store-private-info/{id}', [StorePrivateInfoController::class, 'destroy']); // done in native

    Route::post('/store-note', [StoreNotesController::class, 'store']); // done in native
    Route::get('/store-notes', [StoreNotesController::class, 'index']); // done in native
    Route::put('/store-note/{id}', [StoreNotesController::class, 'update']); // done in native
    Route::delete('/store-note/{id}', [StoreNotesController::class, 'destroy']); // done in native
});
Route::post('/forgot-password', [ForgotPasswordControler::class, 'sendResetCode']); // done in native
Route::post('/verify-reset-code', action: [ForgotPasswordControler::class, 'verifyResetCode']); // done in native
Route::post('/reset-password', [ForgotPasswordControler::class, 'resetPassword']); // done in native
