<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

final class OtpAuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        if ($request->user() !== null) {
            return view('account.dashboard', [
                'summary' => $this->summaryFor((int) $request->user()->id),
                'recentOrders' => $this->recentOrdersFor((int) $request->user()->id),
            ]);
        }

        return view('auth.login-otp');
    }

    public function requestOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:120'],
            'phone_number' => ['required', 'string', 'max:25', 'regex:/^[0-9]{10,16}$/'],
            'username' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[A-Za-z0-9_.-]+$/'],
            'password' => ['required', 'string', 'min:8', 'max:120'],
        ]);

        $email = strtolower(trim((string) $validated['email']));
        $phoneNumber = preg_replace('/[^0-9]/', '', (string) $validated['phone_number']) ?: '';
        $username = strtolower(trim((string) $validated['username']));
        $password = (string) $validated['password'];

        if ($phoneNumber === '') {
            return back()->withErrors(['phone_number' => 'Nomor telepon wajib angka 10-16 digit.'])->withInput();
        }

        $userByEmail = User::query()->where('email', $email)->first();
        $userByUsername = User::query()->where('username', $username)->first();

        if ($userByEmail !== null || $userByUsername !== null) {
            /** @var User $existingUser */
            $existingUser = $userByEmail ?? $userByUsername;

            if (strtolower((string) $existingUser->email) !== $email || strtolower((string) ($existingUser->username ?? '')) !== $username) {
                return back()->withErrors([
                    'email' => 'Email dan username tidak cocok dengan akun terdaftar.',
                ])->withInput();
            }

            if (!Hash::check($password, (string) $existingUser->password)) {
                $this->logSecurityEvent('ACCOUNT_LOGIN_PASSWORD_MISMATCH', 'LOW', $request, [
                    'email' => $email,
                    'username' => $username,
                ], 20, (int) $existingUser->id);

                return back()->withErrors([
                    'password' => 'Password salah.',
                ])->withInput();
            }

            if ((string) ($existingUser->phone_number ?? '') !== $phoneNumber) {
                $existingUser->update([
                    'phone_number' => $phoneNumber,
                ]);
            }

            Auth::login($existingUser);
            $request->session()->regenerate();
            $this->syncRecentOrdersToUser($request, (int) $existingUser->id);

            $this->logSecurityEvent('ACCOUNT_LOGIN_SUCCESS', 'LOW', $request, [
                'email' => $email,
                'username' => $username,
                'mode' => 'login',
            ], 5, (int) $existingUser->id);

            return redirect()->route('account.dashboard')->with('notice', 'Login berhasil.');
        }

        $phoneExists = User::query()->where('phone_number', $phoneNumber)->exists();
        if ($phoneExists) {
            return back()->withErrors([
                'phone_number' => 'Nomor telepon sudah dipakai akun lain.',
            ])->withInput();
        }

        $newUser = User::query()->create([
            'name' => $username,
            'email' => $email,
            'username' => $username,
            'phone_number' => $phoneNumber,
            'password' => $password,
            'role' => 'user',
        ]);

        Auth::login($newUser);
        $request->session()->regenerate();
        $this->syncRecentOrdersToUser($request, (int) $newUser->id);

        $this->logSecurityEvent('ACCOUNT_REGISTER_SUCCESS', 'LOW', $request, [
            'email' => $email,
            'username' => $username,
            'mode' => 'register',
        ], 5, (int) $newUser->id);

        return redirect()->route('account.dashboard')->with('notice', 'Akun berhasil dibuat dan login.');
    }

    public function showVerify(Request $request): RedirectResponse
    {
        return redirect()
            ->route('account.login-otp')
            ->withErrors(['auth' => 'Verifikasi OTP sudah tidak digunakan. Silakan login dengan data akun customer.']);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        return redirect()
            ->route('account.login-otp')
            ->withErrors(['auth' => 'Verifikasi OTP sudah tidak digunakan. Silakan login dengan data akun customer.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('account.login-otp');
    }

    private function syncRecentOrdersToUser(Request $request, int $userId): void
    {
        $recentOrderCodes = collect($request->session()->get('recent_order_codes', []))
            ->filter(static fn ($code): bool => is_string($code) && $code !== '')
            ->values();

        if ($recentOrderCodes->isEmpty()) {
            return;
        }

        Order::query()
            ->whereIn('order_code', $recentOrderCodes->all())
            ->whereNull('user_id')
            ->update(['user_id' => $userId]);
    }

    /**
     * @return array<string, int>
     */
    private function summaryFor(int $userId): array
    {
        return [
            'total_orders' => Order::query()->where('user_id', $userId)->count(),
            'success_orders' => Order::query()->where('user_id', $userId)->where('status', 'SUCCESS')->count(),
            'failed_orders' => Order::query()->where('user_id', $userId)->where('status', 'FAILED')->count(),
        ];
    }

    private function recentOrdersFor(int $userId)
    {
        return Order::query()
            ->with(['product:id,name', 'payment:id,order_id,status'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();
    }

    /**
     * @param array<string, mixed> $context
     */
    private function logSecurityEvent(string $eventCode, string $severity, Request $request, array $context, int $riskScore, ?int $userId = null): void
    {
        SecurityEvent::query()->create([
            'event_code' => $eventCode,
            'severity' => $severity,
            'user_id' => $userId,
            'device_id' => null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'risk_score' => $riskScore,
            'context' => $context,
            'occurred_at' => now(),
        ]);
    }
}
