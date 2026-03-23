<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class RateLimitMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile',
        'hour_bucket',
        'hits',
        'blocked',
    ];

    protected function casts(): array
    {
        return [
            'hour_bucket' => 'datetime',
            'hits' => 'integer',
            'blocked' => 'integer',
        ];
    }
}
