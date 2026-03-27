<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\OtpProvider;
use App\Models\OtpVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure mock provider exists for testing
        OtpProvider::updateOrCreate(
            ['code' => 'mock'],
            ['name' => 'Mock SMS', 'type' => 'whatsapp', 'is_active' => true, 'is_default' => true]
        );
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_and_receive_otp(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'whatsapp' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'whatsapp' => '081234567890',
            'is_verified' => false,
        ]);

        $this->assertDatabaseHas('otp_verifications', [
            'target' => '081234567890',
            'type' => 'register',
            'is_verified' => false,
        ]);

        $response->assertRedirect('/verify-otp');
    }

    public function test_user_can_verify_otp_and_login(): void
    {
        // 1. Setup user
        $user = User::factory()->create([
            'email' => 'test_verify@example.com',
            'whatsapp' => '081234567891',
            'is_verified' => false,
        ]);

        // 2. Mock session state
        session(['otp_target' => $user->whatsapp, 'otp_type' => 'register']);

        // 3. Create active OTP
        $otp = OtpVerification::create([
            'target' => $user->whatsapp,
            'code' => '123456',
            'type' => 'register',
            'otp_provider_id' => OtpProvider::first()->id,
            'expires_at' => now()->addMinutes(5),
            'is_verified' => false,
            'attempts' => 0,
        ]);

        // 4. Verify
        $response = $this->post('/verify-otp', [
            'code' => '123456',
        ]);

        $response->assertRedirect(route('member.dashboard'));
        $this->assertAuthenticatedAs($user);
        
        $this->assertTrue($user->fresh()->is_verified, "User should be marked as verified.");
    }
}
