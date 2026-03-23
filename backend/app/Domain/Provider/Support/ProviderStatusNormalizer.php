<?php

declare(strict_types=1);

namespace App\Domain\Provider\Support;

final class ProviderStatusNormalizer
{
    /**
     * @return array{status: string, is_retryable: bool, raw_status: string}
     */
    public static function normalize(mixed $status): array
    {
        $rawStatus = strtoupper(trim((string) $status));

        if ($rawStatus === '') {
            return [
                'status' => 'PENDING',
                'is_retryable' => false,
                'raw_status' => $rawStatus,
            ];
        }

        if (in_array($rawStatus, ['SUCCESS', 'PAID', 'SUKSES', 'BERHASIL', 'DONE', 'COMPLETED'], true)) {
            return [
                'status' => 'SUCCESS',
                'is_retryable' => false,
                'raw_status' => $rawStatus,
            ];
        }

        if (in_array($rawStatus, ['PENDING', 'PROCESS', 'PROCESSING', 'IN_PROGRESS', 'WAITING', 'MENUNGGU'], true)) {
            return [
                'status' => 'PENDING',
                'is_retryable' => false,
                'raw_status' => $rawStatus,
            ];
        }

        if (in_array($rawStatus, ['TIMEOUT', 'TEMPORARY_ERROR', 'NETWORK_ERROR', 'SERVICE_UNAVAILABLE'], true)) {
            return [
                'status' => 'FAILED',
                'is_retryable' => true,
                'raw_status' => $rawStatus,
            ];
        }

        if (in_array($rawStatus, ['FAILED', 'ERROR', 'GAGAL', 'REJECTED', 'CANCELED', 'CANCELLED', 'EXPIRED'], true)) {
            return [
                'status' => 'FAILED',
                'is_retryable' => false,
                'raw_status' => $rawStatus,
            ];
        }

        return [
            'status' => 'PENDING',
            'is_retryable' => false,
            'raw_status' => $rawStatus,
        ];
    }

    public static function isRetryableHttpStatus(int $statusCode): bool
    {
        return $statusCode === 408 || $statusCode === 429 || $statusCode >= 500;
    }
}
