<?php

namespace App\Services;

use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Get the current balance of the user.
     */
    public function getBalance(User $user): float
    {
        return (float) $user->wallet_balance;
    }

    /**
     * Add funds to the wallet.
     */
    public function topUp(User $user, float $amount, string $reference = null, string $description = 'Top Up Saldo'): bool
    {
        if ($amount <= 0) {
            return false;
        }

        try {
            DB::beginTransaction();

            $user = User::lockForUpdate()->find($user->id);
            $balanceBefore = $user->wallet_balance;
            $balanceAfter = $balanceBefore + $amount;

            $user->update(['wallet_balance' => $balanceAfter]);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'topup',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => $reference,
                'description' => $description,
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Deduct funds from the wallet.
     */
    public function deduct(User $user, float $amount, string $reference = null, string $description = 'Pembayaran Transaksi'): bool
    {
        if ($amount <= 0) {
            return false;
        }

        try {
            DB::beginTransaction();

            $user = User::lockForUpdate()->find($user->id);
            $balanceBefore = $user->wallet_balance;

            if ($balanceBefore < $amount) {
                DB::rollBack();
                return false; // Insufficient balance
            }

            $balanceAfter = $balanceBefore - $amount;
            $user->update(['wallet_balance' => $balanceAfter]);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'purchase',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => $reference,
                'description' => $description,
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Refund funds back to the wallet.
     */
    public function refund(User $user, float $amount, string $reference = null, string $description = 'Refund Transaksi Batal'): bool
    {
        if ($amount <= 0) {
            return false;
        }

        try {
            DB::beginTransaction();

            $user = User::lockForUpdate()->find($user->id);
            $balanceBefore = $user->wallet_balance;
            $balanceAfter = $balanceBefore + $amount;

            $user->update(['wallet_balance' => $balanceAfter]);

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'refund',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'reference' => $reference,
                'description' => $description,
            ]);

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}
