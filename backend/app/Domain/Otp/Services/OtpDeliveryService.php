<?php

declare(strict_types=1);

namespace App\Domain\Otp\Services;

use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Log;

final class OtpDeliveryService
{
    public function __construct(private readonly MailManager $mailManager)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function deliver(string $channel, string $destination, string $otp): array
    {
        $driver = strtolower((string) config('services.otp.driver', 'demo'));
        $normalizedChannel = strtoupper($channel);

        if ($driver === 'demo') {
            return [
                'driver' => 'demo',
                'delivered' => true,
                'message' => 'OTP generated in demo mode.',
                'otp_preview' => $otp,
            ];
        }

        if ($normalizedChannel === 'EMAIL') {
            return $this->deliverEmail($destination, $otp, $driver);
        }

        return $this->deliverWaStub($destination, $otp);
    }

    /**
     * @return array<string, mixed>
     */
    private function deliverEmail(string $destination, string $otp, string $driver): array
    {
        try {
            $subject = (string) config('services.otp.subject', 'Kode OTP Login');
            $body = "Kode OTP kamu: {$otp}. Berlaku 5 menit. Jangan bagikan ke siapapun.";

            $this->mailManager->raw($body, static function ($message) use ($destination, $subject): void {
                $message->to($destination)->subject($subject);
            });

            return [
                'driver' => $driver,
                'delivered' => true,
                'message' => 'OTP sent via email.',
            ];
        } catch (\Throwable $exception) {
            Log::warning('OTP email delivery failed', [
                'destination' => $destination,
                'error' => $exception->getMessage(),
            ]);

            return [
                'driver' => $driver,
                'delivered' => false,
                'message' => 'OTP email delivery failed.',
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function deliverWaStub(string $destination, string $otp): array
    {
        Log::info('WA OTP stub delivery', [
            'destination' => $destination,
            'otp_preview' => $otp,
        ]);

        return [
            'driver' => 'wa_stub',
            'delivered' => true,
            'message' => 'OTP sent via WA stub gateway.',
        ];
    }
}
