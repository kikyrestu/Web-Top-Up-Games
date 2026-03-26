<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Notify admin about a transaction event.
     * Dispatches to all enabled channels (Telegram, WhatsApp).
     */
    public static function notifyAdmin(Transaction $transaction, string $event): void
    {
        if (!$transaction->relationLoaded('items')) {
            $transaction->load('items');
        }

        // Telegram
        if (Setting::get('telegram_enabled') === '1') {
            try {
                $telegram = app(TelegramService::class);
                $telegram->sendTransactionNotification($transaction, $event);
            } catch (\Exception $e) {
                Log::warning('Telegram notification failed: ' . $e->getMessage());
            }
        }

        // WhatsApp
        if (Setting::get('wa_enabled') === '1') {
            try {
                $wa = app(WhatsAppService::class);
                $wa->sendTransactionNotification($transaction, $event);
            } catch (\Exception $e) {
                Log::warning('WhatsApp notification failed: ' . $e->getMessage());
            }
        }
    }
}
