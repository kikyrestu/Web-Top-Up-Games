<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProviderHealthCheck extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'status',
        'response_time_ms',
        'error_rate',
        'checked_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
