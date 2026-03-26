<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrapedProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_provider_id',
        'provider_product_code',
        'product_name',
        'brand',
        'category_name',
        'type',
        'price',
        'price_sell_suggestion',
        'status_provider',
        'is_imported',
        'imported_product_id',
        'synced_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'price_sell_suggestion' => 'decimal:2',
        'is_imported' => 'boolean',
        'synced_at' => 'datetime',
    ];

    public function apiProvider()
    {
        return $this->belongsTo(ApiProvider::class);
    }

    public function importedProduct()
    {
        return $this->belongsTo(Product::class, 'imported_product_id');
    }

    /**
     * Scope: belum di-import
     */
    public function scopeNotImported($query)
    {
        return $query->where('is_imported', false);
    }

    /**
     * Scope: filter by provider
     */
    public function scopeForProvider($query, int $providerId)
    {
        return $query->where('api_provider_id', $providerId);
    }
}
