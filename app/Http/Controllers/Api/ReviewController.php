<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends BaseApiController
{
    /**
     * Daftar review untuk produk tertentu beserta rata-rata dan breakdown
     */
    public function index(int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $reviews = ProductReview::with('user:id,name,avatar')
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->latest()
            ->paginate(15);

        // Breakdown rating 1-5 bintang
        $breakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $allRatings = ProductReview::where('product_id', $productId)->where('is_approved', true)->pluck('rating');
        foreach ($allRatings as $r) {
            if (isset($breakdown[$r])) {
                $breakdown[$r]++;
            }
        }

        return $this->sendResponse([
            'product_id' => $productId,
            'rating_average' => $product->rating_avg,
            'total_reviews' => $product->reviews_count,
            'breakdown' => $breakdown,
            'reviews' => $reviews,
        ], 'Daftar ulasan produk.');
    }

    /**
     * Submit review baru untuk produk setelah pembelian
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id' => 'nullable|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:5|max:1000',
            'images' => 'nullable|array',
            'images.*' => 'string', // URLs
        ]);

        $user = $request->user();
        $productId = $validated['product_id'];

        // Cek apakah user pernah membeli produk ini dan order sudah selesai (completed)
        $hasCompletedOrder = Order::where('user_id', $user->id)
            ->where('order_status', 'completed')
            ->whereHas('items', fn($q) => $q->where('product_id', $productId))
            ->exists();

        // Cek apakah sudah pernah review produk ini sebelumnya
        $existingReview = ProductReview::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($existingReview) {
            return $this->sendError('Anda sudah memberikan ulasan untuk produk ini sebelumnya.', [], 422);
        }

        return DB::transaction(function () use ($user, $validated, $productId, $hasCompletedOrder) {
            $review = ProductReview::create([
                'product_id' => $productId,
                'user_id' => $user->id,
                'order_id' => $validated['order_id'] ?? null,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'images' => $validated['images'] ?? null,
                'is_verified_purchase' => $hasCompletedOrder,
                'is_approved' => true,
            ]);

            // Update rating_avg dan reviews_count pada Product
            $product = Product::findOrFail($productId);
            $newCount = ProductReview::where('product_id', $productId)->where('is_approved', true)->count();
            $newAvg = (float) ProductReview::where('product_id', $productId)->where('is_approved', true)->avg('rating');

            $product->update([
                'reviews_count' => $newCount,
                'rating_avg' => round($newAvg, 2),
            ]);

            return $this->sendResponse($review->load('user:id,name,avatar'), 'Terima kasih! Ulasan Anda berhasil dikirim.', 201);
        });
    }
}

