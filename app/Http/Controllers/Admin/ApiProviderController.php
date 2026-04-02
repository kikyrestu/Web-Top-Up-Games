<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ApiProviderController extends Controller
{
    private const MASK_PLACEHOLDER = '*****';
    private const PIN_MAX_ATTEMPTS = 5;
    private const PIN_DECAY_SECONDS = 300;

    private array $providerCodeMap = [
        'rajabiller' => 'rajabiller',
        'digiflazz' => 'digiflazz',
        'orderkuota' => 'orderkuota',
        'orderkouta' => 'orderkuota',
    ];

    public function index()
    {
        $providers = ApiProvider::latest()->get();
        $hasApiPin = (bool) optional(auth()->user())->api_pin_hash;
        $pinSecurityStatus = $this->pinSecurityStatus(request());

        return view('admin.api_providers.index', compact('providers', 'hasApiPin', 'pinSecurityStatus'));
    }

    public function create(Request $request)
    {
        $providerOptions = [
            'rajabiller' => 'Rajabiller',
            'digiflazz' => 'Digiflazz',
            'orderkuota' => 'OrderKuota',
        ];

        $hasApiPin = (bool) optional(auth()->user())->api_pin_hash;
        $pinSecurityStatus = $this->pinSecurityStatus($request);

        return view('admin.api_providers.create', compact('providerOptions', 'hasApiPin', 'pinSecurityStatus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:api_providers,code',
            'provider' => 'nullable|in:rajabiller,digiflazz,orderkuota,orderkouta',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string',
        ]);

        $data = $request->only('name', 'description');
        $data['code'] = $this->normalizeCode($request->input('provider'), $request->input('code'));
        $data['is_active'] = $request->has('is_active');

        $isCredentialChanging = $this->hasCredentialChanges($request->input('credentials', []), []);
        $this->enforceCredentialSecurity($request, $isCredentialChanging);
        $data['credentials'] = $this->buildCredentialPayload($request->input('credentials', []), []);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('providers', 'public');
        }

        ApiProvider::create($data);

        return redirect()->route('admin.api-providers.index')->with('success', 'API Provider berhasil ditambahkan.');
    }

    public function edit(Request $request, ApiProvider $apiProvider)
    {
        $providerOptions = [
            'rajabiller' => 'Rajabiller',
            'digiflazz' => 'Digiflazz',
            'orderkuota' => 'OrderKuota',
        ];

        $maskedCredentials = $this->maskedCredentials($apiProvider->credentials ?? []);
        $hasApiPin = (bool) optional(auth()->user())->api_pin_hash;
        $pinSecurityStatus = $this->pinSecurityStatus($request);

        return view('admin.api_providers.edit', compact('apiProvider', 'providerOptions', 'maskedCredentials', 'hasApiPin', 'pinSecurityStatus'));
    }

    public function update(Request $request, ApiProvider $apiProvider)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:api_providers,code,'.$apiProvider->id,
            'provider' => 'nullable|in:rajabiller,digiflazz,orderkuota,orderkouta',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string',
        ]);

        $data = $request->only('name', 'description');
        $data['code'] = $this->normalizeCode($request->input('provider'), $request->input('code'));
        $data['is_active'] = $request->has('is_active');

        $existingCredentials = $apiProvider->credentials ?? [];
        $isCredentialChanging = $this->hasCredentialChanges($request->input('credentials', []), $existingCredentials);
        $this->enforceCredentialSecurity($request, $isCredentialChanging);
        $data['credentials'] = $this->buildCredentialPayload($request->input('credentials', []), $existingCredentials);

        if ($request->hasFile('logo')) {
            if ($apiProvider->logo) {
                Storage::disk('public')->delete($apiProvider->logo);
            }
            $data['logo'] = $request->file('logo')->store('providers', 'public');
        }

        $apiProvider->update($data);

        return redirect()->route('admin.api-providers.index')->with('success', 'API Provider berhasil diperbarui.');
    }

    public function destroy(ApiProvider $apiProvider)
    {
        if ($apiProvider->logo) {
            Storage::disk('public')->delete($apiProvider->logo);
        }
        $apiProvider->delete();
        return redirect()->route('admin.api-providers.index')->with('success', 'API Provider dihapus.');
    }

    public function testConnection(ApiProvider $apiProvider)
    {
        if (!$apiProvider->credentials || empty(array_filter(
            collect($apiProvider->credentials)->except('__hashes')->toArray()
        ))) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial belum diisi. Simpan kredensial terlebih dahulu.',
            ]);
        }

        $code = strtolower($apiProvider->code);

        try {
            if ($code === 'digiflazz') {
                $creds    = collect($apiProvider->credentials)->except('__hashes')->toArray();
                $username = $creds['username'] ?? '';
                $apiKey   = $creds['api_key'] ?? '';
                $baseUrl  = isset($creds['url']) && !empty($creds['url'])
                    ? rtrim($creds['url'], '/') . '/'
                    : 'https://api.digiflazz.com/v1/';

                if (empty($username) || empty($apiKey)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Credentials belum lengkap. Pastikan Username dan API Key sudah diisi.',
                    ]);
                }

                $sign = md5($username . $apiKey . 'pricelist');

                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(15)->post($baseUrl . 'price-list', [
                        'cmd'      => 'prepaid',
                        'username' => $username,
                        'sign'     => $sign,
                    ]);

                    $result = $response->json();

                    \Illuminate\Support\Facades\Log::info('Digiflazz testConnection raw', [
                        'username' => $username,
                        'sign'     => $sign,
                        'status'   => $response->status(),
                        'response' => $result,
                    ]);

                    if (isset($result['data']) && is_array($result['data']) && count($result['data']) > 0) {
                        return response()->json([
                            'success' => true,
                            'message' => '✅ Koneksi Digiflazz berhasil! ' . count($result['data']) . ' produk ditemukan.',
                        ]);
                    }

                    // Show the actual API error message if available
                    $apiMessage = $result['message'] ?? ($result['error'] ?? null);
                    if ($apiMessage) {
                        return response()->json([
                            'success' => false,
                            'message' => 'API Digiflazz merespons: "' . $apiMessage . '". Periksa Username dan API Key.',
                        ]);
                    }

                    // Empty data - might be a sandbox account with no products
                    return response()->json([
                        'success' => false,
                        'message' => 'Koneksi OK tapi data produk kosong. Kemungkinan: (1) Username/API Key salah, (2) Akun belum aktif, atau (3) Coba gunakan API Key Production bukan Development.',
                    ]);

                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Koneksi gagal: ' . $e->getMessage(),
                    ]);
                }
            }

            if ($code === 'rajabiller') {
                $service = app(\App\Services\RajabillerService::class);
                $creds   = collect($apiProvider->credentials)->except('__hashes')->toArray();
                $result  = $service->cekSaldo($creds);

                // rc=00 means success in Rajabiller
                if (isset($result['rc']) && $result['rc'] === '00') {
                    $balance = isset($result['saldo']) ? 'Saldo: Rp ' . number_format((float) $result['saldo'], 0, ',', '.') : '';
                    return response()->json([
                        'success' => true,
                        'message' => 'Koneksi Rajabiller berhasil! ' . $balance,
                    ]);
                }

                $errMsg = $result['status'] ?? $result['rc'] ?? 'Unknown error';
                return response()->json([
                    'success' => false,
                    'message' => 'Koneksi gagal. Rajabiller respons: ' . $errMsg . '. Periksa UID dan PIN.',
                ]);
            }

            if ($code === 'orderkuota') {
                $service = new \App\Services\OrderKuotaService();
                $creds   = collect($apiProvider->credentials)->except('__hashes')->toArray();
                $result  = $service->cekSaldo($creds);

                if ($result['success']) {
                    $balance = isset($result['saldo']) ? 'Saldo: Rp ' . number_format((float) $result['saldo'], 0, ',', '.') : '';
                    return response()->json([
                        'success' => true,
                        'message' => '✅ Koneksi OrderKuota (OkeConnect) berhasil! ' . $balance,
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Koneksi gagal: ' . ($result['message'] ?? 'Unknown error') . '. Periksa Member ID, PIN, dan Password.',
                ]);
            }

            // Generic test for other providers — just check credentials exist
            return response()->json([
                'success' => true,
                'message' => 'Kredensial ' . $apiProvider->name . ' tersimpan. Test koneksi otomatis belum tersedia untuk provider ini.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Koneksi gagal: ' . $e->getMessage(),
            ]);
        }
    }

    private function normalizeCode(?string $provider, ?string $code): string
    {
        if ($provider && isset($this->providerCodeMap[$provider])) {
            return $this->providerCodeMap[$provider];
        }

        return strtolower((string) $code);
    }

    private function buildCredentialPayload(array $incoming, array $existing): array
    {
        $existing = $this->removeMetaKeys($existing);
        $clean = $existing;
        $hashes = [];

        foreach ($incoming as $key => $value) {
            $normalizedKey = trim((string) $key);
            if ($normalizedKey === '') {
                continue;
            }

            $normalizedValue = trim((string) $value);
            if ($normalizedValue === '' || $normalizedValue === self::MASK_PLACEHOLDER || preg_match('/^\*+$/', $normalizedValue)) {
                if (array_key_exists($normalizedKey, $existing)) {
                    $clean[$normalizedKey] = $existing[$normalizedKey];
                    $hashes[$normalizedKey] = hash('sha256', (string) $existing[$normalizedKey]);
                }
                continue;
            }

            $clean[$normalizedKey] = $normalizedValue;
            $hashes[$normalizedKey] = hash('sha256', $normalizedValue);
        }

        foreach ($clean as $key => $value) {
            if (!isset($hashes[$key]) && is_string($value) && $value !== '') {
                $hashes[$key] = hash('sha256', $value);
            }
        }

        $clean['__hashes'] = $hashes;

        return $clean;
    }

    private function maskedCredentials(array $credentials): array
    {
        $credentials = $this->removeMetaKeys($credentials);
        $masked = [];
        foreach ($credentials as $key => $value) {
            $masked[$key] = self::MASK_PLACEHOLDER;
        }
        return $masked;
    }

    private function removeMetaKeys(array $credentials): array
    {
        unset($credentials['__hashes']);
        return $credentials;
    }

    private function hasCredentialChanges(array $incoming, array $existing): bool
    {
        $existing = $this->removeMetaKeys($existing);

        foreach ($incoming as $key => $value) {
            $normalizedKey = trim((string) $key);
            if ($normalizedKey === '') {
                continue;
            }

            $normalizedValue = trim((string) $value);
            if ($normalizedValue === '' || $normalizedValue === self::MASK_PLACEHOLDER || preg_match('/^\*+$/', $normalizedValue)) {
                continue;
            }

            if (!array_key_exists($normalizedKey, $existing) || (string) $existing[$normalizedKey] !== $normalizedValue) {
                return true;
            }
        }

        return false;
    }

    private function enforceCredentialSecurity(Request $request, bool $isCredentialChanging): void
    {
        if (!$isCredentialChanging) {
            return;
        }

        $user = $request->user();
        if (!$user) {
            throw ValidationException::withMessages([
                'current_password' => 'Sesi login tidak valid. Silakan login ulang.',
            ]);
        }

        $request->validate([
            'current_password' => 'required|string',
        ]);

        if (!Hash::check((string) $request->input('current_password'), (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password admin salah.',
            ]);
        }

        if (empty($user->api_pin_hash)) {
            $request->validate([
                'new_pin' => 'required|digits:6|confirmed',
            ], [
                'new_pin.required' => 'PIN baru wajib diisi untuk aktivasi keamanan API key.',
                'new_pin.digits' => 'PIN harus 6 digit angka.',
                'new_pin.confirmed' => 'Konfirmasi PIN tidak cocok.',
            ]);

            $user->api_pin_hash = Hash::make((string) $request->input('new_pin'));
            $user->api_pin_set_at = now();
            $user->save();
            return;
        }

        $request->validate([
            'security_pin' => 'required|digits:6',
        ], [
            'security_pin.required' => 'PIN admin wajib diisi untuk mengganti API key.',
            'security_pin.digits' => 'PIN harus 6 digit angka.',
        ]);

        $pinRateKey = $this->pinRateKey($request);

        if (RateLimiter::tooManyAttempts($pinRateKey, self::PIN_MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($pinRateKey);
            $minutes = (int) ceil($seconds / 60);
            throw ValidationException::withMessages([
                'security_pin' => "Terlalu banyak percobaan PIN gagal. Coba lagi dalam {$minutes} menit.",
            ]);
        }

        if (!Hash::check((string) $request->input('security_pin'), (string) $user->api_pin_hash)) {
            RateLimiter::hit($pinRateKey, self::PIN_DECAY_SECONDS);

            $remainingAttempts = RateLimiter::remaining($pinRateKey, self::PIN_MAX_ATTEMPTS);
            if ($remainingAttempts <= 0) {
                $seconds = RateLimiter::availableIn($pinRateKey);
                $minutes = (int) ceil($seconds / 60);
                throw ValidationException::withMessages([
                    'security_pin' => "PIN salah. Batas percobaan tercapai, akun dikunci {$minutes} menit.",
                ]);
            }

            throw ValidationException::withMessages([
                'security_pin' => 'PIN admin salah. Sisa percobaan: ' . $remainingAttempts . ' dari ' . self::PIN_MAX_ATTEMPTS . '.',
            ]);
        }

        RateLimiter::clear($pinRateKey);
    }

    private function pinRateKey(Request $request): string
    {
        $userId = (string) optional($request->user())->id;
        $ip = (string) $request->ip();

        return 'api-provider-pin:' . $userId . ':' . $ip;
    }

    private function pinSecurityStatus(Request $request): array
    {
        $user = $request->user();
        $hasApiPin = (bool) optional($user)->api_pin_hash;

        if (!$hasApiPin) {
            return [
                'has_api_pin' => false,
                'is_locked' => false,
                'attempts_remaining' => self::PIN_MAX_ATTEMPTS,
                'max_attempts' => self::PIN_MAX_ATTEMPTS,
                'lock_seconds' => 0,
            ];
        }

        $key = $this->pinRateKey($request);
        $isLocked = RateLimiter::tooManyAttempts($key, self::PIN_MAX_ATTEMPTS);

        return [
            'has_api_pin' => true,
            'is_locked' => $isLocked,
            'attempts_remaining' => RateLimiter::remaining($key, self::PIN_MAX_ATTEMPTS),
            'max_attempts' => self::PIN_MAX_ATTEMPTS,
            'lock_seconds' => $isLocked ? RateLimiter::availableIn($key) : 0,
        ];
    }
}
