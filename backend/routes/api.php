<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostUserReactionController;
use App\Http\Controllers\Api\UserPostController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/{user}/posts', [UserPostController::class, 'index']);

    Route::get('/categories/search', [CategoryController::class, 'search']);
    Route::resource('categories', CategoryController::class);
    
    Route::get('/tags/search', [TagController::class, 'search']);
    Route::resource('tags', TagController::class);

    Route::resource('posts', PostController::class);
    Route::get('/posts/{post}/comments', [CommentController::class, 'postComments']);

    Route::apiResource('comments', CommentController::class);

    Route::apiResource('reactions', PostUserReactionController::class);
    Route::get('/reactions/{id}/my', [PostUserReactionController::class, 'myReaction']);
});