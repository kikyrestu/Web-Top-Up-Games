<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function provider()
    {
        return $this->belongsTo(OtpProvider::class, 'otp_provider_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
