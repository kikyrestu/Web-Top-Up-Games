<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VoucherUsage extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'discount_amount' => 'decimal:2',
    ];

    public function voucher()     { return $this->belongsTo(Voucher::class); }
    public function user()        { return $this->belongsTo(User::class); }
    public function transaction() { return $this->belongsTo(Transaction::class); }
}
