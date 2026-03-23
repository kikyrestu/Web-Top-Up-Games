<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthTokenControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_issues_api_token_for_valid_credentials(): void
    {
        User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        $this->postJson('/api/v1/auth/token/login', [
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'device_name' => 'postman',
        ])
            ->assertOk()
            ->assertJsonPath('code', 'TOKEN_ISSUED')
            ->assertJsonStructure([
                'data' => ['token', 'token_type', 'user'],
            ]);
    }

    public function test_it_returns_authenticated_user_via_auth_me(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('code', 'AUTH_ME')
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.role', 'admin');
    }

    public function test_it_revokes_all_tokens_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'role' => 'admin',
        ]);

        $user->createToken('device-a');
        $user->createToken('device-b');

        Sanctum::actingAs($user);

        $this->postJson('/api/v1/auth/token/revoke-all')
            ->assertOk()
            ->assertJsonPath('code', 'TOKEN_ALL_REVOKED');

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_it_writes_audit_log_for_failed_login(): void
    {
        User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin-fail@example.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        $this->postJson('/api/v1/auth/token/login', [
            'email' => 'admin-fail@example.com',
            'password' => 'wrong-password',
            'device_name' => 'postman',
        ])->assertStatus(401);

        $this->assertTrue(
            AuditLog::query()->where('event_type', 'AUTH_LOGIN_FAILED')->exists()
        );
    }
}
