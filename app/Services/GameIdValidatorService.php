<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GameIdValidatorService
{
    /**
     * Map game brand keywords → validation config.
     * 'key' is matched against lowercase category name.
     * 'endpoint', 'method', 'params' define how to call the API.
     * 'parse' is the response parser key.
     */
    protected const GAME_VALIDATORS = [
        'mobile legends' => [
            'code' => 'ml',
            'label' => 'Mobile Legends',
            'requires_zone' => true,
        ],
        'free fire' => [
            'code' => 'ff',
            'label' => 'Free Fire',
            'requires_zone' => false,
        ],
        'genshin impact' => [
            'code' => 'gi',
            'label' => 'Genshin Impact',
            'requires_zone' => true,
        ],
        'honkai star rail' => [
            'code' => 'hsr',
            'label' => 'Honkai Star Rail',
            'requires_zone' => true,
        ],
        'valorant' => [
            'code' => 'vr',
            'label' => 'Valorant',
            'requires_zone' => false,
        ],
        'pubg' => [
            'code' => 'pubg',
            'label' => 'PUBG Mobile',
            'requires_zone' => false,
        ],
        'arena of valor' => [
            'code' => 'aov',
            'label' => 'Arena of Valor',
            'requires_zone' => false,
        ],
        'point blank' => [
            'code' => 'pb',
            'label' => 'Point Blank',
            'requires_zone' => false,
        ],
        'higgs domino' => [
            'code' => 'hd',
            'label' => 'Higgs Domino',
            'requires_zone' => false,
        ],
        'stumble guys' => [
            'code' => 'sg',
            'label' => 'Stumble Guys',
            'requires_zone' => false,
        ],
        'super sus' => [
            'code' => 'ss',
            'label' => 'Super Sus',
            'requires_zone' => false,
        ],
        'sausage man' => [
            'code' => 'sm',
            'label' => 'Sausage Man',
            'requires_zone' => true,
        ],
        'clash of clans' => [
            'code' => 'coc',
            'label' => 'Clash of Clans',
            'requires_zone' => false,
        ],
        'tower of fantasy' => [
            'code' => 'tof',
            'label' => 'Tower of Fantasy',
            'requires_zone' => false,
        ],
        'call of duty' => [
            'code' => 'cod',
            'label' => 'Call of Duty Mobile',
            'requires_zone' => false,
        ],
        'blood strike' => [
            'code' => 'bs',
            'label' => 'Blood Strike',
            'requires_zone' => false,
        ],
        'honkai impact' => [
            'code' => 'hi3',
            'label' => 'Honkai Impact 3rd',
            'requires_zone' => true,
        ],
        'zenless zone zero' => [
            'code' => 'zzz',
            'label' => 'Zenless Zone Zero',
            'requires_zone' => true,
        ],
        'wuthering waves' => [
            'code' => 'ww',
            'label' => 'Wuthering Waves',
            'requires_zone' => false,
        ],
        'ragnarok' => [
            'code' => 'rag',
            'label' => 'Ragnarok',
            'requires_zone' => true,
        ],
        'identity v' => [
            'code' => 'idv',
            'label' => 'Identity V',
            'requires_zone' => true,
        ],
        'eggy party' => [
            'code' => 'ep',
            'label' => 'Eggy Party',
            'requires_zone' => false,
        ],
        'ace racer' => [
            'code' => 'ar',
            'label' => 'Ace Racer',
            'requires_zone' => true,
        ],
        'love and deepspace' => [
            'code' => 'lads',
            'label' => 'Love and Deepspace',
            'requires_zone' => true,
        ],
    ];

    /**
     * Base URL for the nickname validation API.
     * Can be overridden via GAME_VALIDATOR_API_URL env var or admin settings.
     */
    protected string $apiBaseUrl;

    /**
     * API provider type: 'isan', 'vip_reseller', 'custom'
     */
    protected string $apiProvider;

    /**
     * API key (for providers that require authentication).
     */
    protected string $apiKey;

    public function __construct()
    {
        $this->apiProvider = \App\Models\Setting::get('game_validator_provider', env('GAME_VALIDATOR_PROVIDER', 'isan'));
        $this->apiKey = \App\Models\Setting::get('game_validator_api_key', env('GAME_VALIDATOR_API_KEY', ''));

        $defaultUrls = [
            'isan' => 'https://api.isan.eu.org',
            'vip_reseller' => 'https://vip-reseller.co.id/api',
        ];

        $this->apiBaseUrl = rtrim(
            \App\Models\Setting::get('game_validator_api_url', env('GAME_VALIDATOR_API_URL', $defaultUrls[$this->apiProvider] ?? '')),
            '/'
        );
    }

    /**
     * Detect game code from category name.
     */
    public function detectGameFromCategory(string $categoryName): ?array
    {
        $nameLower = strtolower($categoryName);

        foreach (self::GAME_VALIDATORS as $keyword => $config) {
            if (str_contains($nameLower, $keyword)) {
                return $config;
            }
        }

        return null;
    }

    /**
     * Check if a category supports ID validation.
     */
    public function supportsValidation(string $categoryName): bool
    {
        return $this->detectGameFromCategory($categoryName) !== null;
    }

    /**
     * Validate a game ID and return the nickname.
     *
     * @return array{success: bool, nickname: string, message: string, game: string}
     */
    public function validate(string $categoryName, string $userId, ?string $zoneId = null): array
    {
        $game = $this->detectGameFromCategory($categoryName);

        if (!$game) {
            return [
                'success' => false,
                'nickname' => '',
                'message' => 'Game ini belum mendukung validasi ID.',
                'game' => '',
            ];
        }

        if ($game['requires_zone'] && empty($zoneId)) {
            return [
                'success' => false,
                'nickname' => '',
                'message' => 'Zone/Server ID wajib diisi untuk ' . $game['label'] . '.',
                'game' => $game['code'],
            ];
        }

        // Check cache first (avoid hitting API too often for same ID)
        $cacheKey = "game_id_check:{$game['code']}:{$userId}:{$zoneId}";
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $result = $this->callValidationApi($game, $userId, $zoneId);

            // Cache successful results for 10 minutes, failures for 2 minutes
            $ttl = $result['success'] ? 600 : 120;
            Cache::put($cacheKey, $result, $ttl);

            return $result;
        } catch (\Exception $e) {
            Log::error('Game ID Validation error', [
                'game' => $game['code'],
                'user_id' => $userId,
                'zone_id' => $zoneId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'nickname' => '',
                'message' => 'Gagal menghubungi server validasi. Silakan coba lagi.',
                'game' => $game['code'],
            ];
        }
    }

    /**
     * Call the validation API for a specific game.
     */
    protected function callValidationApi(array $game, string $userId, ?string $zoneId): array
    {
        return match ($this->apiProvider) {
            'vip_reseller' => $this->callVipResellerApi($game, $userId, $zoneId),
            'custom' => $this->callCustomApi($game, $userId, $zoneId),
            default => $this->callIsanApi($game, $userId, $zoneId),
        };
    }

    /**
     * Call api.isan.eu.org nickname API.
     * GET /nickname/{game_code}?id={user_id}&zone={zone_id}
     */
    protected function callIsanApi(array $game, string $userId, ?string $zoneId): array
    {
        $params = ['id' => $userId];
        if (!empty($zoneId)) {
            $params['zone'] = $zoneId;
        }

        $url = $this->apiBaseUrl . '/nickname/' . $game['code'] . '?' . http_build_query($params);

        $response = Http::timeout(15)
            ->withHeaders(['Accept' => 'application/json', 'User-Agent' => 'TopUpGame/1.0'])
            ->get($url);

        if (!$response->successful()) {
            return [
                'success' => false,
                'nickname' => '',
                'message' => 'Server validasi tidak merespon (HTTP ' . $response->status() . ').',
                'game' => $game['code'],
            ];
        }

        return $this->parseApiResponse($response->json(), $game);
    }

    /**
     * Call VIP Reseller game-feature API.
     * POST /game-feature with key, type=get-nickname, code, target, additional_target
     */
    protected function callVipResellerApi(array $game, string $userId, ?string $zoneId): array
    {
        // VIP Reseller game codes mapping
        $vipGameCodes = [
            'ml' => 'mobilelegends', 'ff' => 'freefire', 'gi' => 'genshinimpact',
            'hsr' => 'honkaistarrail', 'vr' => 'valorant', 'pubg' => 'pubgmobile',
            'aov' => 'arenaofvalor', 'cod' => 'callofduty', 'coc' => 'clashofclans',
            'hi3' => 'honkaiimpact', 'zzz' => 'zenlesszonezero',
        ];

        $gameCode = $vipGameCodes[$game['code']] ?? $game['code'];

        $payload = [
            'key' => $this->apiKey,
            'type' => 'get-nickname',
            'code' => $gameCode,
            'target' => $userId,
        ];

        if (!empty($zoneId)) {
            $payload['additional_target'] = $zoneId;
        }

        $response = Http::timeout(15)
            ->asForm()
            ->post($this->apiBaseUrl . '/game-feature', $payload);

        if (!$response->successful()) {
            return [
                'success' => false,
                'nickname' => '',
                'message' => 'Server validasi tidak merespon.',
                'game' => $game['code'],
            ];
        }

        $data = $response->json();

        if (($data['result'] ?? false) === true && !empty($data['data'])) {
            $nickname = $data['data'] ?? '';
            if (is_array($nickname)) {
                $nickname = $nickname['username'] ?? $nickname['name'] ?? $nickname['nickname'] ?? '';
            }
            return [
                'success' => true,
                'nickname' => (string) $nickname,
                'message' => 'ID valid! Username: ' . $nickname,
                'game' => $game['code'],
            ];
        }

        return [
            'success' => false,
            'nickname' => '',
            'message' => $data['message'] ?? 'ID tidak ditemukan.',
            'game' => $game['code'],
        ];
    }

    /**
     * Call custom API endpoint.
     * GET {base_url}/{game_code}?user_id={user_id}&zone_id={zone_id}
     * Supports flexible response parsing.
     */
    protected function callCustomApi(array $game, string $userId, ?string $zoneId): array
    {
        $params = ['user_id' => $userId, 'game' => $game['code']];
        if (!empty($zoneId)) {
            $params['zone_id'] = $zoneId;
        }

        $headers = ['Accept' => 'application/json', 'User-Agent' => 'TopUpGame/1.0'];
        if (!empty($this->apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        $response = Http::timeout(15)
            ->withHeaders($headers)
            ->get($this->apiBaseUrl . '/nickname', $params);

        if (!$response->successful()) {
            return [
                'success' => false,
                'nickname' => '',
                'message' => 'Server validasi tidak merespon (HTTP ' . $response->status() . ').',
                'game' => $game['code'],
            ];
        }

        return $this->parseApiResponse($response->json(), $game);
    }

    /**
     * Parse API response to extract nickname.
     * Supports multiple response formats from different APIs.
     */
    protected function parseApiResponse(array $data, array $game): array
    {
        // Format 1: { "success": true, "result": "NickName" }
        // Format 2: { "status": true/200, "data": { "name": "NickName" } }
        // Format 3: { "success": true, "name": "NickName" }
        // Format 4: { "result": { "username": "NickName" } }

        $nickname = '';
        $success = false;

        // Try various response formats
        if (isset($data['success']) && $data['success'] === true) {
            $nickname = $data['result'] ?? $data['name'] ?? $data['nickname'] ?? $data['username'] ?? '';
            if (is_array($nickname)) {
                $nickname = $nickname['name'] ?? $nickname['username'] ?? $nickname['nickname'] ?? '';
            }
            $success = !empty($nickname);
        } elseif (isset($data['status']) && in_array($data['status'], [true, 200, 'true', 'success', 'ok'], true)) {
            $d = $data['data'] ?? $data;
            $nickname = $d['name'] ?? $d['username'] ?? $d['nickname'] ?? $d['result'] ?? '';
            if (is_array($nickname)) {
                $nickname = $nickname['name'] ?? $nickname['username'] ?? '';
            }
            $success = !empty($nickname);
        } elseif (isset($data['result'])) {
            if (is_string($data['result'])) {
                $nickname = $data['result'];
                $success = !empty($nickname);
            } elseif (is_array($data['result'])) {
                $nickname = $data['result']['username'] ?? $data['result']['name'] ?? $data['result']['nickname'] ?? '';
                $success = !empty($nickname);
            }
        } elseif (isset($data['name']) || isset($data['nickname']) || isset($data['username'])) {
            $nickname = $data['name'] ?? $data['nickname'] ?? $data['username'] ?? '';
            $success = !empty($nickname);
        }

        if ($success && !empty($nickname)) {
            return [
                'success' => true,
                'nickname' => (string) $nickname,
                'message' => 'ID valid! Username: ' . $nickname,
                'game' => $game['code'],
            ];
        }

        $errorMsg = $data['message'] ?? $data['error'] ?? $data['msg'] ?? '';

        return [
            'success' => false,
            'nickname' => '',
            'message' => !empty($errorMsg) ? (string) $errorMsg : 'ID tidak ditemukan. Pastikan ID yang dimasukkan benar.',
            'game' => $game['code'],
        ];
    }

    /**
     * Get all supported game codes for frontend reference.
     */
    public static function getSupportedGames(): array
    {
        return collect(self::GAME_VALIDATORS)->map(fn($v) => [
            'code' => $v['code'],
            'label' => $v['label'],
            'requires_zone' => $v['requires_zone'],
        ])->values()->all();
    }
}
