<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiProvider extends Model
{
    protected $fillable = [
        'name', 'code', 'logo', 'description', 'credentials', 'is_active'
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_provider_mappings')
            ->withPivot(['provider_product_code', 'price_capital', 'is_active', 'priority'])
            ->withTimestamps();
    }

    public function productMappings()
    {
        return $this->hasMany(ProductProviderMapping::class);
    }
}
