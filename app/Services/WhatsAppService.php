<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $sidecarUrl;
    protected string $sidecarToken;

    public function __construct()
    {
        $this->sidecarUrl = rtrim(Setting::get('wa_bot_url', 'http://localhost:3001'), '/');
        $this->sidecarToken = Setting::get('wa_bot_token', '');
    }

    /**
     * Build HTTP client with optional auth token.
     */
    protected function http(int $timeout = 5)
    {
        $client = Http::timeout($timeout);
        if ($this->sidecarToken) {
            $client = $client->withHeaders(['Authorization' => 'Bearer ' . $this->sidecarToken]);
        }
        return $client;
    }

    /**
     * Get QR code as base64 image from sidecar.
     */
    public function getQrCode(): ?string
    {
        try {
            $response = $this->http(10)->get($this->sidecarUrl . '/qr');

            if ($response->successful()) {
                $data = $response->json();
                return $data['qr'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('WA Bot QR fetch failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get connection status from sidecar.
     */
    public function getStatus(): array
    {
        try {
            $response = $this->http(5)->get($this->sidecarUrl . '/status');

            if ($response->successful()) {
                return $response->json();
            }

            return ['connected' => false, 'error' => 'Sidecar not responding'];
        } catch (\Exception $e) {
            return ['connected' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a WA message via sidecar.
     */
    public function sendMessage(string $number, string $text): bool
    {
        try {
            // Normalize to 62xxx format
            $number = preg_replace('/^0/', '62', $number);
            $number = preg_replace('/^\+/', '', $number);

            $response = $this->http(15)->post($this->sidecarUrl . '/send', [
                'number'  => $number,
                'message' => $text,
            ]);

            return $response->successful() && ($response->json('success') ?? false);

        } catch (\Exception $e) {
            Log::error('WA Bot send failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send transaction notification to admin WA.
     */
    public function sendTransactionNotification($transaction, string $event): bool
    {
        $adminWa = Setting::get('contact_whatsapp');
        if (empty($adminWa)) {
            return false;
        }

        $statusLabel = match ($event) {
            'new'        => '🆕 ORDER BARU',
            'paid'       => '💰 PAID',
            'success'    => '✅ SUKSES',
            'failed'     => '❌ GAGAL',
            'processing' => '⏳ PROSES',
            default      => '📋 UPDATE',
        };

        $item = $transaction->items->first();
        $productName = $item?->product_name ?? 'Unknown';

        $text = "*{$statusLabel}*\n\n"
            . "📄 Invoice: {$transaction->invoice_number}\n"
            . "🛍 Produk: {$productName}\n"
            . "🎯 Target: {$transaction->target_input}\n"
            . "💵 Total: Rp " . number_format($transaction->total_amount, 0, ',', '.') . "\n"
            . "📱 Kontak: {$transaction->customer_contact}\n"
            . "🕐 Waktu: {$transaction->updated_at->format('d/m/Y H:i')}";

        return $this->sendMessage($adminWa, $text);
    }
}
