<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\PaymentGateway;
use App\Models\Setting;
use App\Services\Gateway\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'customer_email' => 'nullable|email',
            'customer_whatsapp' => 'required|string',
            'target_id' => 'required|string',
            'target_zone' => 'nullable|string',
            'product_id' => 'required|exists:products,id',
            'payment_gateway_id' => 'required|exists:payment_gateways,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::with('providerMappings.apiProvider')->findOrFail($request->product_id);
        $paymentGateway = PaymentGateway::findOrFail($request->payment_gateway_id);

        $selectedMapping = $product->resolveCheapestProviderMapping();
        if (! $selectedMapping) {
            return back()->with('error', 'Produk belum memiliki mapping provider aktif.');
        }

        $quantity = $request->quantity;
        $pricingMode = (string) Setting::get('pricing_mode', 'manual');
        $markupPercentage = (float) Setting::get('markup_percentage', 0);
        $unitCapital = (float) $selectedMapping->price_capital;

        $unitSellPrice = (float) $product->price_sell;
        if ($pricingMode === 'cheapest_auto') {
            $unitSellPrice = $unitCapital + (($unitCapital * $markupPercentage) / 100);
        }

        $subtotal = $unitSellPrice * $quantity;

        // Fee calculation: flat + percentage
        $feeFlat = (float) ($paymentGateway->fee_flat ?? 0);
        $feePct  = (float) ($paymentGateway->fee_percent ?? 0);
        $feeAmount = $feeFlat + (($subtotal * $feePct) / 100);
        $totalAmount = $subtotal + $feeAmount;

        $targetInput = $request->target_id;
        if ($request->filled('target_zone')) {
            $targetInput .= '(' . $request->target_zone . ')';
        }

        try {
            DB::beginTransaction();

            $invoiceNumber = 'INV-' . strtoupper(Str::random(10)) . time();

            $transaction = Transaction::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => auth()->id() ?? null,
                'customer_name' => $request->customer_email ?? 'Pelanggan', 
                'customer_contact' => $request->customer_whatsapp,
                'customer_email' => $request->customer_email,
                'customer_whatsapp' => $request->customer_whatsapp,
                'target_input' => $targetInput,
                'payment_gateway_id' => $paymentGateway->id,
                'subtotal' => $subtotal,
                'fee_amount' => $feeAmount,
                'total_amount' => $totalAmount,
                'payment_status' => 'unpaid',
                'transaction_status' => 'pending',
            ]);

            $commissionAmount = $product->calculateCommission($unitSellPrice);

            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'api_provider_id' => $selectedMapping->api_provider_id,
                'provider_product_code' => $selectedMapping->provider_product_code,
                'product_name' => $product->name,
                'price_capital' => $unitCapital,
                'price_sell' => $unitSellPrice,
                'quantity' => $quantity,
                'commission_amount' => $commissionAmount * $quantity,
            ]);

            // Dynamic Payment Gateway — resolve driver and create payment
            $gatewayDriver = PaymentGatewayFactory::resolve($paymentGateway);
            $credentials   = $paymentGateway->credentials ?? [];
            $pgResponse    = $gatewayDriver->createPayment($transaction, $credentials, $paymentGateway->is_test_mode);

            if ($pgResponse) {
                $transaction->update([
                    'api_response' => json_encode($pgResponse),
                    'payment_reference' => $pgResponse['reference'] ?? null,
                ]);
            } else {
                throw new \Exception('Gagal membuat transaksi di Payment Gateway (' . strtoupper($paymentGateway->code) . '). Pastikan konfigurasi API Keys sudah benar di Admin Panel.');
            }

            DB::commit();

            // Notify admin about new transaction
            try {
                \App\Services\NotificationService::notifyAdmin($transaction, 'new');
            } catch (\Exception $notifEx) {
                Log::warning('Notification dispatch failed: ' . $notifEx->getMessage());
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'invoice_number' => $transaction->invoice_number,
                    'redirect_url' => $pgResponse['checkout_url'] ?? route('transaction.show', $transaction->invoice_number),
                ]);
            }

            // Redirect to gateway checkout URL if available, otherwise invoice page
            $redirectUrl = $pgResponse['checkout_url'] ?? route('transaction.show', $transaction->invoice_number);
            return redirect($redirectUrl);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem. Silakan coba lagi beberapa saat.'
                ], 500);
            }
            
            return back()->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi beberapa saat.');
        }
    }

    public function showInvoice($invoiceNumber)
    {
        $transaction = Transaction::with(['items', 'paymentGateway'])->where('invoice_number', $invoiceNumber)->firstOrFail();
        
        $pgData = null;
        if ($transaction->api_response) {
            $pgData = json_decode($transaction->api_response);
        }

        return view('front.invoice', compact('transaction', 'pgData'));
    }

    /**
     * Generic webhook handler for any payment gateway.
     * Route: POST /webhook/pg/{gatewayCode}
     */
    public function handleWebhook(Request $request, string $gatewayCode)
    {
        $gateway = PaymentGateway::where('code', $gatewayCode)->where('is_active', true)->first();

        if (!$gateway) {
            return response()->json(['success' => false, 'message' => 'Gateway not found'], 404);
        }

        $driver      = PaymentGatewayFactory::resolve($gateway);
        $credentials = $gateway->credentials ?? [];
        $result      = $driver->handleWebhook($request, $credentials);

        if (($result['status'] ?? '') === 'invalid') {
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
        }

        $invoice = $result['invoice'] ?? null;
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Missing invoice reference'], 400);
        }

        $transaction = Transaction::where('invoice_number', $invoice)
            ->where('payment_status', 'unpaid')
            ->first();

        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaction not found or already processed'], 404);
        }

        $status = $result['status'] ?? 'pending';
        $reference = $result['reference'] ?? null;

        if ($status === 'paid') {
            $transaction->update([
                'payment_status' => 'paid',
                'payment_reference' => $reference,
            ]);

            // Notify admin
            try {
                \App\Services\NotificationService::notifyAdmin($transaction, 'paid');
            } catch (\Exception $e) {
                Log::warning('Notification failed (paid): ' . $e->getMessage());
            }

            // Trigger order to provider
            try {
                $item = $transaction->items()->first();
                if ($item && $item->api_provider_id) {
                    $provider = \App\Models\ApiProvider::find($item->api_provider_id);
                    if ($provider && \App\Services\Provider\ProviderSyncFactory::supportsSync($provider->code)) {
                        $service = \App\Services\Provider\ProviderSyncFactory::resolve($provider);
                        if (method_exists($service, 'createTransaction')) {
                            $service->createTransaction($transaction);
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::error('Provider order failed: ' . $e->getMessage());
            }

        } elseif ($status === 'failed') {
            $transaction->update([
                'payment_status' => 'failed',
                'payment_reference' => $reference,
            ]);

            try {
                \App\Services\NotificationService::notifyAdmin($transaction, 'failed');
            } catch (\Exception $e) {
                Log::warning('Notification failed (failed): ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true]);
    }
}