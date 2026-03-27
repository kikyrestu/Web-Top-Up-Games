<?php

namespace App\Services\Otp;

interface OtpProviderInterface
{
    /**
     * Send OTP code to the target (email or phone).
     *
     * @param string $target The destination (email address or phone number)
     * @param string $code The OTP code to send
     * @param array $credentials Provider API credentials
     * @return bool True if successfully sent, false otherwise
     */
    public function sendOtp(string $target, string $code, array $credentials): bool;

    /**
     * Validate the provided credentials (e.g. via an API test call).
     *
     * @param array $credentials
     * @return bool True if valid
     */
    public function validateCredentials(array $credentials): bool;
}
