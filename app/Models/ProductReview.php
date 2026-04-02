<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'transaction_id',
        'rating',
        'comment',
        'is_approved',
    ];

    protected $casts = [
        'rating'      => 'integer',
        'is_approved' => 'boolean',
    ];

    public function user()     { return $this->belongsTo(User::class); }
    public function product()  { return $this->belongsTo(Product::class); }
    public function transaction() { return $this->belongsTo(Transaction::class); }

    public function scopeApproved($q) { return $q->where('is_approved', true); }
}
