<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OtpRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            'channel' => ['required', 'in:EMAIL,WA'],
            'destination' => ['required', 'string', 'max:120'],
        ]);

        $channel = strtoupper((string) $validated['channel']);
        $destination = trim((string) $validated['destination']);

        if ($channel === 'EMAIL' && !filter_var($destination, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors(['destination' => 'Format email tidak valid.'])->withInput();
        }

        if ($channel === 'WA' && !preg_match('/^[0-9]{10,16}$/', $destination)) {
            return back()->withErrors(['destination' => 'Format nomor WA harus angka 10-16 digit.'])->withInput();
        }

        $recentCount = OtpRequest::query()
            ->where('channel', $channel)
            ->where('destination', $destination)
            ->where('created_at', '>=', now()->subMinutes(10))
            ->count();

        if ($recentCount >= 3) {
            return back()->withErrors(['destination' => 'Terlalu banyak request OTP. Coba lagi 10 menit lagi.'])->withInput();
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpRequest::query()->create([
            'channel' => $channel,
            'destination' => $destination,
            'code_hash' => Hash::make($otp),
            'attempt_count' => 0,
            'expires_at' => now()->addMinutes(5),
            'request_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->session()->put('otp_pending', [
            'channel' => $channel,
            'destination' => $destination,
        ]);

        return redirect()
            ->route('account.verify-otp')
            ->with('notice', 'OTP berhasil dibuat. Demo code: '.$otp);
    }

    public function showVerify(Request $request): View
    {
        $pending = $request->session()->get('otp_pending');

        if (!is_array($pending)) {
            return redirect()->route('account.login-otp');
        }

        return view('auth.verify-otp', [
            'pending' => $pending,
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $pending = $request->session()->get('otp_pending');

        if (!is_array($pending)) {
            return redirect()->route('account.login-otp');
        }

        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $channel = strtoupper((string) ($pending['channel'] ?? ''));
        $destination = (string) ($pending['destination'] ?? '');

        $otpRequest = OtpRequest::query()
            ->where('channel', $channel)
            ->where('destination', $destination)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if ($otpRequest === null || $otpRequest->expires_at === null || $otpRequest->expires_at->isPast()) {
            return back()->withErrors(['code' => 'OTP tidak ditemukan atau sudah expired.']);
        }

        if ((int) $otpRequest->attempt_count >= 5) {
            return back()->withErrors(['code' => 'OTP diblokir karena terlalu banyak percobaan.']);
        }

        $otpRequest->increment('attempt_count');

        if (!Hash::check((string) $validated['code'], (string) $otpRequest->code_hash)) {
            return back()->withErrors(['code' => 'Kode OTP salah.']);
        }

        $otpRequest->update([
            'verified_at' => now(),
        ]);

        $user = $this->resolveUser($channel, $destination);

        Auth::login($user);
        $request->session()->regenerate();
        $this->syncRecentOrdersToUser($request, (int) $user->id);
        $request->session()->forget('otp_pending');

        return redirect()->route('account.dashboard')->with('notice', 'Login OTP berhasil.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('account.login-otp');
    }

    private function resolveUser(string $channel, string $destination): User
    {
        if ($channel === 'EMAIL') {
            $user = User::query()->where('email', $destination)->first();
            if ($user !== null) {
                return $user;
            }

            return User::query()->create([
                'name' => Str::headline((string) Str::before($destination, '@')),
                'email' => $destination,
                'password' => Str::random(40),
                'role' => 'user',
            ]);
        }

        $normalized = preg_replace('/[^0-9]/', '', $destination) ?: $destination;
        $email = 'wa_'.$normalized.'@guest.local';

        $user = User::query()->where('email', $email)->first();
        if ($user !== null) {
            return $user;
        }

        return User::query()->create([
            'name' => 'WA User '.substr($normalized, -4),
            'email' => $email,
            'password' => Str::random(40),
            'role' => 'user',
        ]);
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
}
