<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user()->load('profile');
});

Route::controller(RegisterController::class)->group(function () {
    Route::post('/register', 'register');
});

Route::controller(LoginController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/logout', 'logout');
});

Route::middleware('auth:sanctum')->controller(ProfileController::class)->group(function () {
    Route::get('/profile/{profile:slug?}', 'show');
    Route::put('/profile/{profile:slug}', 'update');
    Route::delete('/profile/{profile:slug}', 'destroy');
});