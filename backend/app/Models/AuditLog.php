<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'actor_type',
        'actor_id',
        'entity_type',
        'entity_id',
        'request_id',
        'ip_address',
        'user_agent',
        'payload',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
        ];
    }
}
