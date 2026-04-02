<?php

namespace App\Console\Commands;

use App\Models\OtpVerification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PruneExpiredOtps extends Command
{
    protected $signature = 'otp:prune';
    protected $description = 'Delete expired and verified OTP records older than 24 hours';

    public function handle(): int
    {
        $cutoff = Carbon::now()->subHours(24);

        $deleted = OtpVerification::where(function ($q) use ($cutoff) {
            $q->where('expires_at', '<', $cutoff)
              ->orWhere(function ($q2) use ($cutoff) {
                  $q2->where('is_verified', true)->where('updated_at', '<', $cutoff);
              });
        })->delete();

        $this->info("Pruned {$deleted} expired/old OTP records.");

        return self::SUCCESS;
    }
}
