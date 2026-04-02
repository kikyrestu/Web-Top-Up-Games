<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\Referral;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReferralService
{
    /**
     * Generate a unique referral code for a user.
     */
    public function generateReferralCode(User $user): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        $user->update(['referral_code' => $code]);

        return $code;
    }

    /**
     * Apply referral code during registration.
     * Links the new user (referee) to the referrer.
     */
    public function applyReferralCode(User $referee, string $code): bool
    {
        $referrer = User::where('referral_code', $code)->first();

        if (!$referrer || $referrer->id === $referee->id) {
            return false;
        }

        // Already referred
        if ($referee->referred_by) {
            return false;
        }

        $referee->update(['referred_by' => $referrer->id]);

        Referral::create([
            'referrer_id'  => $referrer->id,
            'referee_id'   => $referee->id,
            'status'       => 'active',
            'bonus_amount' => 0,
        ]);

        return true;
    }

    /**
     * Calculate and create commission for a transaction.
     * Called after a transaction is marked as SUCCESS.
     */
    public function processTransactionCommission(Transaction $transaction): void
    {
        if (!$transaction->user_id) return;

        $user = User::find($transaction->user_id);
        if (!$user) return;

        $transaction->load('items.product.category');

        DB::transaction(function () use ($transaction, $user) {
            $totalCommission = 0;

            foreach ($transaction->items as $item) {
                $commissionAmount = $this->calculateItemCommission($item);

                if ($commissionAmount <= 0) continue;

                // Update commission amount on the item
                $item->update(['commission_amount' => $commissionAmount]);

                $totalCommission += $commissionAmount;
            }

            if ($totalCommission <= 0) return;

            // Create commission record for the buyer
            Commission::create([
                'user_id'        => $user->id,
                'transaction_id' => $transaction->id,
                'type'           => 'transaction',
                'amount'         => $totalCommission,
                'status'         => 'approved',
                'note'           => 'Komisi dari transaksi #' . $transaction->invoice_number,
            ]);

            // Credit commission balance (separate from main wallet)
            $user->increment('commission_balance', $totalCommission);

            // If user was referred, also give bonus to referrer
            if ($user->referred_by) {
                $this->processReferrerBonus($user, $totalCommission, $transaction);
            }

            Log::info("Commission Processed: user_id={$user->id}, amount={$totalCommission}, tx={$transaction->invoice_number}");
        });
    }

    /**
     * Give a percentage bonus of the referee's commission to the referrer.
     */
    protected function processReferrerBonus(User $referee, float $refereeCommission, Transaction $transaction): void
    {
        $referrerBonusPercent = (float) Setting::get('referrer_bonus_percent', 10);
        if ($referrerBonusPercent <= 0) return;

        $bonus   = round($refereeCommission * ($referrerBonusPercent / 100), 2);
        $referrer = User::find($referee->referred_by);

        if (!$referrer || $bonus <= 0) return;

        Commission::create([
            'user_id'        => $referrer->id,
            'transaction_id' => $transaction->id,
            'type'           => 'referral_bonus',
            'amount'         => $bonus,
            'status'         => 'approved',
            'note'           => "Bonus referral dari transaksi {$referee->name} ({$referee->username}) #" . $transaction->invoice_number,
        ]);

        $referrer->increment('commission_balance', $bonus);

        // Update total bonus on referral record
        Referral::where('referrer_id', $referrer->id)
            ->where('referee_id', $referee->id)
            ->increment('bonus_amount', $bonus);
    }

    /**
     * Withdraw commission balance to main wallet balance.
     */
    public function withdrawCommission(User $user, float $amount): array
    {
        if ($amount <= 0) {
            return ['success' => false, 'message' => 'Jumlah penarikan tidak valid.'];
        }

        if ($user->commission_balance < $amount) {
            return ['success' => false, 'message' => 'Saldo komisi tidak mencukupi.'];
        }

        DB::transaction(function () use ($user, $amount) {
            $user->decrement('commission_balance', $amount);
            $user->increment('wallet_balance', $amount);

            Commission::create([
                'user_id' => $user->id,
                'type'    => 'withdrawal',
                'amount'  => -$amount,
                'status'  => 'paid',
                'note'    => 'Penarikan komisi ke saldo wallet',
                'paid_at' => now(),
            ]);
        });

        return ['success' => true, 'message' => "Rp " . number_format($amount, 0, ',', '.') . " berhasil dipindahkan ke wallet kamu!"];
    }

    /**
     * Calculate commission for a single transaction item.
     */
    protected function calculateItemCommission($item): float
    {
        $product  = $item->product  ?? null;
        $category = $product?->category ?? null;

        // Priority: product override → category → global setting
        $type  = $product?->commission_type  ?? $category?->commission_type  ?? Setting::get('default_commission_type', 'percentage');
        $value = $product?->commission_value ?? $category?->commission_value ?? (float) Setting::get('default_commission_value', 0);

        if (!$value || $value <= 0) return 0;

        $basePrice = (float) ($item->price ?? $product?->price_sell ?? 0);
        $qty       = (int) ($item->quantity ?? 1);

        if ($type === 'flat') {
            return round($value * $qty, 2);
        }

        if ($type === 'percentage') {
            return round($basePrice * $qty * ($value / 100), 2);
        }

        return 0;
    }
}
