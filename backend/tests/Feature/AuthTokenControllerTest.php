<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
}
