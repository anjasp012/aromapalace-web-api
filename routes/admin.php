<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BeautyArticleController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

// Admin Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Admin Protected Routes
Route::middleware(['auth', EnsureUserIsAdmin::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Products Management
    Route::resource('products', ProductController::class)->except(['show']);

    // Orders Management
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update_status');
    Route::post('/orders/{id}/kiriminaja-awb', [OrderController::class, 'generateKiriminAjaAwb'])->name('orders.kiriminaja_awb');

    // Promotions & Vouchers
    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/promotions/create', [PromotionController::class, 'create'])->name('promotions.create');
    Route::post('/promotions', [PromotionController::class, 'store'])->name('promotions.store');
    Route::patch('/promotions/{id}/toggle', [PromotionController::class, 'toggleActive'])->name('promotions.toggle');
    Route::delete('/promotions/{id}', [PromotionController::class, 'destroy'])->name('promotions.destroy');

    // Stores Management
    Route::resource('stores', StoreController::class)->except(['show']);

    // Beauty Articles Management
    Route::resource('articles', BeautyArticleController::class)->except(['show']);
});

