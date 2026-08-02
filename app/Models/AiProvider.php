<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiProvider extends Model
{
    protected $fillable = [
        'provider_key',
        'name',
        'api_key',
        'model_name',
        'temperature',
        'max_tokens',
        'is_active',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'temperature' => 'float',
        'max_tokens' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'api_key',
    ];
}
