<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\PaymentGateway;
use App\Models\Setting;
use App\Services\Gateway\PaymentGatewayFactory;
use App\Services\ReferralService;
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
            'target_id' => 'nullable|string',
            'target_zone' => 'nullable|string',
            'product_id' => 'required|exists:products,id',
            'payment_method' => 'required|string', // can be 'wallet' or payment gateway ID
            'quantity' => 'required|integer|min:1',
            'inquiry_ref' => 'nullable|string|max:100',
            'inquiry_price' => 'nullable|numeric|min:0',
        ]);

        $product = Product::with('providerMappings.apiProvider')->findOrFail($request->product_id);
        
        $isWalletPayment = $request->payment_method === 'wallet';
        $paymentGateway = null;
        
        if (!$isWalletPayment) {
            $paymentGateway = PaymentGateway::findOrFail($request->payment_method);
        } else if (!auth()->check()) {
            return back()->with('error', 'Silakan login terlebih dahulu untuk membayar menggunakan Saldo.');
        }

        $selectedMapping = $product->resolveCheapestProviderMapping();
        if (! $selectedMapping) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Produk "' . $product->name . '" belum memiliki provider aktif. Silakan hubungi admin.'], 422);
            }
            return back()->with('error', 'Produk belum memiliki mapping provider aktif.');
        }

        $quantity = $request->quantity;
        $pricingMode = (string) Setting::get('pricing_mode', 'manual');
        $unitCapital = (float) $selectedMapping->price_capital;

        $unitSellPrice = (float) $product->price_sell;
        if ($pricingMode === 'cheapest_auto') {
            $unitSellPrice = $unitCapital + $product->calculateMarkup($unitCapital);
        }

        // For postpaid, override price with inquiry result
        $inquiryRef = $request->input('inquiry_ref');
        if ($inquiryRef && $request->filled('inquiry_price') && (float) $request->inquiry_price > 0) {
            $unitSellPrice = (float) $request->inquiry_price;
            $unitCapital = $unitSellPrice; // For postpaid, capital = selling price (no markup on bill amount)
        }

        $subtotal = $unitSellPrice * $quantity;

        // Fee calculation: flat + percentage
        $feeFlat = $isWalletPayment ? 0 : (float) ($paymentGateway->fee_flat ?? 0);
        $feePct  = $isWalletPayment ? 0 : (float) ($paymentGateway->fee_percent ?? 0);
        $feeAmount = $feeFlat + (($subtotal * $feePct) / 100);
        $totalAmount = $subtotal + $feeAmount;
        
        if ($isWalletPayment && auth()->user()->wallet_balance < $totalAmount) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Saldo tidak mencukupi. Saldo kamu: Rp ' . number_format(auth()->user()->wallet_balance, 0, ',', '.') . ', Total tagihan: Rp ' . number_format($totalAmount, 0, ',', '.') . '.'], 422);
            }
            return back()->with('error', 'Saldo tidak mencukupi untuk transaksi ini.');
        }

        $targetInput = $request->target_id ?: 'VOUCHER';
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
                'payment_gateway_id' => $isWalletPayment ? null : $paymentGateway->id,
                'subtotal' => $subtotal,
                'fee_amount' => $feeAmount,
                'total_amount' => $totalAmount,
                'payment_status' => $isWalletPayment ? 'paid' : 'unpaid',
                'transaction_status' => 'pending',
                'inquiry_ref' => $inquiryRef,
            ]);

            $markup = $product->calculateMarkup($unitCapital);
            $commissionAmount = $inquiryRef ? 0 : $markup;

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

            if ($isWalletPayment) {
                // Deduct from wallet immediately
                $walletService = new \App\Services\WalletService();
                $deducted = $walletService->deduct(auth()->user(), $totalAmount, $invoiceNumber, 'Pembayaran Transaksi ' . $invoiceNumber);
                
                if (!$deducted) {
                    throw new \Exception('Gagal memotong saldo wallet.');
                }
                
                // Set pg response custom for wallet
                $transaction->update([
                    'api_response' => json_encode(['method' => 'wallet', 'status' => 'paid']),
                ]);
            } else {
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
                    $errDetail = '';
                    if (isset($pgResponse) && is_array($pgResponse)) {
                        $errDetail = ' Detail: ' . ($pgResponse['message'] ?? json_encode($pgResponse));
                    }
                    throw new \Exception('Gagal membuat transaksi di Payment Gateway (' . strtoupper($paymentGateway->code) . ').' . $errDetail . ' Coba beberapa saat lagi atau pilih metode pembayaran lain.');
                }
            }

            DB::commit();

            // Notify admin about new transaction
            try {
                \App\Services\NotificationService::notifyAdmin($transaction, 'new');
            } catch (\Exception $notifEx) {
                Log::warning('Notification dispatch failed: ' . $notifEx->getMessage());
            }

            // Immediately trigger order to provider if paid with wallet
            if ($isWalletPayment) {
                try {
                    \App\Jobs\ProcessProviderOrder::dispatch($transaction);
                } catch (\Exception $e) {
                    Log::error('Provider order dispatch failed (wallet payment): ' . $e->getMessage());
                }

                // Process commission for wallet-paid transactions
                try {
                    app(ReferralService::class)->processTransactionCommission($transaction);
                } catch (\Exception $e) {
                    Log::warning('Commission processing failed (wallet): ' . $e->getMessage());
                }

                // Customer email will be sent after provider confirms success (via ProviderWebhookController)
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'invoice_number' => $transaction->invoice_number,
                    'redirect_url' => $isWalletPayment ? route('transaction.show', $transaction->invoice_number) : ($pgResponse['checkout_url'] ?? route('transaction.show', $transaction->invoice_number)),
                ]);
            }

            $redirectUrl = $isWalletPayment ? route('transaction.show', $transaction->invoice_number) : ($pgResponse['checkout_url'] ?? route('transaction.show', $transaction->invoice_number));
            return redirect($redirectUrl);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return back()->with('error', $e->getMessage());
        }
    }

    public function showInvoice($invoiceNumber)
    {
        $transaction = Transaction::with(['items', 'paymentGateway'])->where('invoice_number', $invoiceNumber)->firstOrFail();
        
        $pgData = null;
        if ($transaction->api_response) {
            $pgData = json_decode($transaction->api_response);
        }

        // Review eligibility: check if logged-in user already reviewed this product
        $canReview = false;
        $productId = null;
        $productName = null;
        if (auth()->check() && $transaction->user_id === auth()->id() && $transaction->transaction_status === 'success') {
            $firstItem = $transaction->items->first();
            if ($firstItem && $firstItem->product_id) {
                $productId = $firstItem->product_id;
                $productName = $firstItem->product_name ?? 'Produk';
                $canReview = !\App\Models\ProductReview::where('user_id', auth()->id())
                    ->where('product_id', $productId)
                    ->exists();
            }
        }

        return view('front.invoice', compact('transaction', 'pgData', 'canReview', 'productId', 'productName'));
    }

    public function showReceipt($invoiceNumber)
    {
        $transaction = Transaction::with(['items', 'paymentGateway'])->where('invoice_number', $invoiceNumber)->firstOrFail();

        return view('front.receipt', compact('transaction'));
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

            // Is it a wallet top-up?
            if (str_starts_with($transaction->invoice_number, 'TOPUP-') && $transaction->user_id) {
                $transaction->update(['transaction_status' => 'success']);
                
                $walletService = new \App\Services\WalletService();
                $walletService->topUp(
                    \App\Models\User::find($transaction->user_id), 
                    $transaction->subtotal, 
                    $transaction->invoice_number, 
                    'Top Up Saldo ' . $gateway->name
                );
            } else {
                // Regular transaction: Notify admin
                try {
                    \App\Services\NotificationService::notifyAdmin($transaction, 'paid');
                } catch (\Exception $e) {
                    Log::warning('Notification failed (paid): ' . $e->getMessage());
                }

                // Trigger order to provider via Background Job
                try {
                    \App\Jobs\ProcessProviderOrder::dispatch($transaction);
                } catch (\Exception $e) {
                    Log::error('Provider order dispatch failed: ' . $e->getMessage());
                }

                // Process commission for payment-gateway-paid transactions
                try {
                    app(ReferralService::class)->processTransactionCommission($transaction);
                } catch (\Exception $e) {
                    Log::warning('Commission processing failed (pg): ' . $e->getMessage());
                }

                // Customer email will be sent after provider confirms success (via ProviderWebhookController)
            }

        } elseif ($status === 'failed') {
            $transaction->update([
                'payment_status' => 'failed',
                'payment_reference' => $reference,
            ]);

            if (!str_starts_with($transaction->invoice_number, 'TOPUP-')) {
                try {
                    \App\Services\NotificationService::notifyAdmin($transaction, 'failed');
                } catch (\Exception $e) {
                    Log::warning('Notification failed (failed): ' . $e->getMessage());
                }
            }
        }

        return response()->json(['success' => true]);
    }
}