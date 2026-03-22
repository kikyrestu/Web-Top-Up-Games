<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProviderProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'product_id',
        'provider_product_code',
        'provider_product_name',
        'is_available',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'is_available' => 'boolean',
            'raw_payload' => 'array',
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
