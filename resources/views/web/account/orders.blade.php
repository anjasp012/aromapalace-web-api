@extends('layouts.app')

@section('title', 'Riwayat Pesanan - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6 pb-6 sm:pb-8">
    
    <!-- Unified Luxury Account Header & Tabs -->
    @include('web.account.header')

    @php
        $ordersMeta = $orders->map(function($o) {
            $inv = 'INV/' . $o->created_at->format('dmy') . '/AP/' . str_replace('AP-', '', $o->order_number);
            $itemsText = $o->items->pluck('product_name')->join(' ');
            return [
                'id' => $o->id,
                'status' => $o->order_status,
                'search' => strtolower($inv . ' ' . $o->order_number . ' ' . ($o->tracking_number ?? '') . ' ' . $itemsText),
            ];
        })->values();
    @endphp

    <!-- Alpine.js Instant Client-Side Zero-Reload Tabs & Search -->
    <div x-data="{
        activeTab: new URLSearchParams(window.location.search).get('status') || '{{ $status ?: 'all' }}',
        searchQuery: '',
        ordersMeta: @js($ordersMeta),
        setTab(tab) {
            this.activeTab = tab;
            const newUrl = tab === 'all' 
                ? window.location.pathname 
                : window.location.pathname + '?status=' + tab;
            window.history.replaceState({}, '', newUrl);
        },
        isOrderVisible(status, searchTerms) {
            const matchesTab = (this.activeTab === 'all' || this.activeTab === status);
            if (!matchesTab) return false;
            const q = this.searchQuery.toLowerCase().trim();
            if (!q) return true;
            return searchTerms.toLowerCase().includes(q);
        },
        countVisible() {
            const q = this.searchQuery.toLowerCase().trim();
            return this.ordersMeta.filter(o => {
                const tabMatch = (this.activeTab === 'all' || this.activeTab === o.status);
                if (!tabMatch) return false;
                if (!q) return true;
                return o.search.includes(q);
            }).length;
        }
    }" class="space-y-4">

        <!-- Status Filter Buttons Bar & Search Input -->
        <div class="flex items-center justify-between gap-3 flex-wrap pt-1 sm:pt-2">
            <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap text-[11px] sm:text-xs font-medium">
                @php
                    $tabDefinitions = [
                        'all' => 'Semua Pesanan',
                        'pending_payment' => 'Menunggu Pembayaran',
                        'processing' => 'Diproses',
                        'shipped' => 'Dalam Pengiriman',
                        'delivered' => 'Tiba di Tujuan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ];
                @endphp
                @foreach($tabDefinitions as $key => $label)
                    <button type="button"
                            @click="setTab('{{ $key }}')"
                            :class="activeTab === '{{ $key }}' ? 'bg-[#650506] text-white border-[#650506] shadow-xs font-bold' : 'bg-white text-gray-700 border-gray-200 hover:border-[#650506] hover:text-[#650506]'"
                            class="px-3 sm:px-3.5 py-1.5 rounded-lg border transition text-[11px] sm:text-xs font-medium cursor-pointer">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <!-- Search Input Box (Menggantikan Total Pesanan) -->
            <div class="relative w-full sm:w-64 lg:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <input type="text"
                       x-model="searchQuery"
                       placeholder="Cari no. invoice, resi, produk..."
                       class="w-full pl-8.5 pr-8 py-1.5 rounded-lg border border-gray-200 bg-white text-xs text-gray-900 placeholder-gray-400 focus:outline-none focus:border-[#650506] focus:ring-1 focus:ring-[#650506] transition shadow-2xs">
                <button type="button"
                        x-show="searchQuery.length > 0"
                        @click="searchQuery = ''"
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer text-xs"
                        aria-label="Hapus pencarian">
                    &times;
                </button>
            </div>
        </div>

        <!-- Orders List Container -->
        <div class="space-y-3 sm:space-y-4">
            @forelse($orders as $order)
                @php
                    $firstItem = $order->items->first();
                    $otherCount = $order->items->count() - 1;
                    $invoiceNumber = 'INV/' . $order->created_at->format('dmy') . '/AP/' . str_replace('AP-', '', $order->order_number);
                    $productNames = $order->items->pluck('product_name')->join(' ');
                    $searchTerms = strtolower($invoiceNumber . ' ' . $order->order_number . ' ' . ($order->tracking_number ?? '') . ' ' . $productNames);

                    $statusStyles = [
                        'pending_payment' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'processing' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'shipped' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'delivered' => 'bg-teal-50 text-teal-700 border-teal-200',
                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'cancelled' => 'bg-rose-50 text-rose-600 border-rose-200',
                    ];
                    $style = $statusStyles[$order->order_status] ?? 'bg-stone-50 text-stone-700 border-stone-200';
                    
                    $statusLabels = [
                        'pending_payment' => 'Menunggu Pembayaran',
                        'processing' => 'Diproses',
                        'shipped' => 'Dalam Pengiriman',
                        'delivered' => 'Tiba di Tujuan',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                    ];
                    $label = $statusLabels[$order->order_status] ?? strtoupper(str_replace('_', ' ', $order->order_status));
                @endphp
                <div data-order-status="{{ $order->order_status }}"
                     x-show="isOrderVisible('{{ $order->order_status }}', @js($searchTerms))"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-200 shadow-2xs space-y-4 hover:shadow-xs transition">
                    
                    <!-- 1. Top Header: No. Invoice di kiri atas & Tanggal + Status di kanan -->
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-100 text-xs">
                        <!-- Bagian Kiri Atas: No Invoice -->
                        <div class="flex items-center gap-2">
                            <span class="font-mono font-bold text-gray-900 text-xs sm:text-[13px] select-all">
                                {{ $invoiceNumber }}
                            </span>
                        </div>

                        <!-- Bagian Kanan Atas: Tanggal & Badge Status -->
                        <div class="flex items-center gap-2 sm:gap-3">
                            <span class="text-gray-400 text-[11px] sm:text-xs">
                                {{ $order->created_at->translatedFormat('d M Y - H:i') }} WIB
                            </span>
                            <span class="px-2.5 py-0.5 text-[10px] sm:text-xs font-semibold rounded-full border {{ $style }}">
                                {{ $label }}
                            </span>
                        </div>
                    </div>

                    <!-- 2. Middle Row: Foto & Detail Produk di kiri, Total Belanja di kanan -->
                    <div class="flex items-center justify-between gap-4 py-1">
                        <!-- Kiri: Foto Produk + Nama + Qty x Harga -->
                        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                            @if($firstItem)
                                <img src="{{ $firstItem->product_image ?? ($firstItem->product?->primary_image ?? ($firstItem->product?->images?->first()?->image_url ?? 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=150&q=80')) }}"
                                     alt="{{ $firstItem->product_name ?? 'Produk' }}"
                                     class="w-16 h-16 sm:w-18 sm:h-18 rounded-xl object-cover border border-gray-200 bg-stone-50 shrink-0">
                                <div class="min-w-0">
                                    <h4 class="font-bold text-xs sm:text-sm text-gray-900 line-clamp-1 sm:line-clamp-2">
                                        {{ $firstItem->product_name ?? ($firstItem->product?->name ?? 'Produk Aroma Palace') }}
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ $firstItem->quantity }} Pcs x Rp {{ number_format($firstItem->price, 0, ',', '.') }}
                                    </p>
                                    @if($otherCount > 0)
                                        <p class="text-[11px] text-gray-400 mt-0.5">
                                            +{{ $otherCount }} produk lainnya
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Kanan: Total Belanja -->
                        <div class="text-right shrink-0">
                            <span class="text-xs text-gray-400 block font-medium">Total Belanja</span>
                            <span class="text-base sm:text-lg font-bold text-gray-900 font-mono block mt-0.5">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- 3. Bottom Row: Tombol Lihat Detail Transaksi di kanan -->
                    <div class="flex items-center justify-end pt-3 border-t border-gray-100">
                        <button type="button" onclick="openTransactionModal('{{ $order->order_number }}')"
                                class="px-4 py-2 rounded-lg border border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-900 font-bold text-xs shadow-2xs transition cursor-pointer">
                            Lihat Detail Transaksi
                        </button>
                    </div>

                </div>
            @empty
                <div class="text-center py-12 sm:py-16 bg-white rounded-xl sm:rounded-2xl border border-gray-200 p-6 sm:p-8 space-y-3">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto rounded-full bg-stone-100 text-[#650506] flex items-center justify-center">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-gray-900">Belum Ada Pesanan</h3>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">Anda belum pernah melakukan transaksi di Aroma Palace.</p>
                    <div class="pt-2">
                        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg bg-[#650506] hover:bg-[#4A070B] text-white text-xs font-semibold shadow-xs transition">
                            <span>Mulai Belanja</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            @endforelse

            <!-- Empty State with Alpine (Saat tidak ada pesanan di tab / hasil pencarian kosong) -->
            <div x-show="countVisible() === 0 && {{ $orders->count() }} > 0"
                 x-cloak
                 class="text-center py-12 sm:py-16 bg-white rounded-xl sm:rounded-2xl border border-gray-200 p-6 sm:p-8 space-y-3">
                <div class="w-12 h-12 sm:w-16 sm:h-16 mx-auto rounded-full bg-stone-100 text-[#650506] flex items-center justify-center text-2xl">
                    <span x-text="searchQuery.trim() ? '🔍' : '📦'"></span>
                </div>
                <h3 class="text-sm sm:text-base font-bold text-gray-900" 
                    x-text="searchQuery.trim() ? 'Pesanan Tidak Ditemukan' : 'Tidak Ada Pesanan'"></h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto" 
                   x-text="searchQuery.trim() ? `Tidak ada pesanan yang cocok dengan kata kunci '${searchQuery}' pada filter ini.` : 'Tidak ada transaksi di tab status ini. Anda dapat beralih ke tab status lain atau melihat semua pesanan.'">
                </p>
                <div class="pt-2 flex items-center justify-center gap-2">
                    <button type="button" x-show="searchQuery.trim()" @click="searchQuery = ''"
                            class="inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold shadow-xs transition cursor-pointer">
                        <span>Hapus Pencarian</span>
                    </button>
                    <button type="button" @click="setTab('all')"
                            class="inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 rounded-lg bg-[#650506] hover:bg-[#4A070B] text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                        <span>Lihat Semua Pesanan</span>
                        <span>&rarr;</span>
                    </button>
                </div>
            </div>

            <!-- Pagination (jika lebih dari 100 pesanan) -->
            @if($orders->hasPages())
                <div class="pt-4">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

    </div>

</div>

<!-- Include Transaction Details Modal Popup Component -->
@include('web.account.partials.transaction-modal')
@endsection
