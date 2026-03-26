<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name',
        'description', 'price_capital', 'price_sell', 'is_active', 'image',
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
     * Calculate commission amount for a given sell price.
     */
    public function calculateCommission(float $sellPrice): float
    {
        $commission = $this->getEffectiveCommission();

        if ($commission['type'] === 'flat') {
            return $commission['value'];
        }

        // percentage
        return round(($sellPrice * $commission['value']) / 100, 2);
    }
}
