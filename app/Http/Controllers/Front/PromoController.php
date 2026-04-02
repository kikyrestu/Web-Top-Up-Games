<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Support\Carbon;

class PromoController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $vouchers = Voucher::where('is_active', true)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', $now))
            ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn($q) => $q->whereNull('max_uses')->orWhereColumn('uses_count', '<', 'max_uses'))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('front.promo', compact('vouchers'));
    }
}
