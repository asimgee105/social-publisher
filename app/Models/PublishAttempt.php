<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PublishAttempt extends Model
{
    protected $fillable = [
        'post_platform_id',
        'attempt_type',
        'status_code',
        'response_payload',
        'error_message',
        'attempted_at',
    ];

    protected $casts = [
        'response_payload' => 'array',
        'attempted_at' => 'datetime',
        'status_code' => 'integer',
    ];

    public function postPlatform(): BelongsTo
    {
        return $this->belongsTo(PostPlatform::class);
    }
}
