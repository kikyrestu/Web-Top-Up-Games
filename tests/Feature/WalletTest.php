<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_wallet_page()
    {
        $user = User::factory()->create(['is_verified' => true]);
        
        $response = $this->actingAs($user)->get(route('member.wallet'));
        $response->assertStatus(200);
    }

    public function test_user_can_initiate_wallet_top_up()
    {
        $user = User::factory()->create(['is_verified' => true]);
        
        // Register mock gateway
        $mockDriver = new class implements \App\Services\Gateway\PaymentGatewayInterface {
            public function createPayment(\App\Models\Transaction $transaction, array $credentials, bool $isTestMode = false): array|false {
                return ['reference' => 'mock-ref-123', 'checkout_url' => 'http://mock.test', 'status' => 'pending'];
            }
            public function handleWebhook(\Illuminate\Http\Request $request, array $credentials): array {
                return [];
            }
            public static function requiredCredentials(): array {
                return [];
            }
        };
        
        \App\Services\Gateway\PaymentGatewayFactory::extend('mock', get_class($mockDriver));

        // Mock a gateway model
        $gateway = PaymentGateway::create([
            'name' => 'Mock Gateway',
            'code' => 'mock',
            'type' => 'virtual_account',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('member.wallet.topup'), [
            'amount' => 50000,
            'payment_gateway_id' => $gateway->id,
        ]);

        $transaction = Transaction::where('user_id', $user->id)->first();
        
        $this->assertNotNull($transaction);
        $this->assertEquals(50000, $transaction->subtotal);
        $this->assertEquals('pending', $transaction->transaction_status);
        $this->assertTrue(str_contains($transaction->target_input, '(Top Up)'));
        
        $response->assertRedirect(route('transaction.show', $transaction->invoice_number));
    }
}
