<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledPost extends Model
{
    protected $fillable = [
        'post_id',
        'post_platform_id',
        'scheduled_time_utc',
        'timezone',
        'status',
    ];

    protected $casts = [
        'scheduled_time_utc' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function postPlatform(): BelongsTo
    {
        return $this->belongsTo(PostPlatform::class);
    }
}
