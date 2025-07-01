<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Post;
use App\Models\User;

Broadcast::channel('post.{postId}', function (User $user, $postId) {
    return ['id' => $user->id, 'name' => $user->name];
});