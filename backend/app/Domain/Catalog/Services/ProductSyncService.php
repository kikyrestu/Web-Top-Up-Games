<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Services;

final class ProductSyncService
{
    /**
     * Sync catalog from all active providers.
     *
     * @return int Count of updated records.
     */
    public function syncAll(): int
    {
        // TODO: Implement provider fan-out sync and normalization.
        return 0;
    }
}
