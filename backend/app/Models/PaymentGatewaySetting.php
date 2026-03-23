<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class PaymentGatewaySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'display_name',
        'is_active',
        'priority',
        'fee_flat',
        'fee_percent',
        'supported_methods',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'priority' => 'integer',
            'fee_flat' => 'decimal:2',
            'fee_percent' => 'decimal:2',
            'supported_methods' => 'array',
        ];
    }
}
