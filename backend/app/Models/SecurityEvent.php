<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class SecurityEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_code',
        'severity',
        'user_id',
        'device_id',
        'ip_address',
        'user_agent',
        'risk_score',
        'context',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}
