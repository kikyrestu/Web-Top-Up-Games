<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'api_pin_hash', 'api_pin_set_at', 'phone', 'whatsapp', 'avatar', 'wallet_balance', 'is_verified'])]
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'api_pin_set_at' => 'datetime',
            'wallet_balance' => 'decimal:2',
            'is_verified' => 'boolean',
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
}
