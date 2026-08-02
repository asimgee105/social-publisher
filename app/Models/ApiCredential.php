<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiCredential extends Model
{
    protected $fillable = [
        'platform',
        'client_id',
        'client_secret',
        'redirect_uri',
        'scopes',
        'extra_config',
        'is_active',
    ];

    protected $casts = [
        'client_secret' => 'encrypted',
        'scopes' => 'array',
        'extra_config' => 'array',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'client_secret',
    ];
}
