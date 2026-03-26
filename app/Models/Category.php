<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public const GAME_TYPES = ['game', 'seluler', 'pc', 'voucher'];

    protected $fillable = [
        "name",
        "description",
        "type",
        "icon",
        "thumbnail",
        "publisher",
        "slug",
        "is_active",
        "is_popular",
        "is_new",
        "sort_order",
        "commission_type",
        "commission_value",
        "input_fields",
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_popular'   => 'boolean',
        'is_new'       => 'boolean',
        'input_fields' => 'array',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeGameTypes(Builder $query): Builder
    {
        return $query->whereIn('type', self::GAME_TYPES);
    }

    public function scopeNonGameTypes(Builder $query): Builder
    {
        return $query->whereNotIn('type', self::GAME_TYPES);
    }

    public static function isGameType(?string $type): bool
    {
        return in_array(strtolower((string) $type), self::GAME_TYPES, true);
    }

    /**
     * Get input fields with defaults if none configured.
     */
    public function getFormFields(): array
    {
        if (!empty($this->input_fields)) {
            return $this->input_fields;
        }

        // Default fields based on category type
        if (self::isGameType($this->type)) {
            return [
                ['name' => 'target', 'label' => 'User ID', 'placeholder' => 'Masukkan User ID', 'required' => true],
                ['name' => 'target_zone', 'label' => 'Server', 'placeholder' => 'Masukkan Server (jika ada)', 'required' => false],
            ];
        }

        // PPOB default
        return [
            ['name' => 'target', 'label' => 'Nomor Pelanggan', 'placeholder' => 'Masukkan Nomor', 'required' => true],
        ];
    }
}
