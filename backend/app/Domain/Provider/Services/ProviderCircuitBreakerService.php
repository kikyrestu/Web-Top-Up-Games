<?php

declare(strict_types=1);

namespace App\Domain\Provider\Services;

use App\Models\ProviderHealthCheck;

final class ProviderCircuitBreakerService
{
    public function isProviderBlocked(int $providerId): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $latestOpen = ProviderHealthCheck::query()
            ->where('provider_id', $providerId)
            ->where('status', 'OPEN')
            ->latest('checked_at')
            ->first();

        if ($latestOpen === null || $latestOpen->checked_at === null) {
            return false;
        }

        $cooldownSeconds = max(1, (int) config('services.provider_router.circuit_breaker_cooldown_seconds', 120));

        return now()->diffInSeconds($latestOpen->checked_at) < $cooldownSeconds;
    }

    public function recordAttempt(int $providerId, string $attemptStatus): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $isFailed = in_array(strtoupper($attemptStatus), ['FAILED', 'ERROR'], true);

        ProviderHealthCheck::query()->create([
            'provider_id' => $providerId,
            'status' => $isFailed ? 'FAIL' : 'SUCCESS',
            'checked_at' => now(),
            'meta' => [
                'attempt_status' => strtoupper($attemptStatus),
            ],
        ]);

        if (!$isFailed) {
            return;
        }

        $threshold = max(1, (int) config('services.provider_router.circuit_breaker_failure_threshold', 3));

        $recentStatuses = ProviderHealthCheck::query()
            ->where('provider_id', $providerId)
            ->whereIn('status', ['FAIL', 'SUCCESS'])
            ->latest('checked_at')
            ->limit($threshold)
            ->pluck('status')
            ->all();

        if (count($recentStatuses) < $threshold) {
            return;
        }

        foreach ($recentStatuses as $status) {
            if ($status !== 'FAIL') {
                return;
            }
        }

        ProviderHealthCheck::query()->create([
            'provider_id' => $providerId,
            'status' => 'OPEN',
            'checked_at' => now(),
            'meta' => [
                'reason' => 'consecutive_failures',
                'failure_threshold' => $threshold,
                'cooldown_seconds' => max(1, (int) config('services.provider_router.circuit_breaker_cooldown_seconds', 120)),
            ],
        ]);
    }

    private function isEnabled(): bool
    {
        return (bool) config('services.provider_router.circuit_breaker_enabled', true);
    }
}
