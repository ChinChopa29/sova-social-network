<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected $appends = ['image_url'];

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'gender',
        'birthday',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'bio',
        'website',
        'is_public',
        'is_verified',
        'verified_at',
        'language',
        'timezone',
        'notification_preferences',
        'privacy_settings',
        'last_active_at',
        'avatar',
        'background_image',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'is_verified' => 'boolean',
        'birthday' => 'date',
        'verified_at' => 'datetime',
        'last_active_at' => 'datetime',
        'notification_preferences' => 'array',
        'privacy_settings' => 'array',
    ];

    public function getImageUrlAttribute()
    {
        return $this->avatar ? asset($this->avatar) : null;
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
