<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_amount');
        $totalOrders = Order::count();
        $pendingOrders = Order::whereIn('order_status', ['pending_payment', 'processing', 'ready_for_pickup'])->count();
        $totalCustomers = User::where('role', 'customer')->count();

        $recentOrders = Order::with(['user', 'items'])->latest()->take(6)->get();

        $lowStockProducts = Product::with(['variants'])
            ->where('is_active', true)
            ->where('stock', '<=', 10)
            ->take(5)
            ->get();

        $popularProducts = Product::with('brand')
            ->where('is_active', true)
            ->orderBy('rating_avg', 'desc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalRevenue',
            'totalOrders',
            'pendingOrders',
            'totalCustomers',
            'recentOrders',
            'lowStockProducts',
            'popularProducts'
        ));
    }
}

