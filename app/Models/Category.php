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
     * Format kirim ke provider: "{user_id}({zone_id})" atau "{user_id}|{zone_id}"
     */
    public const GAMES_WITH_SERVER = [
        // Moonton Games (User ID + Zone ID)
        'mobile legends', 'magic chess', 'mobile legends adventure',
        // HoYoverse Games (UID + Server)
        'genshin impact', 'honkai star rail', 'honkai impact',
        // NetEase Games (User ID + Server)
        'identity v', 'onmyoji arena', 'naruto shippuden',
        'tom and jerry', 'harry potter magic awakened',
        'captain tsubasa ace', 'eggy party',
        // RPG/MMORPG (User ID + Server)
        'ragnarok', 'laplace', 'dragon nest', 'perfect world',
        'mu origin', 'seal m', 'moonlight blade', 'destiny m',
        'draconia saga', 'lifeafter', 'soul land',
        'astra knights', 'ghost story', 'isekai feast',
        'culinary tour', 'heaven burns red', 'octopath traveler',
        'au2 mobile', 'heroic uncle kim',
        // MOBA/Strategy with Server
        'arena of valor', 'honor of kings', 'lords mobile',
        'sausage man', 'aether gazer', 'punishing gray raven',
        // Others
        'saint seiya', 'mole\'s world', 'slam dunk',
        'ace racer', 'love and deepspace', 'light of thel',
    ];

    /**
     * Game brands yang cuma butuh Player ID / UID (tanpa server).
     * Format: "{player_id}"
     */
    public const GAMES_ID_ONLY = [
        // Battle Royale / FPS
        'free fire', 'pubg', 'call of duty', 'blood strike',
        'crossfire', 'point blank', 'delta force', 'arena breakout',
        'world war heroes',
        // Casual / Party
        'stumble guys', 'super sus', 'speed drifters',
        'smash legends', 'melojam', 'mob rush', 'werewolf',
        // Strategy / Kingdom
        'clash of clans', 'clash royale', 'brawl stars',
        'state of survival', 'guns of glory', 'king of avalon',
        'the ants', 'be the king', 'watcher of realms',
        'whiteout survival',
        // Sports / Racing
        'fc mobile', 'nba infinite', 'football master', 'asphalt',
        // Action / RPG (single server)
        'undawn', 'tower of fantasy', 'zenless zone zero',
        'metal slug', 'one punch man', 'marvel rivals',
        'snowbreak', 'dragonheir', 'pokemon unite',
        'afk journey', 'age of empires mobile',
        // Others
        'higgs domino', 'south park', 'wuthering waves',
        'street fighter', 'the king of fighters',
        'never after', 'night crows', 'goddess of victory',
        'heroes evolved', 'pixel gun', 'growtopia', 'zepeto',
        'teamfight tactics',
    ];

    /**
     * Game brands yang butuh Username / Riot ID (bukan ID angka).
     */
    public const GAMES_USERNAME = [
        'roblox', 'minecraft', 'fortnite', 'valorant',
        'league of legends',
    ];

    /**
     * Game/brand yang merupakan voucher (tanpa input customer).
     * Langsung kirim = dapat kode voucher.
     */
    public const GAMES_VOUCHER = [
        'steam', 'psn', 'playstation', 'xbox', 'nintendo',
        'garena shell', 'garena', 'google play', 'itunes',
        'spotify', 'netflix', 'vidio', 'genflix',
        'apple', 'razer gold', 'unipin', 'cherry credits',
        'go pay', 'gopay',
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
        return in_array(strtolower((string) $type), ['tagihan', 'postpaid', 'pasca', 'pln'], true);
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

            // Games with Username (check before ID types)
            foreach (self::GAMES_USERNAME as $brand) {
                if (str_contains($nameLower, $brand)) {
                    return [
                        ['name' => 'target', 'label' => 'Username', 'type' => 'text', 'placeholder' => 'Masukkan username kamu', 'required' => true],
                    ];
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

            // Voucher type default = no input
            if ($type === 'voucher') {
                return [];
            }

            // Fallback game: default User ID saja (tanpa server/zone)
            // Admin bisa override manual lewat input_fields jika butuh zone
            return [
                ['name' => 'target', 'label' => 'User ID', 'type' => 'text', 'placeholder' => 'Masukkan User ID / Player ID', 'required' => true],
            ];
        }

        // E-money brands yang salah masuk game
        if (str_contains($nameLower, 'dana') || str_contains($nameLower, 'ovo') ||
            str_contains($nameLower, 'shopee') || str_contains($nameLower, 'linkaja') ||
            str_contains($nameLower, 'maxim') || str_contains($nameLower, 'e-toll') ||
            str_contains($nameLower, 'mandiri') || str_contains($nameLower, 'grab')) {
            return [
                ['name' => 'target', 'label' => 'Nomor HP / E-Money', 'type' => 'tel', 'placeholder' => 'Contoh: 08123456789', 'required' => true],
            ];
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
