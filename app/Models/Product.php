<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'product_type', 'product_group',
        'description', 'price_capital', 'price_sell', 'is_active', 'status_provider', 'image',
        'commission_type', 'commission_value',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function apiProvider()
    {
        return $this->belongsTo(ApiProvider::class);
    }

    public function providerMappings()
    {
        return $this->hasMany(ProductProviderMapping::class);
    }

    public function apiProviders()
    {
        return $this->belongsToMany(ApiProvider::class, 'product_provider_mappings')
            ->withPivot(['provider_product_code', 'price_capital', 'is_active', 'priority'])
            ->withTimestamps();
    }

    public function resolveCheapestProviderMapping(): ?ProductProviderMapping
    {
        if (! $this->relationLoaded('providerMappings')) {
            $this->load('providerMappings.apiProvider');
        }

        return $this->providerMappings
            ->filter(function (ProductProviderMapping $mapping) {
                return $mapping->is_active && $mapping->apiProvider && $mapping->apiProvider->is_active;
            })
            ->sortBy([
                ['price_capital', 'asc'],
                ['priority', 'asc'],
                ['id', 'asc'],
            ])
            ->first();
    }

    /**
     * Get effective commission config — product override → category → global default.
     * Returns ['type' => flat|percentage, 'value' => float]
     */
    public function getEffectiveCommission(): array
    {
        // Product-level override
        if (!empty($this->commission_type) && $this->commission_value !== null) {
            return [
                'type'  => $this->commission_type,
                'value' => (float) $this->commission_value,
            ];
        }

        // Category-level
        $category = $this->relationLoaded('category') ? $this->category : $this->category()->first();
        if ($category && !empty($category->commission_type) && $category->commission_value !== null) {
            return [
                'type'  => $category->commission_type,
                'value' => (float) $category->commission_value,
            ];
        }

        // Global default
        return [
            'type'  => Setting::get('default_commission_type', 'percentage'),
            'value' => (float) Setting::get('default_commission_value', 0),
        ];
    }

    /**
     * Calculate markup amount for a given base capital price based on effective commission settings.
     * Postpaid/PPOB categories have 0 markup (commission fixed by provider).
     */
    public function calculateMarkup(float $basePrice = 0): float
    {
        $category = $this->relationLoaded('category') ? $this->category : $this->category()->first();
        if ($category && Category::isPostpaidType($category->type)) {
            return 0; // Postpaid has no markup
        }

        // Skip markup for utility/check products (cek status, cek username, etc.)
        $name = strtolower($this->name ?? '');
        if (preg_match('/^cek\s|cek status|cek username|cek hutang|cek saldo|cek kuota/i', $name)) {
            return 0;
        }

        $commission = $this->getEffectiveCommission();
        if ($commission['type'] === 'flat') {
            return $commission['value'];
        }

        // percentage
        return round(($basePrice * $commission['value']) / 100, 2);
    }

    /**
     * Calculate suggested sell price using global defaults for auto-sync.
     */
    public static function calculateSuggestedPrice(float $capitalPrice, ?string $categoryType): float
    {
        if (Category::isPostpaidType($categoryType)) {
            return $capitalPrice; // No markup for postpaid
        }

        $type  = Setting::get('default_commission_type', 'percentage');
        $value = (float) Setting::get('default_commission_value', 0);

        if ($type === 'flat') {
            return $capitalPrice + $value;
        }

        return round($capitalPrice + (($capitalPrice * $value) / 100), 2);
    }
}
