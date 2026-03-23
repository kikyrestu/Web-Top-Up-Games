<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PromoCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'campaign_type',
        'discount_mode',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'quota_total',
        'quota_per_user',
        'scope',
        'category_id',
        'product_id',
        'start_at',
        'end_at',
        'is_active',
        'description',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'max_discount_amount' => 'decimal:2',
            'quota_total' => 'integer',
            'quota_per_user' => 'integer',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_active' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(PromoRedemption::class);
    }
}
