<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DarkUserController;
use App\Http\Controllers\StorePasswordController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// Route::post('/register', [AuthController::class, 'register']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});
// Route::apiResource('/store-passwords', StorePasswordController::class);
Route::get('/users', [DarkUserController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::get('users/{id}', [DarkUserController::class, 'show']);
    Route::post('/store-password', [StorePasswordController::class, 'store']);
    Route::get('/store-passwords', [StorePasswordController::class, 'index']);
    Route::put('/store-password/{id}', [StorePasswordController::class, 'update']);
    Route::delete('/store-password/{id}', [StorePasswordController::class, 'destroy']);
});
