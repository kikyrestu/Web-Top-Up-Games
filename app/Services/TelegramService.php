<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected ?string $botToken;
    protected ?string $chatId;

    public function __construct()
    {
        $this->botToken = Setting::get('telegram_bot_token');
        $this->chatId   = Setting::get('telegram_chat_id');
    }

    public function isConfigured(): bool
    {
        return !empty($this->botToken) && !empty($this->chatId);
    }

    public function sendMessage(string $chatId, string $text, string $parseMode = 'HTML'): bool
    {
        if (empty($this->botToken)) {
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->botToken}/sendMessage";

            $response = Http::post($url, [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => $parseMode,
            ]);

            if (!$response->successful()) {
                Log::warning('Telegram send failed', ['response' => $response->json()]);
                return false;
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Telegram error: ' . $e->getMessage());
            return false;
        }
    }

    public function sendToAdmin(string $text): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        return $this->sendMessage($this->chatId, $text);
    }

    /**
     * Format & send transaction notification to admin.
     */
    public function sendTransactionNotification($transaction, string $event): bool
    {
        $statusEmoji = match ($event) {
            'paid'       => '💰',
            'success'    => '✅',
            'failed'     => '❌',
            'processing' => '⏳',
            default      => '📋',
        };

        $item = $transaction->items->first();
        $productName = $item?->product_name ?? 'Unknown';

        $text = "{$statusEmoji} <b>Transaksi {$event}</b>\n\n"
            . "📄 Invoice: <code>{$transaction->invoice_number}</code>\n"
            . "🛍 Produk: {$productName}\n"
            . "🎯 Target: {$transaction->target_input}\n"
            . "💵 Total: Rp " . number_format($transaction->total_amount, 0, ',', '.') . "\n"
            . "📱 Kontak: {$transaction->customer_contact}\n"
            . "🕐 Waktu: {$transaction->updated_at->format('d/m/Y H:i')}";

        return $this->sendToAdmin($text);
    }
}
