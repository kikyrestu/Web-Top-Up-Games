<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PromoRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'promo_campaign_id',
        'order_id',
        'user_id',
        'campaign_code',
        'campaign_type',
        'discount_amount',
        'cashback_amount',
        'redeemed_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'cashback_amount' => 'decimal:2',
            'redeemed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PromoCampaign::class, 'promo_campaign_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
