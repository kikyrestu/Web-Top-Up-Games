<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\GuestSession;

class GuestSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_initialize_session()
    {
        $response = $this->postJson('/api/v1/guest/session/init', [
            'fingerprint' => 'browser_md5_hash_123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'identity_key',
                         'expires_at'
                     ]
                 ]);

        $this->assertDatabaseHas('guest_sessions', [
            'fingerprint' => 'browser_md5_hash_123',
        ]);
        
        // Assert identity_key starts with gks_
        $this->assertStringStartsWith('gks_', $response->json('data.identity_key'));
    }

    public function test_guest_init_returns_existing_session_for_same_fingerprint()
    {
        // First request
        $response1 = $this->postJson('/api/v1/guest/session/init', [
            'fingerprint' => 'browser_md5_hash_123',
        ]);
        $identityKey1 = $response1->json('data.identity_key');

        // Second request
        $response2 = $this->postJson('/api/v1/guest/session/init', [
            'fingerprint' => 'browser_md5_hash_123',
        ]);
        $identityKey2 = $response2->json('data.identity_key');

        // Assert they are exactly the same
        $this->assertEquals($identityKey1, $identityKey2);
        
        // Assert only one record exists
        $this->assertDatabaseCount('guest_sessions', 1);
    }
}
