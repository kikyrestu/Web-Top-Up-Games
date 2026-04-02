<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use App\Models\Transaction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Store a new review (AJAX from invoice popup).
     */
    public function store(Request $request)
    {
        $request->validate([
            'product_id'     => 'required|exists:products,id',
            'transaction_id' => 'required|exists:transactions,id',
            'rating'         => 'required|integer|min:1|max:5',
            'comment'        => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu.'], 401);
        }

        // Verify transaction belongs to user and is successful
        $transaction = Transaction::where('id', $request->transaction_id)
            ->where('user_id', $user->id)
            ->where('transaction_status', 'success')
            ->first();

        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak valid.'], 422);
        }

        // Prevent duplicate review per user per product
        $exists = ProductReview::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Kamu sudah pernah memberikan ulasan untuk produk ini.'], 422);
        }

        ProductReview::create([
            'user_id'        => $user->id,
            'product_id'     => $request->product_id,
            'transaction_id' => $request->transaction_id,
            'rating'         => $request->rating,
            'comment'        => $request->comment,
            'is_approved'    => false,
        ]);

        return response()->json(['success' => true, 'message' => 'Terima kasih! Ulasan kamu sedang dalam proses moderasi.']);
    }
}
