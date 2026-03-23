<?php

namespace Tests\Feature;

use App\Models\IdempotencyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class IdempotencyPurgeCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_purges_only_expired_idempotency_records(): void
    {
        IdempotencyRequest::query()->create([
            'scope' => 'POST:api/v1/payments/initiate',
            'idempotency_key' => 'expired-1',
            'actor_fingerprint' => 'guest:1',
            'request_hash' => hash('sha256', 'expired-1'),
            'response_status' => 200,
            'response_body' => ['ok' => true],
            'expires_at' => now()->subHour(),
        ]);

        IdempotencyRequest::query()->create([
            'scope' => 'POST:api/v1/payments/initiate',
            'idempotency_key' => 'active-1',
            'actor_fingerprint' => 'guest:2',
            'request_hash' => hash('sha256', 'active-1'),
            'response_status' => 200,
            'response_body' => ['ok' => true],
            'expires_at' => now()->addHour(),
        ]);

        Artisan::call('idempotency:purge-expired');

        $this->assertSame(1, IdempotencyRequest::query()->count());
        $this->assertTrue(IdempotencyRequest::query()->where('idempotency_key', 'active-1')->exists());
        $this->assertFalse(IdempotencyRequest::query()->where('idempotency_key', 'expired-1')->exists());
    }
}
