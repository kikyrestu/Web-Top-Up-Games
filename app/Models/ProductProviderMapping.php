<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductProviderMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'api_provider_id',
        'provider_product_code',
        'price_capital',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'price_capital' => 'decimal:2',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function apiProvider()
    {
        return $this->belongsTo(ApiProvider::class);
    }
}
