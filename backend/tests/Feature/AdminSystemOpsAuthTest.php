<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminSystemOpsAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard_overview(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/dashboard/overview')
            ->assertOk()
            ->assertJsonPath('code', 'ADMIN_DASHBOARD_OVERVIEW');
    }

    public function test_non_admin_is_forbidden_from_dashboard_overview(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/admin/dashboard/overview')
            ->assertStatus(403)
            ->assertJsonPath('code', 'FORBIDDEN');
    }
}
