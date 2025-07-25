<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostUserReaction extends Model
{
    protected $table = 'post_user_reaction';
    
    protected $fillable = [
        'post_id',
        'user_id',
        'type',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
