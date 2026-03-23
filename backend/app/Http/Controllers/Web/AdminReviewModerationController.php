<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewModeration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminReviewModerationController extends Controller
{
    public function index(Request $request): View
    {
        $status = strtoupper(trim((string) $request->query('status', 'PENDING_APPROVAL')));
        $search = trim((string) $request->query('q', ''));

        $allowedStatus = ['PENDING_APPROVAL', 'APPROVED', 'REJECTED'];
        if (!in_array($status, $allowedStatus, true)) {
            $status = 'PENDING_APPROVAL';
        }

        $reviews = Review::query()
            ->with([
                'product:id,name,slug',
                'user:id,name,email',
                'order:id,order_code',
            ])
            ->when($status !== 'ALL', static fn ($query) => $query->where('status', $status))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('content', 'like', '%'.$search.'%')
                        ->orWhereHas('product', static fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
                        ->orWhereHas('order', static fn ($q) => $q->where('order_code', 'like', '%'.$search.'%'));
                });
            })
            ->orderByRaw("CASE status WHEN 'PENDING_APPROVAL' THEN 0 WHEN 'REJECTED' THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'filters' => [
                'status' => $status,
                'q' => $search,
            ],
        ]);
    }

    public function show(Review $review): View
    {
        $review->load([
            'product:id,name,slug',
            'user:id,name,email',
            'order:id,order_code,status,completed_at',
            'moderations:id,review_id,admin_user_id,action,reason,moderated_at,created_at',
            'moderations.adminUser:id,name,email',
        ]);

        $moderationHistory = $review->moderations
            ->sortByDesc(static fn ($row) => $row->moderated_at ?? $row->created_at)
            ->values();

        return view('admin.reviews.show', [
            'review' => $review,
            'moderationHistory' => $moderationHistory,
        ]);
    }

    public function approve(Request $request, Review $review): RedirectResponse
    {
        if ($review->status === 'APPROVED') {
            return back()->with('notice', 'Review sudah dalam status APPROVED.');
        }

        $review->update([
            'status' => 'APPROVED',
            'approved_at' => now(),
        ]);

        ReviewModeration::query()->create([
            'review_id' => $review->id,
            'admin_user_id' => $request->user()?->id,
            'action' => 'APPROVE',
            'reason' => trim((string) $request->input('reason', '')) ?: null,
            'moderated_at' => now(),
        ]);

        return back()->with('notice', 'Review berhasil di-approve.');
    }

    public function reject(Request $request, Review $review): RedirectResponse
    {
        if ($review->status === 'REJECTED') {
            return back()->with('notice', 'Review sudah dalam status REJECTED.');
        }

        $review->update([
            'status' => 'REJECTED',
            'approved_at' => null,
        ]);

        ReviewModeration::query()->create([
            'review_id' => $review->id,
            'admin_user_id' => $request->user()?->id,
            'action' => 'REJECT',
            'reason' => trim((string) $request->input('reason', '')) ?: null,
            'moderated_at' => now(),
        ]);

        return back()->with('notice', 'Review berhasil di-reject.');
    }
}
