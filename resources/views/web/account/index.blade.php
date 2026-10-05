@extends('layouts.app')

@section('title', 'Akun Saya - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6 pb-6 sm:pb-8">
    <!-- Unified Luxury Account Header & Tabs -->
    @include('web.account.header')

    <!-- Quick Stat KPI Metrics (4 Columns) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
        <!-- Metric 1: Total Orders -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-3 sm:p-5 border border-gray-200/90 shadow-2xs hover:shadow-sm transition group">
            <div class="flex items-center justify-between text-gray-400 mb-1.5 sm:mb-2">
                <span class="text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Pesanan</span>
                <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-[#650506]/5 text-[#650506] flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-900 font-mono">{{ number_format($stats['total_orders']) }}</div>
            <a href="{{ route('account.orders') }}" class="text-[10px] sm:text-[11px] font-semibold text-[#650506] hover:underline mt-1.5 sm:mt-2 inline-block">
                Lihat semua pesanan &rarr;
            </a>
        </div>

        <!-- Metric 2: Pending Orders -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-3 sm:p-5 border border-gray-200/90 shadow-2xs hover:shadow-sm transition group">
            <div class="flex items-center justify-between text-gray-400 mb-1.5 sm:mb-2">
                <span class="text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Perlu Diproses</span>
                <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-900 font-mono">{{ number_format($stats['pending_orders']) }}</div>
            <a href="{{ route('account.orders', ['status' => 'processing']) }}" class="text-[10px] sm:text-[11px] font-semibold text-amber-700 hover:underline mt-1.5 sm:mt-2 inline-block">
                Pesanan aktif &rarr;
            </a>
        </div>

        <!-- Metric 3: Reward Points -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-3 sm:p-5 border border-gray-200/90 shadow-2xs hover:shadow-sm transition group">
            <div class="flex items-center justify-between text-gray-400 mb-1.5 sm:mb-2">
                <span class="text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Poin Loyalty</span>
                <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-900 font-mono">{{ number_format($stats['points']) }}</div>
            <a href="{{ route('account.rewards') }}" class="text-[10px] sm:text-[11px] font-semibold text-emerald-700 hover:underline mt-1.5 sm:mt-2 inline-block">
                Tukar voucher diskon &rarr;
            </a>
        </div>

        <!-- Metric 4: Riwayat Poin -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-3 sm:p-5 border border-gray-200/90 shadow-2xs hover:shadow-sm transition group">
            <div class="flex items-center justify-between text-gray-400 mb-1.5 sm:mb-2">
                <span class="text-[10px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider">Riwayat Poin</span>
                <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-gray-900 font-mono">{{ number_format($stats['point_history_count'] ?? 0) }}</div>
            <a href="{{ route('account.points') }}" class="text-[10px] sm:text-[11px] font-semibold text-amber-700 hover:underline mt-1.5 sm:mt-2 inline-block">
                Lihat transaksi poin &rarr;
            </a>
        </div>
    </div>

    <!-- Main Content 2-Columns (Recent Orders + Sidebar Info) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
        <!-- Left: Recent Orders (2 Cols) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl sm:rounded-2xl p-3.5 sm:p-6 lg:p-7 border border-gray-200/90 shadow-2xs space-y-3.5 sm:space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 sm:pb-4 gap-2">
                    <div>
                        <h3 class="font-serif font-bold text-gray-900 text-base sm:text-lg">Pesanan Terkini</h3>
                        <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Pantau status transaksi dan pengiriman parfum Anda.</p>
                    </div>
                    <a href="{{ route('account.orders') }}" class="text-xs sm:text-sm font-semibold text-[#650506] hover:text-[#4A070B] inline-flex items-center gap-1 group shrink-0 transition-colors">
                        <span>Lihat Semua ({{ $stats['total_orders'] }})</span>
                        <span class="inline-block transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
                    </a>
                </div>

                <div class="divide-y divide-gray-100">
                    @forelse($recentOrders as $order)
                        @php
                            $statusStyles = [
                                'pending_payment' => 'bg-amber-50 text-amber-800 border-amber-200',
                                'processing' => 'bg-blue-50 text-blue-800 border-blue-200',
                                'shipped' => 'bg-purple-50 text-purple-800 border-purple-200',
                                'ready_for_pickup' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                                'delivered' => 'bg-teal-50 text-teal-800 border-teal-200',
                                'completed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                'cancelled' => 'bg-rose-50 text-rose-800 border-rose-200',
                            ];
                            $style = $statusStyles[$order->order_status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                            $statusLabel = match($order->order_status) {
                                'pending_payment' => 'Menunggu Pembayaran',
                                'processing' => 'Sedang Diproses',
                                'shipped' => 'Dalam Pengiriman',
                                'ready_for_pickup' => 'Siap Diambil di Butik',
                                'delivered' => 'Terkirim',
                                'completed' => 'Selesai',
                                'cancelled' => 'Dibatalkan',
                                default => strtoupper(str_replace('_', ' ', $order->order_status))
                            };
                            $firstItem = $order->items->first();
                        @endphp
                        <div class="py-3.5 sm:py-4.5 first:pt-1 last:pb-1 space-y-2.5 sm:space-y-3">
                            @php
                                $invoiceNumber = 'INV/' . $order->created_at->format('dmy') . '/AP/' . str_replace('AP-', '', $order->order_number);
                            @endphp
                            <!-- Top Row: No Invoice di kiri atas & Tanggal + Status di kanan -->
                            <div class="flex flex-wrap items-center justify-between gap-2 pb-2.5 border-b border-gray-100 text-xs">
                                <span class="font-bold text-gray-900 text-xs sm:text-[13px] font-mono select-all">
                                    {{ $invoiceNumber }}
                                </span>

                                <div class="flex items-center gap-2 sm:gap-3">
                                    <span class="text-gray-400 text-[11px] sm:text-xs">{{ $order->created_at->translatedFormat('d M Y - H:i') }} WIB</span>
                                    <span class="px-2.5 py-0.5 text-[10px] sm:text-xs font-semibold rounded-full border {{ $style }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                            </div>

                            @if($firstItem)
                            <!-- Middle Row: Produk di kiri & Total Belanja di kanan -->
                            <div class="flex items-center justify-between gap-3 sm:gap-4 py-1">
                                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                                    <img src="{{ $firstItem->product?->images?->first()?->image_url ?? 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=120&q=80' }}" 
                                         alt="{{ $firstItem->product?->name ?? 'Produk' }}" 
                                         class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl object-cover bg-stone-50 border border-gray-200 shrink-0">
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-xs sm:text-sm text-gray-900 truncate">{{ $firstItem->product?->name ?? 'Produk Aroma Palace' }}</h4>
                                        <p class="text-[11px] text-gray-500 mt-0.5">
                                            {{ $firstItem->quantity }} Pcs x Rp {{ number_format($firstItem->price, 0, ',', '.') }}
                                            @if($order->items->count() > 1)
                                                <span class="text-gray-400 font-medium block sm:inline sm:ml-1">(+{{ $order->items->count() - 1 }} lainnya)</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="text-xs text-gray-400 block font-medium">Total Belanja</span>
                                    <span class="text-sm sm:text-base font-bold text-gray-900 font-mono block mt-0.5">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            @endif

                            <!-- Bottom Row: Tombol Lihat Detail Transaksi di kanan -->
                            <div class="flex items-center justify-end pt-2.5 border-t border-gray-100">
                                <button type="button" onclick="openTransactionModal('{{ $order->order_number }}')" 
                                   class="px-4 py-2 rounded-lg border border-gray-300 hover:border-gray-400 hover:bg-gray-50 text-gray-900 font-bold text-xs shadow-2xs transition cursor-pointer">
                                    <span>Lihat Detail Transaksi</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 sm:py-12 space-y-3">
                            <div class="w-12 h-12 sm:w-14 sm:h-14 mx-auto rounded-full bg-[#650506]/5 text-[#650506] flex items-center justify-center">
                                <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            </div>
                            <h4 class="font-bold text-sm text-gray-900">Belum Ada Transaksi</h4>
                            <p class="text-xs text-gray-500 max-w-sm mx-auto">Temukan koleksi wewangian niche eksklusif dari desainer dunia untuk melengkapi pesona Anda.</p>
                            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2 sm:py-2.5 rounded-xl bg-[#650506] text-white font-bold text-xs hover:bg-[#4A070B] transition shadow-sm mt-2">
                                <span>Jelajahi Katalog Parfum</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Sidebar Information (1 Col) -->
        <div class="space-y-4 sm:space-y-6">
            <!-- Primary Address Card -->
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-200/90 shadow-2xs space-y-3 sm:space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 sm:pb-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#650506]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <h4 class="font-bold text-gray-900 text-sm">Alamat Utama</h4>
                    </div>
                    <a href="{{ route('account.addresses') }}" class="text-xs font-semibold text-[#650506] hover:underline">
                        {{ $primaryAddress ? 'Kelola' : '+ Tambah' }}
                    </a>
                </div>

                @if($primaryAddress)
                    <div class="bg-stone-50/70 p-3 sm:p-4 rounded-xl border border-gray-200/80 space-y-1.5 sm:space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-900 text-xs sm:text-sm">{{ $primaryAddress->recipient_name }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#650506]/10 text-[#650506]">
                                {{ $primaryAddress->label ?? 'Utama' }}
                            </span>
                        </div>
                        <p class="text-gray-500 font-mono text-[11px] sm:text-xs">{{ $primaryAddress->phone_number }}</p>
                        <p class="text-gray-700 leading-relaxed text-[11px] sm:text-xs">
                            {{ $primaryAddress->full_address }}<br>
                            {{ $primaryAddress->city }}{{ $primaryAddress->postal_code ? ', ' . $primaryAddress->postal_code : '' }}
                        </p>
                    </div>
                @else
                    <div class="text-center py-5 sm:py-6 bg-stone-50 rounded-xl border border-dashed border-gray-300 p-3 sm:p-4">
                        <p class="text-xs text-gray-500">Belum ada alamat pengiriman utama yang tersimpan.</p>
                        <a href="{{ route('account.addresses') }}" class="inline-block mt-2.5 sm:mt-3 px-3 sm:px-3.5 py-1.5 rounded-lg bg-[#650506] text-white text-xs font-bold hover:bg-[#4A070B] transition">
                            + Tambah Alamat Baru
                        </a>
                    </div>
                @endif
            </div>

            <!-- Member Benefits Card -->
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-200/90 shadow-2xs space-y-3 sm:space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 sm:pb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-amber-500 text-base">★</span>
                        <h4 class="font-bold text-gray-900 text-sm">Keuntungan Membership</h4>
                    </div>
                    <a href="{{ route('account.rewards') }}" class="text-xs font-semibold text-[#650506] hover:underline">
                        Lihat Reward &rarr;
                    </a>
                </div>

                <ul class="space-y-2 sm:space-y-2.5 text-[11px] sm:text-xs text-gray-600">
                    @forelse($membershipStatus['benefits'] ?? $membershipStatus['my_benefits'] as $benefit)
                        <li class="flex items-start gap-2 sm:gap-2.5">
                            <span class="text-emerald-600 font-bold mt-0.5">✓</span>
                            <span class="leading-relaxed">{{ $benefit }}</span>
                        </li>
                    @empty
                        <li class="text-gray-400">Keuntungan membership aktif untuk seluruh transaksi Anda.</li>
                    @endforelse
                </ul>
            </div>

            <!-- Concierge & Customer Care Support -->
            <div class="bg-stone-50 rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-200/80 space-y-3 sm:space-y-3.5">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-[#650506] text-white flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    <div>
                        <h5 class="font-bold text-xs text-gray-900 uppercase tracking-wider">Fragrance Concierge</h5>
                        <p class="text-[10px] sm:text-[11px] text-gray-500">Butik Bantuan & Konsultasi Aroma</p>
                    </div>
                </div>
                <p class="text-[11px] sm:text-xs text-gray-600 leading-relaxed">
                    Butuh rekomendasi wewangian atau bantuan dengan pesanan Anda? Tim Beauty Advisor kami siap melayani.
                </p>
                <div class="pt-1.5 sm:pt-2 flex items-center gap-2">
                    <a href="https://wa.me/6281234567890" target="_blank" class="flex-1 text-center py-1.5 sm:py-2 px-2.5 sm:px-3 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition flex items-center justify-center gap-1.5">
                        <span>WhatsApp Concierge</span>
                    </a>
                    <a href="{{ route('stores.index') }}" class="px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-white text-xs font-semibold transition">
                        Lokasi Butik
                    </a>
                </div>
            </div>

            <!-- Tombol Keluar dari Akun (Sidebar Footer) -->
            <form method="POST" action="{{ route('logout') }}" class="pt-0.5">
                @csrf
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl border border-gray-200 hover:border-rose-300 bg-white hover:bg-rose-50/70 text-gray-500 hover:text-rose-600 text-xs font-semibold transition flex items-center justify-center gap-2 cursor-pointer shadow-2xs">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Keluar dari Akun</span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Include Transaction Details Modal Popup Component -->
@include('web.account.partials.transaction-modal')
@endsection
