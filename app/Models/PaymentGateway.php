<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $fillable = [
        'name', 'code', 'logo', 'credentials', 'fee_flat', 'fee_percent', 
        'is_active', 'is_test_mode'
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'is_active' => 'boolean',
        'is_test_mode' => 'boolean',
    ];
}
