<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PostPlatform extends Model
{
    protected $fillable = [
        'post_id',
        'social_account_id',
        'platform_key',
        'status',
        'platform_post_id',
        'platform_post_url',
        'error_code',
        'error_message',
        'retries_count',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'retries_count' => 'integer',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function socialAccount(): BelongsTo
    {
        return $this->belongsTo(SocialAccount::class);
    }

    public function postContent(): HasOne
    {
        return $this->hasOne(PostContent::class);
    }

    public function publishAttempts(): HasMany
    {
        return $this->hasMany(PublishAttempt::class)->orderBy('attempted_at', 'desc');
    }
}
