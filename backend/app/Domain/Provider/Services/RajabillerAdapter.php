<?php

declare(strict_types=1);

namespace App\Domain\Provider\Services;

use App\Domain\Provider\Contracts\ProviderAdapterInterface;
use App\Domain\Provider\Support\ProviderStatusNormalizer;
use Illuminate\Support\Facades\Http;
use Throwable;

final class RajabillerAdapter implements ProviderAdapterInterface
{
    private const PRODUCT_GROUPS = [
        'GAME ONLINE', 'TELKOMSEL', 'ISAT', 'AXIS / XL', 'SMART', 'KARTU3',
        'FREN', 'BOLT', 'PLN', 'EMONEY', 'PDAM', 'TV BERLANGGANAN',
        'MULTI FINANCE', 'PAJAK', 'SAMSAT', 'KARTU KREDIT',
        'TELEPON PASCA BAYAR', 'ASURANSI', 'IPL', 'EDUKASI',
    ];

    /**
     * RC codes that mean final failure (not retryable).
     */
    private const FAILED_RC = ['01', '03', '04', '05', '06', '08', '09', '16', '77', '87', '97'];

    private function getConfig(): array
    {
        return [
            'base_url' => (string) config('services.rajabiller.base_url'),
            'uid' => (string) config('services.rajabiller.uid'),
            'pin' => (string) config('services.rajabiller.pin'),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function syncProducts(): array
    {
        $config = $this->getConfig();

        if ($config['base_url'] === '' || $config['uid'] === '' || $config['pin'] === '') {
            return [];
        }

        $products = [];

        foreach (self::PRODUCT_GROUPS as $group) {
            try {
                $response = Http::timeout(25)->post($config['base_url'], [
                    'method' => 'info',
                    'uid' => $config['uid'],
                    'pin' => $config['pin'],
                    'produk' => $group,
                ]);

                if (!$response->successful()) {
                    continue;
                }

                $json = $response->json();
                if (!is_array($json)) {
                    continue;
                }

                // info response can be a list or nested under 'data'
                $items = $json['data'] ?? $json['DATA'] ?? [];
                if (!is_array($items) || empty($items)) {
                    // Maybe the response itself is a single product or flat list
                    if (isset($json['id_produk']) || isset($json['kode'])) {
                        $items = [$json];
                    } else {
                        // Check if it's an array of products directly
                        $items = array_filter($json, static fn($v) => is_array($v) && (isset($v['id_produk']) || isset($v['kode'])));
                    }
                }

                foreach ($items as $item) {
                    if (!is_array($item)) {
                        continue;
                    }

                    $code = (string) ($item['id_produk'] ?? $item['kode'] ?? $item['KODE'] ?? '');
                    if ($code === '') {
                        continue;
                    }

                    $products[] = [
                        'provider_product_code' => $code,
                        'provider_product_name' => (string) ($item['nama_produk'] ?? $item['nama'] ?? $item['NAMA'] ?? $code),
                        'base_price' => (float) ($item['harga'] ?? $item['HARGA'] ?? $item['nominal'] ?? 0),
                        'admin_fee' => (float) ($item['nominal_admin'] ?? $item['admin'] ?? 0),
                        'commission' => 0.0,
                        'raw_payload' => $item,
                    ];
                }
            } catch (Throwable) {
                continue;
            }

            usleep(100_000); // 100ms delay between groups
        }

        return $products;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createOrder(array $payload): array
    {
        $config = $this->getConfig();

        if ($config['base_url'] === '' || $config['uid'] === '' || $config['pin'] === '') {
            return [
                'status' => 'FAILED',
                'is_retryable' => false,
                'provider_ref' => null,
                'raw' => ['error' => 'missing_rajabiller_config'],
            ];
        }

        $produk = (string) ($payload['buyer_sku_code'] ?? '');
        $idpel = (string) ($payload['customer_no'] ?? '');
        $ref1 = (string) ($payload['ref_id'] ?? '');

        if ($produk === '' || $idpel === '') {
            return [
                'status' => 'FAILED',
                'is_retryable' => false,
                'provider_ref' => null,
                'raw' => ['error' => 'invalid_payload'],
            ];
        }

        $requestPayload = [
            'method' => 'bayar',
            'uid' => $config['uid'],
            'pin' => $config['pin'],
            'produk' => $produk,
            'idpel' => $idpel,
            'ref1' => $ref1,
        ];

        // PLN prepaid and emoney need nominal
        if (isset($payload['nominal'])) {
            $requestPayload['nominal'] = (string) $payload['nominal'];
        }

        try {
            $response = Http::timeout(45)->post($config['base_url'], $requestPayload);
        } catch (Throwable $exception) {
            return [
                'status' => 'PENDING',
                'is_retryable' => true,
                'provider_ref' => null,
                'raw' => [
                    'error' => 'network_exception',
                    'message' => $exception->getMessage(),
                ],
            ];
        }

        if (!$response->successful()) {
            return [
                'status' => 'FAILED',
                'is_retryable' => ProviderStatusNormalizer::isRetryableHttpStatus($response->status()),
                'provider_ref' => null,
                'raw' => [
                    'error' => 'http_status_' . $response->status(),
                    'http_status' => $response->status(),
                ],
            ];
        }

        $json = $response->json();
        if (!is_array($json)) {
            return [
                'status' => 'PENDING',
                'is_retryable' => true,
                'provider_ref' => null,
                'raw' => ['error' => 'invalid_json_response'],
            ];
        }

        $rc = (string) ($json['rc'] ?? '');

        if ($rc === '00') {
            $status = 'SUCCESS';
            $retryable = false;
        } elseif ($rc === '68' || $rc === '') {
            $status = 'PENDING';
            $retryable = false;
        } elseif (in_array($rc, self::FAILED_RC, true)) {
            $status = 'FAILED';
            $retryable = in_array($rc, ['09', '77'], true); // Too many requests or biller down
        } else {
            $status = 'PENDING';
            $retryable = false;
        }

        return [
            'status' => $status,
            'is_retryable' => $retryable,
            'provider_ref' => $json['trxid'] ?? $json['refid'] ?? null,
            'raw' => $json,
        ];
    }
}
