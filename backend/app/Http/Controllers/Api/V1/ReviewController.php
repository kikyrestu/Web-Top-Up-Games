<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_code' => ['required', 'string', 'max:50'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $userId = (int) $request->user()->id;

        $order = Order::query()
            ->where('user_id', $userId)
            ->where('order_code', $validated['order_code'])
            ->where('status', 'SUCCESS')
            ->first();

        if ($order === null) {
            return response()->json([
                'success' => false,
                'code' => 'REVIEW_ORDER_NOT_ELIGIBLE',
                'message' => 'Order not eligible for review',
                'data' => null,
            ], 422);
        }

        $alreadyReviewed = Review::query()
            ->where('user_id', $userId)
            ->where('order_id', $order->id)
            ->exists();

        if ($alreadyReviewed) {
            return response()->json([
                'success' => false,
                'code' => 'REVIEW_ALREADY_EXISTS',
                'message' => 'Review for this order already submitted',
                'data' => null,
            ], 422);
        }

        $review = Review::query()->create([
            'product_id' => $order->product_id,
            'user_id' => $userId,
            'order_id' => $order->id,
            'rating' => (int) $validated['rating'],
            'content' => trim((string) $validated['content']),
            'status' => 'PENDING_APPROVAL',
            'approved_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'code' => 'REVIEW_SUBMITTED',
            'message' => 'Review submitted and pending moderation',
            'data' => [
                'id' => (int) $review->id,
                'status' => (string) $review->status,
            ],
        ]);
    }

    public function productReviews(string $slug, Request $request): JsonResponse
    {
        $perPage = max(1, min((int) $request->integer('per_page', 20), 100));

        $product = Product::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if ($product === null) {
            return response()->json([
                'success' => false,
                'code' => 'PRODUCT_NOT_FOUND',
                'message' => 'Product not found',
                'data' => null,
            ], 404);
        }

        $reviews = Review::query()
            ->with('user:id,name')
            ->where('product_id', $product->id)
            ->where('status', 'APPROVED')
            ->orderByDesc('approved_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'code' => 'PRODUCT_REVIEWS_FOUND',
            'message' => 'Product reviews loaded',
            'data' => [
                'product' => [
                    'id' => (int) $product->id,
                    'name' => (string) $product->name,
                    'slug' => (string) $product->slug,
                ],
                'items' => $reviews->getCollection()->map(static fn (Review $review): array => [
                    'id' => (int) $review->id,
                    'rating' => (int) $review->rating,
                    'content' => (string) $review->content,
                    'approved_at' => $review->approved_at?->toISOString(),
                    'user' => [
                        'id' => (int) ($review->user?->id ?? 0),
                        'name' => (string) ($review->user?->name ?? 'Guest'),
                    ],
                ])->values(),
                'meta' => [
                    'current_page' => $reviews->currentPage(),
                    'per_page' => $reviews->perPage(),
                    'last_page' => $reviews->lastPage(),
                    'total' => $reviews->total(),
                ],
            ],
        ]);
    }
}
