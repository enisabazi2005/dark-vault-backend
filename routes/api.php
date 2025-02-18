<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DarkUserController;
use App\Http\Controllers\FriendReqestsController;
use App\Http\Controllers\PusherController;
use App\Http\Controllers\StoreEmailController;
use App\Http\Controllers\StoreNotesController;
use App\Http\Controllers\StorePasswordController;
use App\Http\Controllers\StorePrivateInfoController;
use App\Models\FriendRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::apiResource('/friend-requests', FriendRequests::class);
// Route::apiResource('/store-passwords', StorePasswordController::class);
Route::get('/users', [DarkUserController::class, 'index']);
Route::post('/send-message', [PusherController::class, 'store']);
Route::get('/messages/{senderRequestId}/{receiverRequestId}', [PusherController::class , 'getMessages']);
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/friend-request/{request_id}/send', [FriendReqestsController::class , 'sendRequest']);
    Route::post('/friend-request/{senderRequestId}/respond', [FriendReqestsController::class, 'respondRequest']);
    Route::get('/friend-request/{request_id}/friends', [FriendReqestsController::class, 'getFriends']);
    Route::get('/friend-request/{request_id}/pending', [FriendReqestsController::class, 'getPendingRequest']);

    Route::get('users/{id}', [DarkUserController::class, 'show']);
    
    Route::post('/store-password', [StorePasswordController::class, 'store']);
    Route::get('/store-passwords', [StorePasswordController::class, 'index']);
    Route::put('/store-password/{id}', [StorePasswordController::class, 'update']);
    Route::delete('/store-password/{id}', [StorePasswordController::class, 'destroy']);

    Route::post('/store-email', [StoreEmailController::class, 'store']);
    Route::get('/store-emails', [StoreEmailController::class, 'index']);
    Route::put('/store-email/{id}', [StoreEmailController::class, 'update']);
    Route::delete('/store-email/{id}', [StoreEmailController::class, 'destroy']);

    Route::post('/store-private-info', [StorePrivateInfoController::class, 'store']);
    Route::get('/store-private-infos', [StorePrivateInfoController::class, 'index']);
    Route::put('/store-private-info/{id}', [StorePrivateInfoController::class, 'update']);
    Route::delete('/store-private-info/{id}', [StorePrivateInfoController::class, 'destroy']);

    Route::post('/store-note', [StoreNotesController::class, 'store']);
    Route::get('/store-notes', [StoreNotesController::class, 'index']);
    Route::put('/store-note/{id}', [StoreNotesController::class, 'update']);
    Route::delete('/store-note/{id}', [StoreNotesController::class, 'destroy']);
});
