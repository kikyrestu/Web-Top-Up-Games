<?php

declare(strict_types=1);

namespace App\Domain\Security\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class TamperRiskService
{
    /**
     * @param array<string, mixed> $validated
     * @return array{score:int, level:string, reasons:array<int, string>}
     */
    public function evaluateCheckout(Request $request, array $validated): array
    {
        $score = 0;
        $reasons = [];

        $tamperFields = ['base_price', 'admin_fee', 'margin', 'final_amount', 'provider_id', 'selected_provider'];
        foreach ($tamperFields as $field) {
            if ($request->has($field)) {
                $score += 35;
                $reasons[] = 'suspicious_field:'.$field;
            }
        }

        $quantity = (int) ($validated['quantity'] ?? 1);
        if ($quantity >= 7) {
            $score += 12;
            $reasons[] = 'high_quantity';
        }

        $customerTarget = (string) ($validated['customer_target'] ?? '');
        if ($customerTarget !== '' && preg_match('/<script|onerror=|onload=|union\s+select|drop\s+table|\$\{/', mb_strtolower($customerTarget)) === 1) {
            $score += 45;
            $reasons[] = 'suspicious_target_payload';
        }

        $velocity = $this->trackVelocity((string) $request->ip());
        if ($velocity >= 6) {
            $score += 28;
            $reasons[] = 'high_checkout_velocity';
        } elseif ($velocity >= 4) {
            $score += 14;
            $reasons[] = 'moderate_checkout_velocity';
        }

        $currentUserAgent = trim((string) $request->userAgent());
        $previousUserAgent = trim((string) $request->session()->get('security.last_checkout_user_agent', ''));
        if ($previousUserAgent !== '' && $currentUserAgent !== '' && $previousUserAgent !== $currentUserAgent) {
            $score += 20;
            $reasons[] = 'user_agent_changed';
        }
        $request->session()->put('security.last_checkout_user_agent', $currentUserAgent);

        $level = 'LOW';
        if ($score >= 70) {
            $level = 'HIGH';
        } elseif ($score >= 35) {
            $level = 'MEDIUM';
        }

        return [
            'score' => min($score, 100),
            'level' => $level,
            'reasons' => $reasons,
        ];
    }

    public function getOrCreateChallenge(Request $request): string
    {
        $existingQuestion = (string) $request->session()->get('security.checkout_challenge.question', '');
        if ($existingQuestion !== '') {
            return $existingQuestion;
        }

        $a = random_int(2, 9);
        $b = random_int(1, 9);

        $request->session()->put('security.checkout_challenge.question', "Berapa hasil {$a} + {$b} ?");
        $request->session()->put('security.checkout_challenge.answer', (string) ($a + $b));

        return (string) $request->session()->get('security.checkout_challenge.question');
    }

    public function verifyChallenge(Request $request, string $answer): bool
    {
        $expected = trim((string) $request->session()->get('security.checkout_challenge.answer', ''));

        if ($expected === '' || $answer === '') {
            return false;
        }

        return hash_equals($expected, trim($answer));
    }

    public function clearChallenge(Request $request): void
    {
        $request->session()->forget('security.checkout_challenge');
    }

    private function trackVelocity(string $ip): int
    {
        $cacheKey = 'security:checkout_velocity:'.sha1($ip);

        Cache::add($cacheKey, 0, now()->addSeconds(60));

        return (int) Cache::increment($cacheKey);
    }
}
