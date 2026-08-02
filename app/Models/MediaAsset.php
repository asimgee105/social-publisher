<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaAsset extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'original_name',
        'mime_type',
        'file_size',
        'duration',
        'width',
        'height',
        'aspect_ratio',
        'codec',
        'frame_rate',
        'path',
        'thumbnail_path',
    ];

    protected $casts = [
        'duration' => 'float',
        'frame_rate' => 'float',
        'width' => 'integer',
        'height' => 'integer',
        'file_size' => 'integer',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class);
    }
}
