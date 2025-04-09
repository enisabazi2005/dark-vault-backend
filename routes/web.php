<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GroupUserController;

Route::get('/', function () {
    return view('welcome');
});

    Route::get('/group/{code}', [GroupUserController::class, 'viewGroup'])->name('group.show');