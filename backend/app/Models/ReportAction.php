<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReportAction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'moderator_id',
        'report_id',
        'target_type',
        'target_id',
        'target_user_id',
        'action_type',
        'comment',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    public function targetUser()
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
