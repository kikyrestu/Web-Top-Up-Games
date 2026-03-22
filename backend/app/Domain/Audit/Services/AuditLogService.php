<?php

declare(strict_types=1);

namespace App\Domain\Audit\Services;

final class AuditLogService
{
    /**
     * Write append-only audit event.
     *
     * @param array<string, mixed> $event
     */
    public function write(array $event): void
    {
        // TODO: Persist audit events and redact sensitive fields.
    }
}
