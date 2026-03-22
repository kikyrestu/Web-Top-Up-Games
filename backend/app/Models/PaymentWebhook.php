<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PaymentWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'gateway',
        'event_key',
        'signature',
        'is_verified',
        'headers',
        'payload',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'headers' => 'array',
            'payload' => 'array',
            'received_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
