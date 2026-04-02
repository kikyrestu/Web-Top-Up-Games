<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;

class ReviewController extends Controller
{
    public function index()
    {
        $reviews = ProductReview::with(['user', 'product'])->latest()->paginate(30);
        return view('admin.reviews.index', compact('reviews'));
    }

    public function approve(ProductReview $review)
    {
        $review->update(['is_approved' => true]);
        return back()->with('success', 'Review disetujui.');
    }

    public function reject(ProductReview $review)
    {
        $review->update(['is_approved' => false]);
        return back()->with('success', 'Review ditolak.');
    }

    public function destroy(ProductReview $review)
    {
        $review->delete();
        return back()->with('success', 'Review dihapus.');
    }
}
