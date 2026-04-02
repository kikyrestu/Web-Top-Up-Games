<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Support\Facades\Auth;

class VoucherService
{
    /**
     * Validate a voucher code and return voucher + calculated discount.
     * Returns array: ['success' => bool, 'message' => string, 'voucher' => Voucher|null, 'discount' => float]
     */
    public function apply(string $code, float $subtotal, ?int $userId = null): array
    {
        $code    = strtoupper(trim($code));
        $voucher = Voucher::where('code', $code)->first();

        if (!$voucher) {
            return ['success' => false, 'message' => 'Kode voucher tidak ditemukan.', 'voucher' => null, 'discount' => 0];
        }

        if (!$voucher->isValid($subtotal)) {
            $reason = !$voucher->is_active
                ? 'Voucher sudah tidak aktif.'
                : ($voucher->expires_at?->isPast() ? 'Voucher sudah expired.' : ($subtotal < $voucher->min_purchase
                    ? 'Minimal pembelian Rp ' . number_format($voucher->min_purchase, 0, ',', '.') . ' untuk voucher ini.'
                    : 'Voucher sudah habis.'));
            return ['success' => false, 'message' => $reason, 'voucher' => null, 'discount' => 0];
        }

        // Check per-user limit
        if ($userId) {
            $userUsage = VoucherUsage::where('voucher_id', $voucher->id)->where('user_id', $userId)->count();
            if ($userUsage >= $voucher->max_uses_per_user) {
                return ['success' => false, 'message' => 'Kamu sudah pernah menggunakan voucher ini.', 'voucher' => null, 'discount' => 0];
            }
        }

        $discount = $voucher->calculateDiscount($subtotal);

        return [
            'success'  => true,
            'message'  => "Voucher diterapkan! Potongan Rp " . number_format($discount, 0, ',', '.'),
            'voucher'  => $voucher,
            'discount' => $discount,
        ];
    }

    /**
     * Record voucher usage. Call after transaction is confirmed.
     */
    public function recordUsage(Voucher $voucher, float $discountAmount, ?int $userId = null, ?int $transactionId = null): void
    {
        VoucherUsage::create([
            'voucher_id'      => $voucher->id,
            'user_id'         => $userId,
            'transaction_id'  => $transactionId,
            'discount_amount' => $discountAmount,
        ]);

        $voucher->increment('uses_count');
    }
}
