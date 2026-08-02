<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEvent extends Model
{
    protected $fillable = [
        'platform',
        'event_type',
        'payload',
        'signature_verified',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'signature_verified' => 'boolean',
        'processed_at' => 'datetime',
    ];
}
