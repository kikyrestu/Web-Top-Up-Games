<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBootstrapControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_bootstraps_first_admin_with_valid_setup_key(): void
    {
        config()->set('app.admin_bootstrap_key', 'super-secret-bootstrap-key');

        $this->postJson('/api/v1/admin/bootstrap', [
            'name' => 'Bootstrap Admin',
            'email' => 'bootstrap-admin@example.com',
            'password' => 'secret12345',
            'setup_key' => 'super-secret-bootstrap-key',
        ])
            ->assertStatus(201)
            ->assertJsonPath('code', 'ADMIN_BOOTSTRAPPED');

        $this->assertTrue(
            User::query()
                ->where('email', 'bootstrap-admin@example.com')
                ->where('role', 'admin')
                ->exists()
        );
    }

    public function test_it_blocks_bootstrap_when_admin_already_exists(): void
    {
        User::query()->create([
            'name' => 'Existing Admin',
            'email' => 'existing-admin@example.com',
            'password' => bcrypt('secret12345'),
            'role' => 'admin',
        ]);

        config()->set('app.admin_bootstrap_key', 'super-secret-bootstrap-key');

        $this->postJson('/api/v1/admin/bootstrap', [
            'name' => 'Another Admin',
            'email' => 'another-admin@example.com',
            'password' => 'secret12345',
            'setup_key' => 'super-secret-bootstrap-key',
        ])
            ->assertStatus(409)
            ->assertJsonPath('code', 'ADMIN_ALREADY_EXISTS');
    }
}
