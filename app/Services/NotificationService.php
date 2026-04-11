<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

    /**
     * Notify customer about their transaction.
     * Sends email + WhatsApp if available.
     */
    public static function notifyCustomer(Transaction $transaction, string $event): void
    {
        if (!$transaction->relationLoaded('items')) {
            $transaction->load('items');
        }

        $siteName = Setting::get('site_name', 'PPOBKu');
        $invoiceUrl = route('transaction.show', $transaction->invoice_number);
        $item       = $transaction->items->first();
        $productName = $item?->product_name ?? 'Produk';

        // --- Email ---
        $email = $transaction->customer_email;
        if (!empty($email)) {
            $subject = match($event) {
                'new'     => "[{$siteName}] 🛒 Pesanan Dibuat - #{$transaction->invoice_number}",
                'paid'    => "[{$siteName}] 💳 Pembayaran Diterima - #{$transaction->invoice_number}",
                'success' => "[{$siteName}] ✅ Transaksi Berhasil - #{$transaction->invoice_number}",
                'failed'  => "[{$siteName}] ❌ Transaksi Gagal - #{$transaction->invoice_number}",
                default   => "[{$siteName}] Transaksi #{$transaction->invoice_number}",
            };

            $bodyHtml = self::buildCustomerEmailBody($transaction, $event, $siteName, $productName, $invoiceUrl);

            try {
                Mail::html($bodyHtml, function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                });
                Log::info("Customer email sent [{$event}] to {$email} for #{$transaction->invoice_number}");
            } catch (\Exception $e) {
                Log::warning('Customer email notification failed: ' . $e->getMessage());
            }
        }

        // --- WhatsApp ---
        $waNumber = $transaction->customer_whatsapp ?? $transaction->customer_contact ?? null;
        if (!empty($waNumber) && Setting::get('wa_enabled') === '1') {
            try {
                $wa = app(WhatsAppService::class);
                $waText = self::buildCustomerWhatsAppMessage($transaction, $event, $siteName, $productName, $invoiceUrl);
                $wa->sendMessage($waNumber, $waText);
                Log::info("Customer WA sent [{$event}] to {$waNumber} for #{$transaction->invoice_number}");
            } catch (\Exception $e) {
                Log::warning('Customer WA notification failed: ' . $e->getMessage());
            }
        }
    }

    protected static function buildCustomerWhatsAppMessage(Transaction $tx, string $event, string $siteName, string $productName, string $invoiceUrl): string
    {
        $statusLine = match($event) {
            'new'     => "🛒 *Pesanan Dibuat*",
            'paid'    => "💳 *Pembayaran Diterima*",
            'success' => "✅ *Transaksi Berhasil!*",
            'failed'  => "❌ *Transaksi Gagal*",
            default   => "📋 *Update Transaksi*",
        };

        $formattedTotal = number_format((float) $tx->total_amount, 0, ',', '.');

        $msg = "{$statusLine}\n\n"
            . "Halo, berikut info transaksi kamu di *{$siteName}*:\n\n"
            . "📄 Invoice: `{$tx->invoice_number}`\n"
            . "🛍 Produk: {$productName}\n"
            . "🎯 Target: {$tx->target_input}\n"
            . "💵 Total: *Rp {$formattedTotal}*\n";

        if ($event === 'success' && !empty($tx->sn)) {
            $msg .= "🔑 SN: `{$tx->sn}`\n";
        }

        if ($event === 'paid') {
            $msg .= "\n⏳ Pesananmu sedang diproses, tunggu notifikasi selanjutnya ya!\n";
        } elseif ($event === 'new') {
            $msg .= "\n💡 Silakan segera lakukan pembayaran.\n";
        } elseif ($event === 'failed') {
            $msg .= "\n🔄 Dana akan dikembalikan jika pembayaran sudah diterima. Hubungi CS jika butuh bantuan.\n";
        }

        $msg .= "\n🔗 Detail: {$invoiceUrl}\n\nTerima kasih! 🙏\n_{$siteName}_";

        return $msg;
    }

    protected static function buildCustomerEmailBody(Transaction $tx, string $event, string $siteName, string $productName, string $invoiceUrl): string
    {
        $statusIcon  = match($event) { 'success' => '✅', 'failed' => '❌', 'paid' => '💳', 'new' => '🛒', default => '📋' };
        $statusText  = match($event) { 'success' => 'Transaksi Berhasil!', 'failed' => 'Transaksi Gagal', 'paid' => 'Pembayaran Diterima', 'new' => 'Pesanan Dibuat', default => 'Update Transaksi' };
        $formattedTotal = number_format((float) $tx->total_amount, 0, ',', '.');

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f5f5f5;font-family:Arial,sans-serif;">
<table cellpadding="0" cellspacing="0" width="100%" style="background:#f5f5f5;padding:30px 0;">
<tr><td align="center">
<table cellpadding="0" cellspacing="0" width="540" style="background:#1c1c1c;border-radius:16px;overflow:hidden;">
  <tr><td style="background:#121212;padding:24px 32px;text-align:center;border-bottom:1px solid #2d2d2d;">
    <h1 style="color:#f97316;margin:0;font-size:22px;font-weight:900;">{$siteName}</h1>
  </td></tr>
  <tr><td style="padding:32px;text-align:center;">
    <div style="font-size:48px;margin-bottom:12px;">{$statusIcon}</div>
    <h2 style="color:#fff;margin:0 0 8px;font-size:22px;">{$statusText}</h2>
    <p style="color:#888;margin:0;font-size:14px;">Order ID: <strong style="color:#f97316;font-family:monospace;">{$tx->invoice_number}</strong></p>
  </td></tr>
  <tr><td style="padding:0 32px 24px;">
    <table width="100%" style="background:#121212;border-radius:12px;overflow:hidden;border:1px solid #2d2d2d;">
      <tr><td style="padding:12px 16px;border-bottom:1px solid #2d2d2d;">
        <span style="color:#666;font-size:12px;">Produk</span><br>
        <span style="color:#fff;font-weight:bold;font-size:14px;">{$productName}</span>
      </td></tr>
      <tr><td style="padding:12px 16px;border-bottom:1px solid #2d2d2d;">
        <span style="color:#666;font-size:12px;">Target</span><br>
        <span style="color:#fff;font-size:14px;font-family:monospace;">{$tx->target_input}</span>
      </td></tr>
      <tr><td style="padding:12px 16px;">
        <span style="color:#666;font-size:12px;">Total</span><br>
        <span style="color:#f97316;font-weight:900;font-size:20px;">Rp {$formattedTotal}</span>
      </td></tr>
    </table>
  </td></tr>
  <tr><td style="padding:0 32px 32px;text-align:center;">
    <a href="{$invoiceUrl}" style="display:inline-block;background:#f97316;color:#fff;font-weight:bold;text-decoration:none;padding:14px 32px;border-radius:12px;font-size:14px;">
      Lihat Invoice Detail →
    </a>
  </td></tr>
  <tr><td style="background:#0d0d0d;padding:16px 32px;text-align:center;border-top:1px solid #2d2d2d;">
    <p style="color:#555;margin:0;font-size:11px;">Email ini dikirim otomatis oleh {$siteName}. Jangan balas email ini.</p>
  </td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }
}
