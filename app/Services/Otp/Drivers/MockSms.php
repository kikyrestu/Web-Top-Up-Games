<?php

namespace App\Services\Otp\Drivers;

use App\Services\Otp\OtpProviderInterface;
use Illuminate\Support\Facades\Log;

class MockSms implements OtpProviderInterface
{
    public function sendOtp(string $target, string $code, array $credentials): bool
    {
        Log::info("MOCK OTP SENT to {$target}: {$code}");
        return true;
    }

    public function validateCredentials(array $credentials): bool
    {
        return true;
    }
}
