<?php

use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BeautyContentController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use  App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

// Health & Info Endpoint
Route::get('/', function () {
    return response()->json([
        'name' => 'Aroma Palace Web & Mobile API',
        'version' => '1.0.0',
        'status' => 'operational',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// Standarisasi API v1 (Mendukung Web, Android, iOS)
$registerV1Routes = function () {
    // 1. Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    // 2. Home Screen (Banners, Rekomendasi, Populer, Kategori, Diskon, Brands)
    Route::get('/home', [HomeController::class, 'index']);

    // 3 & 4. Product Catalog & Details
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/categories', [ProductController::class, 'categories']);
        Route::get('/brands', [ProductController::class, 'brands']);
        Route::get('/{slug_or_id}', [ProductController::class, 'show']);
        Route::get('/{productId}/reviews', [ReviewController::class, 'index']);
    });

    // 11. Store Locator (PostGIS Geo Distance & Detail)
    Route::prefix('stores')->group(function () {
        Route::get('/', [StoreController::class, 'index']);
        Route::get('/{id}', [StoreController::class, 'show']);
    });

    // 10. Delivery & Kurir Options (KiriminAja SDK)
    Route::get('/delivery/options', [DeliveryController::class, 'options']);
    Route::get('/delivery/provinces', [DeliveryController::class, 'provinces']);
    Route::get('/delivery/cities', [DeliveryController::class, 'cities']);
    Route::get('/delivery/districts', [DeliveryController::class, 'districts']);

    // 12. Promotions & Vouchers
    Route::prefix('promotions')->group(function () {
        Route::get('/', [PromotionController::class, 'index']);
        Route::get('/vouchers', [PromotionController::class, 'vouchers']);
        Route::get('/exclusive-offers', [PromotionController::class, 'exclusiveOffers']);
        Route::post('/validate', [PromotionController::class, 'validateCode']);
        Route::get('/{code}', [PromotionController::class, 'show']);
    });

    // 16. Beauty Content & Tips
    Route::prefix('beauty-content')->group(function () {
        Route::get('/topics', [BeautyContentController::class, 'topics']);
        Route::get('/articles', [BeautyContentController::class, 'articles']);
        Route::get('/trends', [BeautyContentController::class, 'trends']);
        Route::get('/articles/{slug}', [BeautyContentController::class, 'show']);
    });

    // 17. Search Autocomplete Suggestions
    Route::get('/search/suggestions', [SearchController::class, 'suggestions']);

    // 8. Payment & Shipping Webhook Callback (Public for Payment Gateway & Courier Aggregator)
    Route::post('/payments/webhook', [PaymentController::class, 'webhook']);
    Route::post('/payments/pakasir/webhook', [PaymentController::class, 'pakasirWebhook']);
    Route::post('/shipping/kiriminaja/webhook', [DeliveryController::class, 'kiriminajaWebhook']);
    Route::post('/payments/simulate/{orderNumber}', [PaymentController::class, 'simulatePay']);
    Route::get('/payments/status/{orderNumber}', [PaymentController::class, 'status']);

    // ==========================================
    // PROTECTED ROUTES (Requires Sanctum Bearer Token)
    // ==========================================
    Route::middleware('auth:sanctum')->group(function () {
        // User Profile & Transactions History
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::put('/', [ProfileController::class, 'update']);
            Route::post('/change-password', [ProfileController::class, 'changePassword']);
            Route::get('/transactions', [ProfileController::class, 'transactionHistory']);
        });

        // Alamat Pengiriman
        Route::prefix('addresses')->group(function () {
            Route::get('/', [AddressController::class, 'index']);
            Route::post('/', [AddressController::class, 'store']);
            Route::get('/{id}', [AddressController::class, 'show']);
            Route::put('/{id}', [AddressController::class, 'update']);
            Route::delete('/{id}', [AddressController::class, 'destroy']);
            Route::patch('/{id}/primary', [AddressController::class, 'setPrimary']);
        });

        // 5. Wishlist
        Route::prefix('wishlist')->group(function () {
            Route::get('/', [WishlistController::class, 'index']);
            Route::post('/', [WishlistController::class, 'store']);
            Route::delete('/{productId}', [WishlistController::class, 'destroy']);
            Route::post('/{productId}/move-to-cart', [WishlistController::class, 'moveToCart']);
            Route::post('/sync', [WishlistController::class, 'sync']);
        });

        // 6. Shopping Cart
        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index']);
            Route::post('/', [CartController::class, 'store']);
            Route::put('/{itemId}', [CartController::class, 'update']);
            Route::delete('/{itemId}', [CartController::class, 'destroy']);
            Route::post('/apply-promo', [CartController::class, 'applyPromo']);
            Route::delete('/promo/remove', [CartController::class, 'removePromo']);
            Route::delete('/clear', [CartController::class, 'clear']);
        });

        // 7. Checkout
        Route::prefix('checkout')->group(function () {
            Route::post('/preview', [CheckoutController::class, 'preview']);
            Route::post('/process', [CheckoutController::class, 'process']);
        });

        // 9. Order Management
        Route::prefix('orders')->group(function () {
            Route::get('/', [OrderController::class, 'index']);
            Route::get('/{orderNumber}', [OrderController::class, 'show']);
            Route::get('/{orderNumber}/tracking', [OrderController::class, 'tracking']);
            Route::post('/{orderNumber}/cancel', [OrderController::class, 'cancel']);
        });

        // 10. Store Pickup Status
        Route::get('/pickup/{orderNumber}/status', [DeliveryController::class, 'pickupStatus']);

        // 13. Loyalty / Membership
        Route::prefix('membership')->group(function () {
            Route::get('/status', [MembershipController::class, 'status']);
            Route::get('/point-history', [MembershipController::class, 'pointHistory']);
            Route::get('/rewards', [MembershipController::class, 'rewards']);
            Route::post('/rewards/{rewardId}/redeem', [MembershipController::class, 'redeemReward']);
        });

        // 14. Product Reviews
        Route::post('/reviews', [ReviewController::class, 'store']);

        // 15. Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
        });

        // 17. Search History
        Route::prefix('search/history')->group(function () {
            Route::get('/', [SearchController::class, 'recentSearches']);
            Route::delete('/{id}', [SearchController::class, 'deleteHistory']);
            Route::delete('/', [SearchController::class, 'clearHistory']);
        });
    });
};

// Daftarkan route untuk domain api.aromapalace.test (Mobile Flutter) dan prefix /api/v1
Route::domain('api.aromapalace.test')->group(function () use ($registerV1Routes) {
    Route::group([], $registerV1Routes);
    Route::prefix('v1')->group($registerV1Routes);
});
Route::prefix('api/v1')->group($registerV1Routes);
Route::prefix('v1')->group($registerV1Routes);