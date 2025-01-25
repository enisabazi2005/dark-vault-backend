<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DarkUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/test', function () {
    return response()->json(['message' => 'API is working']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users', [DarkUserController::class, 'index']);
});
