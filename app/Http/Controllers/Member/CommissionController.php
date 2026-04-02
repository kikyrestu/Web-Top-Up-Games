<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Commission;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommissionController extends Controller
{
    public function __construct(protected ReferralService $referralService) {}

    /**
     * Show the commission/referral dashboard for the member.
     */
    public function index()
    {
        $user = Auth::user();

        // Auto-generate referral code if not yet set
        $referralCode = $user->getReferralCode();

        $commissions = Commission::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_earned'      => Commission::where('user_id', $user->id)->where('amount', '>', 0)->sum('amount'),
            'pending'           => Commission::where('user_id', $user->id)->pending()->sum('amount'),
            'commission_balance' => (float) $user->commission_balance,
            'total_referrals'   => $user->referralsMade()->count(),
            'active_referrals'  => $user->referralsMade()->where('status', 'active')->count(),
        ];

        $referralUrl = route('register') . '?ref=' . $referralCode;

        return view('member.commission', compact('user', 'commissions', 'stats', 'referralCode', 'referralUrl'));
    }

    /**
     * Withdraw commission balance to main wallet.
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000',
        ]);

        $result = $this->referralService->withdrawCommission(Auth::user(), (float) $request->amount);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
