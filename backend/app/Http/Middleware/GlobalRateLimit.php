<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

final class GlobalRateLimit
{
    /**
     * @return array{0:int,1:int,2:string}
     */
    private function profileFor(string $profile): array
    {
        return match ($profile) {
            'otp-login' => [5, 60, 'Terlalu banyak request OTP login. Coba lagi 1 menit lagi.'],
            'otp-verify' => [10, 60, 'Terlalu banyak percobaan verifikasi OTP. Coba lagi 1 menit lagi.'],
            'review-submit' => [6, 60, 'Terlalu banyak kirim ulasan. Coba lagi sebentar.'],
            default => [20, 60, 'Terlalu banyak request. Coba lagi nanti.'],
        };
    }

    public function handle(Request $request, Closure $next, string $profile = 'default'): Response
    {
        [$maxAttempts, $decaySeconds, $message] = $this->profileFor($profile);

        $identity = implode('|', [
            $profile,
            (string) ($request->ip() ?? '0.0.0.0'),
            (string) ($request->user()?->id ?? 'guest'),
            (string) $request->path(),
        ]);

        $key = 'rate-limit:'.sha1($identity);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                ], 429);
            }

            return back()->withErrors(['rate_limit' => $message]);
        }

        RateLimiter::hit($key, $decaySeconds);

        return $next($request);
    }
}
