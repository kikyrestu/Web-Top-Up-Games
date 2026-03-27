<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\PaymentGateway;

class GuestSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_transactions_are_synced_on_otp_verify()
    {
        // Setup mock gateway for Transaction creation
        $gateway = PaymentGateway::create([
            'name' => 'Gateway',
            'code' => 'mock_gw',
            'type' => 'ewallet',
            'is_active' => true,
        ]);

        // 1. Create a guest transaction (user_id is null)
        $transaction = Transaction::create([
            'invoice_number' => 'INV-GUEST-12345',
            'user_id' => null,
            'customer_name' => 'Guest User',
            'customer_contact' => '081234567890',
            'target_input' => 'user123',
            'payment_gateway_id' => $gateway->id,
            'subtotal' => 10000,
            'fee_amount' => 500,
            'total_amount' => 10500,
        ]);

        $this->assertNull($transaction->user_id);

        // 2. Register/Create user with the identical contact
        $user = User::factory()->create([
            'whatsapp' => '081234567890',
            'is_verified' => false,
            'email' => 'guest@example.com'
        ]);

        // Put session for OTP verification
        Session::put('otp_target', '081234567890');
        Session::put('otp_type', 'register');

        // Mock OtpService to bypass real verification
        $this->mock(\App\Services\Otp\OtpService::class, function ($mock) {
            $mock->shouldReceive('verifyOtp')
                 ->with('081234567890', '123456', 'register')
                 ->once()
                 ->andReturn(['success' => true]);
        });

        // 3. Hit the OTP Verify endpoint
        $response = $this->post('/verify-otp', [
            'code' => '123456',
        ]);

        $response->assertRedirect(route('member.dashboard'));
        $this->assertAuthenticatedAs($user);

        // 4. Assert transaction is now linked
        $transaction->refresh();
        $this->assertEquals($user->id, $transaction->user_id);
    }
}
