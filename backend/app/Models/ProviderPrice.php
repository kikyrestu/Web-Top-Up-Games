<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProviderPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'product_id',
        'base_price',
        'admin_fee',
        'commission',
        'is_active',
        'provider_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'commission' => 'decimal:2',
            'is_active' => 'boolean',
            'provider_updated_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
