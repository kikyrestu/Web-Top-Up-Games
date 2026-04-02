<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public const GAME_TYPES = ['game', 'pc', 'voucher'];

    /**
     * Input field presets per category type.
     * Dipakai untuk auto-detect form customer berdasarkan tipe kategori.
     */
    public const INPUT_PRESETS = [
        'pulsa' => [
            ['name' => 'target', 'label' => 'Nomor HP', 'type' => 'tel', 'placeholder' => 'Contoh: 08123456789', 'required' => true],
        ],
        'paket_data' => [
            ['name' => 'target', 'label' => 'Nomor HP', 'type' => 'tel', 'placeholder' => 'Contoh: 08123456789', 'required' => true],
        ],
        'seluler' => [
            ['name' => 'target', 'label' => 'Nomor HP', 'type' => 'tel', 'placeholder' => 'Contoh: 08123456789', 'required' => true],
        ],
        'pln' => [
            ['name' => 'target', 'label' => 'No. Meter / ID Pelanggan', 'type' => 'text', 'placeholder' => 'Masukkan 11-12 digit nomor meter', 'required' => true],
        ],
        'pdam' => [
            ['name' => 'target', 'label' => 'Nomor Pelanggan PDAM', 'type' => 'text', 'placeholder' => 'Masukkan nomor pelanggan', 'required' => true],
        ],
        'bpjs' => [
            ['name' => 'target', 'label' => 'Nomor Peserta BPJS', 'type' => 'text', 'placeholder' => 'Masukkan nomor peserta', 'required' => true],
        ],
        'internet' => [
            ['name' => 'target', 'label' => 'ID Pelanggan', 'type' => 'text', 'placeholder' => 'Masukkan ID pelanggan', 'required' => true],
        ],
        'emoney' => [
            ['name' => 'target', 'label' => 'Nomor E-Money / HP', 'type' => 'tel', 'placeholder' => 'Contoh: 08123456789', 'required' => true],
        ],
        'ppob' => [
            ['name' => 'target', 'label' => 'Nomor Pelanggan', 'type' => 'text', 'placeholder' => 'Masukkan nomor pelanggan', 'required' => true],
        ],
        'tagihan' => [
            ['name' => 'target', 'label' => 'ID Pelanggan / No. Tagihan', 'type' => 'text', 'placeholder' => 'Masukkan nomor pelanggan', 'required' => true],
        ],
        'voucher' => [],
        'game' => [
            ['name' => 'target', 'label' => 'User ID', 'type' => 'text', 'placeholder' => 'Masukkan User ID', 'required' => true],
            ['name' => 'target_zone', 'label' => 'Server / Zone ID', 'type' => 'text', 'placeholder' => 'Masukkan Zone ID (jika ada)', 'required' => false],
        ],
        'pc' => [
            ['name' => 'target', 'label' => 'User ID / Account', 'type' => 'text', 'placeholder' => 'Masukkan User ID atau Email', 'required' => true],
        ],
    ];

    /**
     * Game brands yang butuh User ID + Server/Zone ID.
     */
    public const GAMES_WITH_SERVER = [
        'mobile legends', 'genshin impact', 'honkai star rail', 'honkai impact',
        'arena of valor', 'ragnarok', 'dragon nest', 'laplace',
        'light of thel', 'perfect world', 'sausage man', 'lords mobile',
        'aether gazer', 'saint seiya', 'mole\'s world', 'slam dunk',
        'ace racer', 'love and deepspace', 'identity v',
    ];

    /**
     * Game brands yang cuma butuh Player ID (tanpa server).
     */
    public const GAMES_ID_ONLY = [
        'free fire', 'pubg', 'call of duty', 'stumble guys',
        'point blank', 'higgs domino', 'super sus', 'blood strike',
        'tower of fantasy', 'clash of clans', 'clash royale',
        'brawl stars', 'undawn', 'eggy party', 'south park',
        'zenless zone zero', 'wuthering waves', 'metal slug',
        'one punch man', 'street fighter', 'the king of fighters',
        'never after', 'night crows', 'goddess of victory',
    ];

    /**
     * Game brands yang butuh Username (bukan ID angka).
     */
    public const GAMES_USERNAME = [
        'roblox', 'minecraft', 'fortnite', 'valorant', 'league of legends',
    ];

    /**
     * Game/brand yang merupakan voucher (tanpa input customer).
     */
    public const GAMES_VOUCHER = [
        'steam', 'psn', 'xbox', 'nintendo', 'garena shell',
        'google play', 'itunes', 'spotify', 'netflix', 'vidio',
        'apple', 'razer gold', 'unipin', 'cherry credits',
    ];

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

    public static function isPostpaidType(?string $type): bool
    {
        return in_array(strtolower((string) $type), ['tagihan', 'postpaid', 'pasca'], true);
    }

    /**
     * Auto-detect input fields berdasarkan type + nama kategori.
     *
     * Logic:
     * 1. Untuk game → cek nama brand: ML butuh server, FF cuma ID, Steam = voucher (tanpa input)
     * 2. Untuk non-game → mapping langsung dari type (pulsa → Nomor HP, PLN → No. Meter, dll)
     * 3. Fallback ke preset generic
     */
    public static function detectInputFields(string $type, ?string $name = null): array
    {
        $type = strtolower($type);
        $nameLower = strtolower($name ?? '');

        // Game types: cek spesifik per brand
        if (in_array($type, self::GAME_TYPES)) {
            // Voucher games (no input needed)
            foreach (self::GAMES_VOUCHER as $brand) {
                if (str_contains($nameLower, $brand)) {
                    return [];
                }
            }

            // Games with User ID + Server/Zone
            foreach (self::GAMES_WITH_SERVER as $brand) {
                if (str_contains($nameLower, $brand)) {
                    return [
                        ['name' => 'target', 'label' => 'User ID', 'type' => 'number', 'placeholder' => 'Masukkan User ID', 'required' => true],
                        ['name' => 'target_zone', 'label' => 'Zone ID', 'type' => 'number', 'placeholder' => 'Masukkan Zone ID', 'required' => true],
                    ];
                }
            }

            // Games with Player ID only
            foreach (self::GAMES_ID_ONLY as $brand) {
                if (str_contains($nameLower, $brand)) {
                    return [
                        ['name' => 'target', 'label' => 'Player ID', 'type' => 'number', 'placeholder' => 'Masukkan Player ID', 'required' => true],
                    ];
                }
            }

            // Games with Username
            foreach (self::GAMES_USERNAME as $brand) {
                if (str_contains($nameLower, $brand)) {
                    return [
                        ['name' => 'target', 'label' => 'Username', 'type' => 'text', 'placeholder' => 'Masukkan username kamu', 'required' => true],
                    ];
                }
            }

            // Voucher type default = no input
            if ($type === 'voucher') {
                return [];
            }
        }

        // Non-game: return preset by type
        return self::INPUT_PRESETS[$type] ?? self::INPUT_PRESETS['ppob'];
    }

    /**
     * Get input fields with smart auto-detection fallback.
     */
    public function getFormFields(): array
    {
        // Custom fields yang di-set manual oleh admin prioritas tertinggi
        if (!empty($this->input_fields)) {
            return $this->input_fields;
        }

        // Auto-detect dari type + nama kategori
        return self::detectInputFields($this->type, $this->name);
    }
}
