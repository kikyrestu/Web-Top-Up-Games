<?php

declare(strict_types=1);

namespace App\Domain\Pricing\Services;

final class PricingEngineService
{
    /**
     * Rank provider candidates for top-up and PPOB products.
     *
     * @param array<int, array<string, mixed>> $candidates
     * @param string $productType
     * @return array<int, array<string, mixed>>
     */
    public function rankCandidates(array $candidates, string $productType): array
    {
        $normalizedType = strtoupper($productType);

        usort($candidates, static function (array $left, array $right) use ($normalizedType): int {
            $leftAdmin = (float) ($left['admin_fee'] ?? 0);
            $rightAdmin = (float) ($right['admin_fee'] ?? 0);
            $leftCommission = (float) ($left['commission'] ?? 0);
            $rightCommission = (float) ($right['commission'] ?? 0);
            $leftBase = (float) ($left['base_price'] ?? 0);
            $rightBase = (float) ($right['base_price'] ?? 0);

            if ($normalizedType === 'MULTIFINANCE') {
                // Rule 1: provider dengan admin 0 diprioritaskan.
                $leftIsZeroAdmin = $leftAdmin === 0.0;
                $rightIsZeroAdmin = $rightAdmin === 0.0;

                if ($leftIsZeroAdmin !== $rightIsZeroAdmin) {
                    return $leftIsZeroAdmin ? -1 : 1;
                }

                // Rule 2: jika admin sama, pilih komisi tertinggi.
                if ($leftAdmin === $rightAdmin && $leftCommission !== $rightCommission) {
                    return $leftCommission > $rightCommission ? -1 : 1;
                }

                if ($leftAdmin !== $rightAdmin) {
                    return $leftAdmin < $rightAdmin ? -1 : 1;
                }

                if ($leftBase !== $rightBase) {
                    return $leftBase < $rightBase ? -1 : 1;
                }

                return 0;
            }

            // Default top-up: harga modal total terendah diprioritaskan.
            $leftTotalCost = $leftBase + $leftAdmin;
            $rightTotalCost = $rightBase + $rightAdmin;

            if ($leftTotalCost !== $rightTotalCost) {
                return $leftTotalCost < $rightTotalCost ? -1 : 1;
            }

            if ($leftCommission !== $rightCommission) {
                return $leftCommission > $rightCommission ? -1 : 1;
            }

            return 0;
        });

        return $candidates;
    }
}
