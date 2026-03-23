<?php

declare(strict_types=1);

namespace App\Domain\Promo\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCampaign;
use App\Models\PromoRedemption;

final class PromoEngineService
{
    /**
     * @return array{ok: bool, code: string, message: string, discount_amount: float, cashback_amount: float, campaign: PromoCampaign|null}
     */
    public function evaluate(?string $promoCode, Product $product, float $baseAmount, ?int $userId): array
    {
        $normalizedCode = strtoupper(trim((string) $promoCode));

        if ($normalizedCode === '') {
            return [
                'ok' => true,
                'code' => '',
                'message' => '',
                'discount_amount' => 0.0,
                'cashback_amount' => 0.0,
                'campaign' => null,
            ];
        }

        $campaign = PromoCampaign::query()
            ->where('code', $normalizedCode)
            ->first();

        if ($campaign === null) {
            return $this->invalid($normalizedCode, 'Kode promo tidak ditemukan.');
        }

        $now = now();

        if (!$campaign->is_active) {
            return $this->invalid($normalizedCode, 'Promo sedang tidak aktif.');
        }

        if ($campaign->start_at !== null && $campaign->start_at->gt($now)) {
            return $this->invalid($normalizedCode, 'Promo belum mulai.');
        }

        if ($campaign->end_at !== null && $campaign->end_at->lt($now)) {
            return $this->invalid($normalizedCode, 'Promo sudah berakhir.');
        }

        if ((float) $campaign->min_order_amount > 0 && $baseAmount < (float) $campaign->min_order_amount) {
            return $this->invalid($normalizedCode, 'Minimum transaksi promo belum terpenuhi.');
        }

        if (!$this->isScopeMatch($campaign, $product)) {
            return $this->invalid($normalizedCode, 'Promo tidak berlaku untuk produk ini.');
        }

        $totalQuota = $campaign->quota_total !== null ? (int) $campaign->quota_total : null;
        if ($totalQuota !== null) {
            $used = PromoRedemption::query()->where('promo_campaign_id', $campaign->id)->count();
            if ($used >= $totalQuota) {
                return $this->invalid($normalizedCode, 'Kuota promo sudah habis.');
            }
        }

        $perUserQuota = $campaign->quota_per_user !== null ? (int) $campaign->quota_per_user : null;
        if ($perUserQuota !== null && $userId !== null) {
            $usedByUser = PromoRedemption::query()
                ->where('promo_campaign_id', $campaign->id)
                ->where('user_id', $userId)
                ->count();

            if ($usedByUser >= $perUserQuota) {
                return $this->invalid($normalizedCode, 'Kuota promo user sudah habis.');
            }
        }

        $reward = $this->calculateReward($campaign, $baseAmount);

        return [
            'ok' => true,
            'code' => $normalizedCode,
            'message' => 'Promo berhasil diterapkan.',
            'discount_amount' => $reward['discount_amount'],
            'cashback_amount' => $reward['cashback_amount'],
            'campaign' => $campaign,
        ];
    }

    public function recordRedemption(PromoCampaign $campaign, Order $order, ?int $userId, float $discountAmount, float $cashbackAmount): void
    {
        PromoRedemption::query()->create([
            'promo_campaign_id' => $campaign->id,
            'order_id' => $order->id,
            'user_id' => $userId,
            'campaign_code' => (string) $campaign->code,
            'campaign_type' => strtoupper((string) $campaign->campaign_type),
            'discount_amount' => round($discountAmount, 2),
            'cashback_amount' => round($cashbackAmount, 2),
            'redeemed_at' => now(),
            'meta' => [
                'order_code' => $order->order_code,
                'final_amount' => (float) $order->final_amount,
            ],
        ]);
    }

    /**
     * @return array{discount_amount: float, cashback_amount: float}
     */
    private function calculateReward(PromoCampaign $campaign, float $baseAmount): array
    {
        $rawReward = 0.0;
        $mode = strtoupper((string) $campaign->discount_mode);

        if ($mode === 'PERCENTAGE') {
            $rawReward = ($baseAmount * (float) $campaign->discount_value) / 100;
        } else {
            $rawReward = (float) $campaign->discount_value;
        }

        $max = $campaign->max_discount_amount !== null ? (float) $campaign->max_discount_amount : null;
        if ($max !== null && $rawReward > $max) {
            $rawReward = $max;
        }

        $reward = max(0.0, min($baseAmount, round($rawReward, 2)));

        if (strtoupper((string) $campaign->campaign_type) === 'CASHBACK') {
            return [
                'discount_amount' => 0.0,
                'cashback_amount' => $reward,
            ];
        }

        return [
            'discount_amount' => $reward,
            'cashback_amount' => 0.0,
        ];
    }

    private function isScopeMatch(PromoCampaign $campaign, Product $product): bool
    {
        $scope = strtoupper((string) $campaign->scope);

        if ($scope === 'PRODUCT') {
            return (int) ($campaign->product_id ?? 0) === (int) $product->id;
        }

        if ($scope === 'CATEGORY') {
            return (int) ($campaign->category_id ?? 0) === (int) $product->category_id;
        }

        return true;
    }

    /**
     * @return array{ok: bool, code: string, message: string, discount_amount: float, cashback_amount: float, campaign: PromoCampaign|null}
     */
    private function invalid(string $code, string $message): array
    {
        return [
            'ok' => false,
            'code' => $code,
            'message' => $message,
            'discount_amount' => 0.0,
            'cashback_amount' => 0.0,
            'campaign' => null,
        ];
    }
}
