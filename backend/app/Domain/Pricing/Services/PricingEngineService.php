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
        // TODO: Apply admin-zero and highest-commission rule for multifinance.
        return $candidates;
    }
}
