<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Services\Gateway\PaymentGatewayFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $transactions = $user->walletTransactions()->latest()->paginate(10);
        $walletLabel = Setting::get('wallet_label', 'Saldo');
        
        $paymentGateways = PaymentGateway::where('is_active', true)->get();

        return view('member.wallet', compact('user', 'transactions', 'walletLabel', 'paymentGateways'));
    }

    public function topUp(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
        ]);

        $user = auth()->user();
        $amount = $request->amount;
        $paymentGateway = PaymentGateway::findOrFail($request->payment_gateway_id);

        // Calculate fees
        $feeFlat = (float) ($paymentGateway->fee_flat ?? 0);
        $feePct  = (float) ($paymentGateway->fee_percent ?? 0);
        $feeAmount = $feeFlat + (($amount * $feePct) / 100);
        $totalAmount = $amount + $feeAmount;

        try {
            DB::beginTransaction();

            $invoiceNumber = 'TOPUP-' . strtoupper(Str::random(10)) . time();

            // Store Topup as a Transaction but targeting pseudo 'Wallet Topup'
            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $user->id,
                'customer_name' => $user->name,
                'customer_contact' => $user->whatsapp ?? $user->phone ?? $user->email,
                'target_input' => $user->id . ' (Top Up)',
                'payment_gateway_id' => $paymentGateway->id,
                'subtotal' => $amount,
                'fee_amount' => $feeAmount,
                'total_amount' => $totalAmount,
                'payment_status' => 'unpaid',
                'transaction_status' => 'pending', 
                // Using transaction_status 'pending' to identify it's not yet applied to wallet
            ]);

            $gatewayDriver = PaymentGatewayFactory::resolve($paymentGateway);
            $credentials   = $paymentGateway->credentials ?? [];
            $pgResponse    = $gatewayDriver->createPayment($transaction, $credentials, $paymentGateway->is_test_mode);

            if ($pgResponse) {
                $transaction->update([
                    'api_response' => json_encode($pgResponse),
                    'payment_reference' => $pgResponse['reference'] ?? null,
                ]);
            } else {
                throw new \Exception('Gagal membuat URL pembayaran Top Up.');
            }

            DB::commit();
            
            // Redirect to generic transaction invoice page to pay
            return redirect()->route('transaction.show', $transaction->invoice_number);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Topup Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses top-up. Coba lagi.');
        }
    }
}
