<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'level',
        'action',
        'message',
        'context',
        'ip_address',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public static function log(string $action, string $message, string $level = 'info', array $context = []): self
    {
        return static::create([
            'level' => $level,
            'action' => $action,
            'message' => $message,
            'context' => $context,
            'ip_address' => request()->ip(),
        ]);
    }
}
