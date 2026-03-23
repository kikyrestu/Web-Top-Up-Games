<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

final class GlobalRateLimit
{
    private static ?bool $hasMetricsTable = null;

    /**
     * @return array<int, string>
     */
    public static function monitoredProfiles(): array
    {
        return ['otp-login', 'otp-verify', 'review-submit'];
    }

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

        $this->incrementMetric($profile, 'hits');

        $identity = implode('|', [
            $profile,
            (string) ($request->ip() ?? '0.0.0.0'),
            (string) ($request->user()?->id ?? 'guest'),
            (string) $request->path(),
        ]);

        $key = 'rate-limit:'.sha1($identity);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $this->incrementMetric($profile, 'blocked');

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

    private function incrementMetric(string $profile, string $type): void
    {
        $hour = now()->format('YmdH');
        $key = 'rate_limit_metric:'.$profile.':'.$type.':'.$hour;

        if (!Cache::has($key)) {
            Cache::put($key, 0, now()->addHours(48));
        }

        Cache::increment($key);

        $this->incrementDatabaseMetric($profile, $type);
    }

    private function incrementDatabaseMetric(string $profile, string $type): void
    {
        if (!$this->hasMetricsTable()) {
            return;
        }

        $hourBucket = now()->copy()->startOfHour()->format('Y-m-d H:i:s');

        try {
            DB::table('rate_limit_metrics')->upsert([
                [
                    'profile' => $profile,
                    'hour_bucket' => $hourBucket,
                    'hits' => $type === 'hits' ? 1 : 0,
                    'blocked' => $type === 'blocked' ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ], ['profile', 'hour_bucket'], [
                'hits' => DB::raw('rate_limit_metrics.hits + '.($type === 'hits' ? '1' : '0')),
                'blocked' => DB::raw('rate_limit_metrics.blocked + '.($type === 'blocked' ? '1' : '0')),
                'updated_at' => now(),
            ]);
        } catch (QueryException) {
            self::$hasMetricsTable = false;
        }
    }

    private function hasMetricsTable(): bool
    {
        if (self::$hasMetricsTable !== null) {
            return self::$hasMetricsTable;
        }

        self::$hasMetricsTable = Schema::hasTable('rate_limit_metrics');

        return self::$hasMetricsTable;
    }
}
