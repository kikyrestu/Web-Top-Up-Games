<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTokenThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_token_endpoint_is_rate_limited(): void
    {
        User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin-throttle@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        $payload = [
            'email' => 'admin-throttle@example.com',
            'password' => 'secret123',
            'device_name' => 'throttle-test',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/token/login', $payload)->assertOk();
        }

        $this->postJson('/api/v1/auth/token/login', $payload)
            ->assertStatus(429);
    }
}
