@extends('layouts.admin')

@section('title', 'Dashboard Ringkasan Bisnis')

@section('content')
<div class="space-y-8">
    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Revenue Card -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Penjualan</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</h3>
                <span class="text-xs text-emerald-600 font-medium flex items-center gap-1 mt-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Dari seluruh transaksi lunas
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Pesanan</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ number_format($totalOrders) }}</h3>
                <span class="text-xs text-amber-600 font-medium mt-1 inline-block">{{ $pendingOrders }} menunggu penanganan</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            </div>
        </div>

        <!-- Customers Card -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Member / User</p>
                <h3 class="text-2xl font-extrabold text-gray-900 mt-1">{{ number_format($totalCustomers) }}</h3>
                <span class="text-xs text-gray-500 font-medium mt-1 inline-block">Loyalty program aktif</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Peringatan Stok</p>
                <h3 class="text-2xl font-extrabold text-rose-600 mt-1">{{ $lowStockProducts->count() }}</h3>
                <span class="text-xs text-rose-500 font-medium mt-1 inline-block">Stok tersisa &le; 10 botol</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
            <h4 class="font-bold text-gray-900">Aksi Cepat</h4>
            <p class="text-xs text-gray-500">Kelola inventaris dan promosi dengan satu klik</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Tambah Produk
            </a>
            <a href="{{ route('admin.promotions.create') }}" class="inline-flex items-center gap-2 bg-stone-800 hover:bg-stone-900 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Buat Voucher
            </a>
            <a href="{{ route('admin.stores.create') }}" class="inline-flex items-center gap-2 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-semibold px-4 py-2.5 rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                Tambah Toko
            </a>
        </div>
    </div>

    <!-- Recent Orders & Low Stock Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Orders (2 Columns) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">Pesanan Terbaru</h3>
                    <p class="text-xs text-gray-500">Aktivitas transaksi terkini yang membutuhkan tindakan</p>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-amber-600 hover:text-amber-700">Lihat Semua &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">No. Order</th>
                            <th class="px-4 py-3">Pelanggan</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Metode</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 rounded-r-lg text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentOrders as $order)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="px-4 py-3 font-semibold text-gray-900">#{{ $order->order_number }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $order->user?->name ?? 'Guest' }}</td>
                            <td class="px-4 py-3 font-semibold text-gray-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-xs uppercase text-gray-500 font-medium">{{ str_replace('_', ' ', $order->payment_method) }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full 
                                    @if($order->order_status === 'completed') bg-emerald-100 text-emerald-800
                                    @elseif($order->order_status === 'shipped') bg-blue-100 text-blue-800
                                    @elseif($order->order_status === 'ready_for_pickup') bg-purple-100 text-purple-800
                                    @elseif($order->order_status === 'processing') bg-amber-100 text-amber-800
                                    @elseif($order->order_status === 'cancelled') bg-rose-100 text-rose-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ str_replace('_', ' ', $order->order_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="text-xs font-semibold text-amber-600 hover:underline">Kelola</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada pesanan terbaru.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Alert Box (1 Column) -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-gray-900 text-lg">Peringatan Stok</h3>
                <span class="text-xs bg-rose-100 text-rose-700 font-bold px-2 py-0.5 rounded-full">Perlu Restock</span>
            </div>
            <p class="text-xs text-gray-500 mb-4">Produk berikut memiliki sisa stok yang hampir habis.</p>

            <div class="space-y-3">
                @forelse($lowStockProducts as $prod)
                <div class="p-3 bg-rose-50/50 border border-rose-100 rounded-xl flex items-center justify-between">
                    <div>
                        <h5 class="text-sm font-bold text-gray-900">{{ $prod->name }}</h5>
                        <p class="text-xs text-gray-500">Harga: Rp {{ number_format($prod->base_price, 0, ',', '.') }}</p>
                    </div>
                    <span class="px-2.5 py-1 bg-rose-600 text-white text-xs font-extrabold rounded-lg">
                        {{ $prod->stock }} pcs
                    </span>
                </div>
                @empty
                <div class="text-center py-8 text-gray-400 text-xs">
                    Semua stok produk dalam kondisi aman (&gt; 10 pcs).
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

