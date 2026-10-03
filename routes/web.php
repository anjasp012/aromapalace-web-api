<?php

use App\Http\Controllers\Web\AccountController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BeautyContentController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\CheckoutController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\SitemapController;
use App\Http\Controllers\Web\StoreController;
use App\Http\Controllers\Web\WishlistController;

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Storefront Routes
Route::get('/', function (Request $request) {
    if ($request->expectsJson() && !$request->hasHeader('X-Inertia') && !$request->hasHeader('X-Livewire')) {
        return response()->json([
            'name' => 'Aroma Palace Web & Mobile API',
            'version' => '1.0.0',
            'status' => 'operational',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
    return app(HomeController::class)->index();
})->name('home');

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/stores', [StoreController::class, 'index'])->name('stores.index');
Route::get('/articles', [BeautyContentController::class, 'index'])->name('articles.index');
Route::get('/articles/{slug}', [BeautyContentController::class, 'show'])->name('articles.show');

// Customer Auth Routes (Guest)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
});

// Customer Protected Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Wishlist (Wajib Login)
    Route::prefix('wishlist')->name('wishlist.')->group(function () {
        Route::get('/', [WishlistController::class, 'index'])->name('index');
        Route::post('/toggle', [WishlistController::class, 'toggle'])->name('toggle');
        Route::delete('/{productId}', [WishlistController::class, 'destroy'])->name('destroy');
        Route::post('/{productId}/move-to-cart', [WishlistController::class, 'moveToCart'])->name('moveToCart');
    });

    // Cart
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/', [CartController::class, 'store'])->name('store');
        Route::put('/{id}', [CartController::class, 'update'])->name('update');
        Route::delete('/{id}', [CartController::class, 'destroy'])->name('destroy');
        Route::post('/promo', [CartController::class, 'applyPromo'])->name('promo.apply');
        Route::delete('/promo', [CartController::class, 'removePromo'])->name('promo.remove');
    });

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/calculate', [CheckoutController::class, 'calculate'])->name('checkout.calculate');

    // Public Webhooks for Pakasir & KiriminAja
    Route::post('/payments/pakasir/webhook', [\App\Http\Controllers\Api\PaymentController::class, 'pakasirWebhook']);
    Route::post('/shipping/kiriminaja/webhook', [\App\Http\Controllers\Api\DeliveryController::class, 'kiriminajaWebhook']);

    // Account & Orders & Membership
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
        Route::get('/orders/{orderNumber}', [AccountController::class, 'orderShow'])->name('orders.show');
        Route::post('/orders/{orderNumber}/check-payment', [AccountController::class, 'orderCheckPayment'])->name('orders.check_payment');
        Route::post('/orders/{orderNumber}/simulate-payment', [AccountController::class, 'orderSimulatePayment'])->name('orders.simulate_payment');
        Route::post('/orders/{orderNumber}/cancel', [AccountController::class, 'orderCancel'])->name('orders.cancel');

        Route::get('/rewards', [AccountController::class, 'rewards'])->name('rewards');
        Route::post('/rewards/{id}/redeem', [AccountController::class, 'redeemReward'])->name('rewards.redeem');
        Route::get('/points', [AccountController::class, 'points'])->name('points');

        Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [AccountController::class, 'storeAddress'])->name('addresses.store');
        Route::post('/addresses/{id}/primary', [AccountController::class, 'setPrimaryAddress'])->name('addresses.primary');
        Route::delete('/addresses/{id}', [AccountController::class, 'deleteAddress'])->name('addresses.destroy');
    });
});
