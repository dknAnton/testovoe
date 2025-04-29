<?php

use App\Http\Controllers\GroupUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/groups/users-count', [GroupUserController::class, 'index']);
