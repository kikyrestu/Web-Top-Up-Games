<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'value'           => 'decimal:2',
        'max_discount'    => 'decimal:2',
        'min_purchase'    => 'decimal:2',
        'is_active'       => 'boolean',
        'starts_at'       => 'datetime',
        'expires_at'      => 'datetime',
    ];

    public function usages()
    {
        return $this->hasMany(VoucherUsage::class);
    }

    public function isValid(?float $purchaseAmount = null): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && $this->starts_at->isFuture()) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) return false;
        if ($purchaseAmount !== null && $purchaseAmount < (float) $this->min_purchase) return false;
        return true;
    }

    /**
     * Calculate discount for a given subtotal.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'flat') {
            return min((float) $this->value, $subtotal);
        }

        // percentage
        $discount = $subtotal * ($this->value / 100);
        if ($this->max_discount !== null) {
            $discount = min($discount, (float) $this->max_discount);
        }
        return round($discount, 2);
    }
}
