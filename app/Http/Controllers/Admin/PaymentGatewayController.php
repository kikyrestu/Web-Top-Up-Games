<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PaymentGatewayController extends Controller
{
    private const MASK_PLACEHOLDER = '*****';
    private const PIN_MAX_ATTEMPTS = 5;
    private const PIN_DECAY_SECONDS = 300;

    private array $providerCodeMap = [
        'midtrans' => 'midtrans',
        'klikqris' => 'klikqris',
        'doku' => 'doku',
        'dompetx' => 'doku',
    ];

    public function index()
    {
        $gateways = PaymentGateway::latest()->get();
        $hasApiPin = (bool) optional(auth()->user())->api_pin_hash;
        $pinSecurityStatus = $this->pinSecurityStatus(request());

        return view('admin.payment_gateways.index', compact('gateways', 'hasApiPin', 'pinSecurityStatus'));
    }

    public function create(Request $request)
    {
        $providerOptions = [
            'midtrans' => 'Midtrans',
            'klikqris' => 'KlikQRIS',
            'doku' => 'DompetX / DOKU',
        ];

        $hasApiPin = (bool) optional(auth()->user())->api_pin_hash;
        $pinSecurityStatus = $this->pinSecurityStatus($request);

        return view('admin.payment_gateways.create', compact('providerOptions', 'hasApiPin', 'pinSecurityStatus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_gateways',
            'provider' => 'nullable|in:midtrans,klikqris,doku,dompetx',
            'logo' => 'nullable|image|max:2048',
            'fee_flat' => 'numeric|min:0',
            'fee_percent' => 'numeric|min:0|max:100',
        ]);

        $data = $request->except('logo', 'is_active', 'is_test_mode');
        $data['code'] = $this->normalizeCode($request->input('provider'), $request->input('code'));
        $data['is_active'] = $request->has('is_active');
        $data['is_test_mode'] = $request->has('is_test_mode');

        $isCredentialChanging = $this->hasCredentialChanges($request->input('credentials', []), []);
        $this->enforceCredentialSecurity($request, $isCredentialChanging);

        $data['credentials'] = $this->buildCredentialPayload($request->input('credentials', []), []);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('gateways', 'public');
        }

        PaymentGateway::create($data);

        return redirect()->route('admin.payment-gateways.index')->with('success', 'Payment Gateway berhasil ditambahkan.');
    }

    public function edit(Request $request, PaymentGateway $paymentGateway)
    {
        $providerOptions = [
            'midtrans' => 'Midtrans',
            'klikqris' => 'KlikQRIS',
            'doku' => 'DompetX / DOKU',
        ];

        $maskedCredentials = $this->maskedCredentials($paymentGateway->credentials ?? []);
        $hasApiPin = (bool) optional(auth()->user())->api_pin_hash;
        $pinSecurityStatus = $this->pinSecurityStatus($request);

        return view('admin.payment_gateways.edit', compact('paymentGateway', 'providerOptions', 'maskedCredentials', 'hasApiPin', 'pinSecurityStatus'));
    }

    public function update(Request $request, PaymentGateway $paymentGateway)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_gateways,code,'.$paymentGateway->id,
            'provider' => 'nullable|in:midtrans,klikqris,doku,dompetx',
            'logo' => 'nullable|image|max:2048',
            'fee_flat' => 'numeric|min:0',
            'fee_percent' => 'numeric|min:0|max:100',
        ]);

        $data = $request->except('logo', 'is_active', 'is_test_mode');
        $data['code'] = $this->normalizeCode($request->input('provider'), $request->input('code'));
        $data['is_active'] = $request->has('is_active');
        $data['is_test_mode'] = $request->has('is_test_mode');

        $existingCredentials = $paymentGateway->credentials ?? [];
        $isCredentialChanging = $this->hasCredentialChanges($request->input('credentials', []), $existingCredentials);
        $this->enforceCredentialSecurity($request, $isCredentialChanging);

        $data['credentials'] = $this->buildCredentialPayload(
            $request->input('credentials', []),
            $existingCredentials
        );

        if ($request->hasFile('logo')) {
            if ($paymentGateway->logo) Storage::disk('public')->delete($paymentGateway->logo);
            $data['logo'] = $request->file('logo')->store('gateways', 'public');
        }

        $paymentGateway->update($data);

        return redirect()->route('admin.payment-gateways.index')->with('success', 'Payment Gateway berhasil diperbarui.');
    }

    public function destroy(PaymentGateway $paymentGateway)
    {
        if ($paymentGateway->logo) Storage::disk('public')->delete($paymentGateway->logo);
        $paymentGateway->delete();
        return redirect()->route('admin.payment-gateways.index')->with('success', 'Payment Gateway dihapus.');
    }

    public function testConnection(PaymentGateway $paymentGateway)
    {
        if (!$paymentGateway->credentials || empty(array_filter(
            collect($paymentGateway->credentials)->except('__hashes')->toArray()
        ))) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial belum diisi. Simpan kredensial terlebih dahulu.',
            ]);
        }

        $code = strtolower($paymentGateway->code);

        try {
            if ($code === 'midtrans') {
                $serverKey = $paymentGateway->credentials['server_key'] ?? '';
                $baseUrl = $paymentGateway->is_test_mode
                    ? 'https://api.sandbox.midtrans.com'
                    : 'https://api.midtrans.com';

                // Use the /v2/point_of_sales endpoint to verify server key auth
                $response = \Illuminate\Support\Facades\Http::withBasicAuth($serverKey, '')
                    ->timeout(10)
                    ->get($baseUrl . '/v2/point_of_sales');

                // 401 = bad key, anything else (200/404/etc) means auth is valid
                if ($response->status() === 401) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Server Key Midtrans tidak valid (401 Unauthorized). Periksa kembali key Anda.',
                    ]);
                }

                $modeLabel = $paymentGateway->is_test_mode ? 'Sandbox' : 'Production';
                return response()->json([
                    'success' => true,
                    'message' => "Koneksi Midtrans ({$modeLabel}) berhasil! Server key terautentikasi.",
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kredensial ' . $paymentGateway->name . ' tersimpan. Test koneksi otomatis belum tersedia untuk gateway ini.',
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

            // Keep old secret when admin leaves blank field or keeps masked marker.
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

        return 'payment-gateway-pin:' . $userId . ':' . $ip;
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
