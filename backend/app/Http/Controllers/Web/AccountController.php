<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

final class AccountController extends Controller
{
    public function dashboard(Request $request): View
    {
        $userId = (int) $request->user()->id;

        $summary = [
            'total_orders' => Order::query()->where('user_id', $userId)->count(),
            'success_orders' => Order::query()->where('user_id', $userId)->where('status', 'SUCCESS')->count(),
            'failed_orders' => Order::query()->where('user_id', $userId)->where('status', 'FAILED')->count(),
        ];

        $recentOrders = Order::query()
            ->with(['product:id,name', 'payment:id,order_id,status'])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        return view('account.dashboard', [
            'summary' => $summary,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function transactions(Request $request): View
    {
        $orders = Order::query()
            ->with(['product:id,name,type', 'payment:id,order_id,status,gateway,gateway_reference'])
            ->where('user_id', (int) $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('account.transactions', [
            'orders' => $orders,
        ]);
    }

    public function transactionShow(Request $request, string $orderCode): View
    {
        $order = Order::query()
            ->with([
                'product:id,name,type',
                'items:id,order_id,quantity,unit_price,subtotal',
                'payment:id,order_id,gateway,gateway_reference,method,amount,status,paid_at,expired_at,meta',
                'providerAttempts:id,order_id,provider_id,attempt_no,status,provider_ref,attempted_at',
                'providerAttempts.provider:id,code,name',
            ])
            ->where('user_id', (int) $request->user()->id)
            ->where('order_code', $orderCode)
            ->firstOrFail();

        return view('account.transaction-show', [
            'order' => $order,
            'paymentMeta' => is_array($order->payment?->meta) ? $order->payment?->meta : [],
        ]);
    }

    public function profile(Request $request): View
    {
        return view('account.profile', [
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return back()->with('notice', 'Profil berhasil diperbarui.');
    }

    public function reviews(Request $request): View
    {
        $userId = (int) $request->user()->id;

        $reviews = Review::query()
            ->with('product:id,name,slug')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $reviewedOrderIds = Review::query()
            ->where('user_id', $userId)
            ->whereNotNull('order_id')
            ->pluck('order_id')
            ->all();

        $eligibleOrders = Order::query()
            ->with('product:id,name,slug')
            ->where('user_id', $userId)
            ->where('status', 'SUCCESS')
            ->when($reviewedOrderIds !== [], static fn ($query) => $query->whereNotIn('id', $reviewedOrderIds))
            ->orderByDesc('completed_at')
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('account.reviews', [
            'reviews' => $reviews,
            'eligibleOrders' => $eligibleOrders,
        ]);
    }

    public function storeReview(Request $request): RedirectResponse
    {
        $userId = (int) $request->user()->id;
        $cooldownKey = 'account:review:cooldown:user:'.$userId;

        if (!Cache::add($cooldownKey, '1', now()->addSeconds(20))) {
            return back()->withErrors([
                'order_code' => 'Terlalu cepat mengirim ulasan. Coba lagi dalam beberapa detik.',
            ])->withInput();
        }

        $validated = $request->validate([
            'order_code' => ['required', 'string', 'max:50'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $order = Order::query()
            ->where('user_id', $userId)
            ->where('order_code', $validated['order_code'])
            ->where('status', 'SUCCESS')
            ->first();

        if ($order === null) {
            return back()->withErrors([
                'order_code' => 'Order tidak valid atau belum sukses sehingga belum bisa direview.',
            ])->withInput();
        }

        $alreadyReviewed = Review::query()
            ->where('user_id', $userId)
            ->where('order_id', $order->id)
            ->exists();

        if ($alreadyReviewed) {
            return back()->withErrors([
                'order_code' => 'Order ini sudah pernah direview.',
            ])->withInput();
        }

        Review::query()->create([
            'product_id' => $order->product_id,
            'user_id' => $userId,
            'order_id' => $order->id,
            'rating' => (int) $validated['rating'],
            'content' => trim((string) $validated['content']),
            'status' => 'PENDING_APPROVAL',
            'approved_at' => null,
        ]);

        return back()->with('notice', 'Ulasan berhasil dikirim dan menunggu moderasi admin.');
    }
}
