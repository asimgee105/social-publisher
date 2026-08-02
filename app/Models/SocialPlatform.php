<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialPlatform extends Model
{
    protected $fillable = [
        'key',
        'name',
        'icon',
        'supports_scheduling',
        'supports_direct_publish',
        'supports_draft_upload',
        'supports_video',
        'supports_image',
        'is_active',
    ];

    protected $casts = [
        'supports_scheduling' => 'boolean',
        'supports_direct_publish' => 'boolean',
        'supports_draft_upload' => 'boolean',
        'supports_video' => 'boolean',
        'supports_image' => 'boolean',
        'is_active' => 'boolean',
    ];
}
