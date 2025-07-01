<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'content',
        'user_id',
        'category_id',
        'status',
        'image',
        'is_commentable',
        'view_count',
        'like_count',
        'dislike_count'
    ];

    protected $casts = [
        'is_commentable' => 'boolean',
        'view_count' => 'integer',
        'like_count' => 'integer',
        'dislike_count' => 'integer',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(PostImage::class);
    }

   public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }
}
