<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount'    => 'decimal:2',
        'paid_at'   => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    // Scopes
    public function scopePending($q)    { return $q->where('status', 'pending'); }
    public function scopeApproved($q)   { return $q->where('status', 'approved'); }
    public function scopePaid($q)       { return $q->where('status', 'paid'); }
}
