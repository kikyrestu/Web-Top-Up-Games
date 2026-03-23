<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class OtpRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'destination',
        'code_hash',
        'attempt_count',
        'expires_at',
        'verified_at',
        'request_ip',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }
}
