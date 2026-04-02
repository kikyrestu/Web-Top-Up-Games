<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'username', 'email', 'password', 'api_pin_hash', 'api_pin_set_at', 'phone', 'whatsapp', 'avatar', 'wallet_balance', 'commission_balance', 'is_verified', 'referral_code', 'referred_by'])]
#[Hidden(['password', 'remember_token', 'api_pin_hash'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'api_pin_set_at'     => 'datetime',
            'wallet_balance'     => 'decimal:2',
            'commission_balance' => 'decimal:2',
            'is_verified'        => 'boolean',
        ];
    }
    
    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
    
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
    
    public function favoriteGames()
    {
        return $this->hasMany(FavoriteGame::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function referralsMade()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    /**
     * Get or auto-generate referral code.
     */
    public function getReferralCode(): string
    {
        if (!$this->referral_code) {
            return app(\App\Services\ReferralService::class)->generateReferralCode($this);
        }
        return $this->referral_code;
    }
}
