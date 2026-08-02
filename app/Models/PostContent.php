<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostContent extends Model
{
    protected $fillable = [
        'post_platform_id',
        'caption',
        'hook',
        'youtube_title',
        'youtube_description',
        'hashtags',
        'privacy_level',
        'made_for_kids',
        'synthetic_content_disclosure',
        'commercial_content_disclosure',
        'version',
    ];

    protected $casts = [
        'hashtags' => 'array',
        'made_for_kids' => 'boolean',
        'synthetic_content_disclosure' => 'boolean',
        'commercial_content_disclosure' => 'boolean',
        'version' => 'integer',
    ];

    public function postPlatform(): BelongsTo
    {
        return $this->belongsTo(PostPlatform::class);
    }
}
