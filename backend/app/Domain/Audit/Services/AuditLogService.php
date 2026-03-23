<?php

declare(strict_types=1);

namespace App\Domain\Audit\Services;

use App\Models\AuditLog;
use Illuminate\Support\Arr;

final class AuditLogService
{
    /**
     * Write append-only audit event.
     *
     * @param array<string, mixed> $event
     */
    public function write(array $event): void
    {
        AuditLog::query()->create([
            'event_type' => (string) Arr::get($event, 'event_type', 'UNKNOWN_EVENT'),
            'actor_type' => Arr::get($event, 'actor_type'),
            'actor_id' => Arr::get($event, 'actor_id'),
            'entity_type' => Arr::get($event, 'entity_type'),
            'entity_id' => Arr::get($event, 'entity_id'),
            'request_id' => Arr::get($event, 'request_id'),
            'ip_address' => Arr::get($event, 'ip_address'),
            'user_agent' => Arr::get($event, 'user_agent'),
            'payload' => Arr::get($event, 'payload', []),
            'occurred_at' => Arr::get($event, 'occurred_at', now()),
        ]);
    }
}
