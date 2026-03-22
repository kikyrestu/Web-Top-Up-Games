<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_code',
        'user_id',
        'guest_session_id',
        'product_id',
        'product_type',
        'customer_target',
        'base_price',
        'admin_fee',
        'margin',
        'final_amount',
        'status',
        'paid_at',
        'processed_at',
        'completed_at',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'admin_fee' => 'decimal:2',
            'margin' => 'decimal:2',
            'final_amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'processed_at' => 'datetime',
            'completed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }
}
