<?php

namespace App\Services;

use App\Models\Transaction;
use App\Services\Provider\ProviderSyncInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderKuotaService implements ProviderSyncInterface
{
    const CENTER_URL    = 'https://h2h.okeconnect.com/trx/';
    const CENTER_BACKUP = 'https://b2b.okeconnect.com/trx/';

    /**
     * Build query params for auth (tanpa sign — using pin+password).
     */
    protected function authParams(array $credentials): array
    {
        return [
            'memberID' => $credentials['member_id'] ?? '',
            'pin'      => $credentials['pin'] ?? '',
            'password'  => $credentials['password'] ?? '',
        ];
    }

    /**
     * Get center URL from credentials or default.
     */
    protected function getBaseUrl(array $credentials): string
    {
        return rtrim($credentials['url'] ?? self::CENTER_URL, '/') . '/';
    }

    /**
     * Send HTTP GET request with retry on backup center.
     */
    protected function sendRequest(array $credentials, string $endpoint, array $params = []): ?string
    {
        $baseUrl = $this->getBaseUrl($credentials);

        // Try primary center
        try {
            $response = Http::timeout(30)->withOptions(['force_ip_resolve' => 'v4'])->get($baseUrl . $endpoint, $params);
            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Exception $e) {
            Log::warning("OrderKuota primary center failed: {$e->getMessage()}");
        }

        // Try backup center
        try {
            $backupUrl = rtrim($credentials['backup_url'] ?? self::CENTER_BACKUP, '/') . '/';
            $response = Http::timeout(30)->withOptions(['force_ip_resolve' => 'v4'])->get($backupUrl . $endpoint, $params);
            if ($response->successful()) {
                return $response->body();
            }
        } catch (\Exception $e) {
            Log::warning("OrderKuota backup center failed: {$e->getMessage()}");
        }

        return null;
    }

    /**
     * Check balance / saldo.
     */
    public function cekSaldo(array $credentials): array
    {
        $params = $this->authParams($credentials);
        $body = $this->sendRequest($credentials, 'balance', $params);

        if ($body === null) {
            return ['success' => false, 'saldo' => 0, 'message' => 'Connection failed'];
        }

        // Response: "Yth. Okeconnect H2H (OK00123). Saldo 150.094.085! ..."
        if (preg_match('/Saldo\s+([\d.,]+)/i', $body, $m)) {
            $saldo = (float) str_replace(['.', ','], ['', '.'], $m[1]);
            return ['success' => true, 'saldo' => $saldo, 'raw' => $body];
        }

        return ['success' => false, 'saldo' => 0, 'message' => $body];
    }

    /**
     * Execute a transaction (top-up / payment).
     */
    public function createTransaction(Transaction $transaction)
    {
        if (!$transaction->relationLoaded('items')) {
            $transaction->load('items.apiProvider');
        }

        $item = $transaction->items->first();
        if (!$item) return false;

        $credentials = [];
        if ($item->apiProvider) {
            $credentials = $item->apiProvider->credentials ?? [];
        }

        $productCode = $item->provider_product_code ?: ($item->product->provider_product_code ?? '');
        if (!$productCode) {
            Log::warning('OrderKuota: No product code for Trx ' . $transaction->invoice_number);
            return false;
        }

        $dest  = $transaction->target_input;
        $refId = $transaction->invoice_number;

        $params = array_merge($this->authParams($credentials), [
            'product' => $productCode,
            'dest'    => $dest,
            'refID'   => $refId,
        ]);

        $body = $this->sendRequest($credentials, 'trx', $params);

        Log::info('OrderKuota Transaction Response', [
            'invoice'  => $refId,
            'product'  => $productCode,
            'response' => $body,
        ]);

        if ($body === null) {
            $transaction->update([
                'transaction_status' => 'failed',
                'api_response'       => 'Connection failed to OrderKuota',
            ]);
            return false;
        }

        $parsed = $this->parseTransactionResponse($body);

        $updateData = [
            'transaction_status' => $parsed['status'],
            'api_response'       => $body,
        ];

        if (!empty($parsed['sn'])) {
            $updateData['provider_reference'] = $parsed['sn'];
        }
        if (!empty($parsed['trx_id'])) {
            $updateData['provider_reference'] = $parsed['trx_id'] . (!empty($parsed['sn']) ? '|SN:' . $parsed['sn'] : '');
        }

        $transaction->update($updateData);

        return $parsed;
    }

    /**
     * Check transaction status.
     */
    public function checkStatus(Transaction $transaction): string
    {
        if (!$transaction->relationLoaded('items')) {
            $transaction->load('items.apiProvider');
        }

        $item = $transaction->items->first();
        if (!$item) return $transaction->transaction_status;

        $credentials = [];
        if ($item->apiProvider) {
            $credentials = $item->apiProvider->credentials ?? [];
        }

        $productCode = $item->provider_product_code ?: ($item->product->provider_product_code ?? '');
        $dest  = $transaction->target_input;
        $refId = $transaction->invoice_number;

        $params = array_merge($this->authParams($credentials), [
            'product' => $productCode,
            'dest'    => $dest,
            'refID'   => $refId,
            'check'   => '1',
        ]);

        $body = $this->sendRequest($credentials, 'trx', $params);

        if ($body === null) {
            return $transaction->transaction_status;
        }

        $parsed = $this->parseTransactionResponse($body);

        if ($parsed['status'] !== $transaction->transaction_status) {
            $updateData = ['transaction_status' => $parsed['status']];
            if (!empty($parsed['sn'])) {
                $updateData['provider_reference'] = $parsed['sn'];
            }
            $updateData['api_response'] = $body;
            $transaction->update($updateData);
        }

        return $parsed['status'];
    }

    /**
     * Parse OkeConnect text response into structured data.
     *
     * Response examples:
     *   "T#41168891 R#1234 Telkomsel 5.000 S5.082280004280 SUKSES. SN/Ref: R210630.2203.210045. Saldo ..."
     *   "T#41168891 R#1234 S5.082280004280 akan diproses. Saldo ..."
     *   "T#41169572 R#1235 Telkomsel 5.000 S5.082280004280 GAGAL. Nomor tujuan salah. Saldo ..."
     */
    protected function parseTransactionResponse(string $body): array
    {
        $result = [
            'status'  => 'processing',
            'sn'      => null,
            'trx_id'  => null,
            'saldo'   => null,
            'message' => $body,
        ];

        // Extract T# (transaction ID from OkeConnect)
        if (preg_match('/T#(\d+)/i', $body, $m)) {
            $result['trx_id'] = $m[1];
        }

        // Determine status from keywords
        $bodyUpper = strtoupper($body);
        if (str_contains($bodyUpper, 'SUKSES')) {
            $result['status'] = 'success';
        } elseif (str_contains($bodyUpper, 'GAGAL')) {
            $result['status'] = 'failed';
        } elseif (str_contains($bodyUpper, 'AKAN DIPROSES') || str_contains($bodyUpper, 'MENUNGGU') || str_contains($bodyUpper, 'PROSES')) {
            $result['status'] = 'processing';
        }

        // Extract SN/Ref
        if (preg_match('/SN\/Ref:\s*(\S+)/i', $body, $m)) {
            $result['sn'] = rtrim($m[1], '.');
        } elseif (preg_match('/SN:\s*(\S+)/i', $body, $m)) {
            $result['sn'] = rtrim($m[1], '.');
        }

        // Extract saldo
        if (preg_match('/Saldo\s+([\d.,]+)/i', $body, $m)) {
            $result['saldo'] = (float) str_replace(['.', ','], ['', '.'], $m[1]);
        }

        return $result;
    }

    /**
     * Parse callback message from OkeConnect webhook (public wrapper).
     */
    public function parseCallbackMessage(string $body): array
    {
        return $this->parseTransactionResponse($body);
    }

    /**
     * Inquiry (cek tagihan) — for postpaid products.
     * OkeConnect uses separate product codes: B-prefix for payment, C-prefix for inquiry.
     * E.g. BPAM128 → CPAM128, BPLA → CPLA, BHOME → CHOME
     */
    public function inquiry(array $credentials, string $productCode, string $dest): array
    {
        // Convert B-prefix (bayar) to C-prefix (cek) for inquiry
        $cekCode = $productCode;
        if (preg_match('/^B[A-Z]/', $productCode)) {
            $cekCode = 'C' . substr($productCode, 1);
        }

        $refId = 'INQ-' . strtoupper(\Illuminate\Support\Str::random(8)) . time();

        $params = array_merge($this->authParams($credentials), [
            'product' => $cekCode,
            'dest'    => $dest,
            'refID'   => $refId,
        ]);

        $body = $this->sendRequest($credentials, 'trx', $params);

        Log::info('OrderKuota Inquiry', [
            'product'      => $productCode,
            'cek_code'     => $cekCode,
            'dest'         => $dest,
            'raw_response' => $body,
        ]);

        if ($body === null) {
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke server OrderKuota.',
            ];
        }

        return $this->parseInquiryResponse($body, $refId, $dest);
    }

    /**
     * Parse inquiry response from OkeConnect.
     * 
     * Possible formats:
     *   Success: "T#12345 R#refID CPAM128.1234567890 SUKSES. Nama: JOHN DOE. Tagihan: Rp 150.000. Admin: Rp 2.500. Total: Rp 152.500. Saldo ..."
     *   Also: "... Nama: JOHN - Pemakaian: 20M3 - Tagihan: Rp.50.000 - Admin: Rp.2.500 - ..."
     *   Failed: "T#12345 R#refID CPAM128.1234567890 GAGAL. Nomor tujuan salah. Saldo ..."
     */
    protected function parseInquiryResponse(string $body, string $refId, string $dest): array
    {
        $bodyUpper = strtoupper($body);
        $success   = str_contains($bodyUpper, 'SUKSES');
        $failed    = str_contains($bodyUpper, 'GAGAL');

        // Extract customer name
        $customerName = '';
        if (preg_match('/Nama[:\s]+([^.\-\n]+)/i', $body, $m)) {
            $customerName = trim($m[1]);
        }

        // Extract tagihan (bill amount)
        $tagihan = 0;
        if (preg_match('/Tagihan[:\s]*(?:Rp\.?\s*)([\d.,]+)/i', $body, $m)) {
            $tagihan = (int) str_replace(['.', ','], ['', ''], $m[1]);
        }

        // Extract admin fee
        $admin = 0;
        if (preg_match('/Admin[:\s]*(?:Rp\.?\s*)([\d.,]+)/i', $body, $m)) {
            $admin = (int) str_replace(['.', ','], ['', ''], $m[1]);
        }

        // Extract total
        $total = 0;
        if (preg_match('/Total[:\s]*(?:Rp\.?\s*)([\d.,]+)/i', $body, $m)) {
            $total = (int) str_replace(['.', ','], ['', ''], $m[1]);
        }
        if ($total === 0 && ($tagihan + $admin) > 0) {
            $total = $tagihan + $admin;
        }

        // Extract periode
        $periode = '';
        if (preg_match('/Periode[:\s]+([^.\-\n]+)/i', $body, $m)) {
            $periode = trim($m[1]);
        }

        // Build status message
        $message = $body;
        if ($failed) {
            // Extract error reason after GAGAL
            if (preg_match('/GAGAL\.?\s*(.+?)(?:\.?\s*Saldo|\s*$)/i', $body, $m)) {
                $message = trim($m[1]);
            }
        }

        return [
            'success'       => $success,
            'ref_id'        => $refId,
            'customer_no'   => $dest,
            'customer_name' => $customerName,
            'price'         => $total > 0 ? $total : $tagihan,
            'admin'         => $admin,
            'tagihan'       => $tagihan,
            'periode'       => $periode,
            'message'       => $success ? 'Tagihan ditemukan' : $message,
            'status'        => $success ? 'Sukses' : 'Gagal',
            'provider'      => 'orderkuota',
            'raw'           => $body,
        ];
    }

    /**
     * Get price list — OkeConnect doesn't have a product list API endpoint.
     * Products must be added manually or scraped from the dashboard.
     * This returns an empty array; use manual product mapping in admin.
     */
    public function getPriceList(array $credentials, array $options = []): array
    {
        // OkeConnect H2H (OrderKuota) does not provide a product list API.
        // Products use codes like S5, S10, T5, T10, SM20, XL10, etc.
        // Must be configured manually via admin panel.
        Log::info('OrderKuota: getPriceList called — this provider does not have a product list API. Use manual product mapping.');
        return [];
    }
}
