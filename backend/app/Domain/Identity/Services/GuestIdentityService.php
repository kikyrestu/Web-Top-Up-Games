<?php

declare(strict_types=1);

namespace App\Domain\Identity\Services;

final class GuestIdentityService
{
    /**
     * Link guest transactions to authenticated user account.
     */
    public function linkToUser(int $userId, string $identityKey): int
    {
        // TODO: Implement guest-to-user transaction linking.
        return 0;
    }
}
