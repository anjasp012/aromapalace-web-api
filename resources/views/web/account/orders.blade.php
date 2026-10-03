@extends('layouts.app')

@section('title', 'Riwayat & Status Pesanan - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6 pb-8">
    
    <!-- Unified Luxury Account Header & Tabs -->
    @include('web.account.header')

    <!-- Page Title & Quick Navigation Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-1 sm:pt-2 border-b border-gray-100 pb-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-serif font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <span>Riwayat & Status Pesanan</span>
                <span class="text-xs font-sans font-medium px-2 py-0.5 rounded-full bg-stone-100 text-stone-600 border border-stone-200">
                    {{ $orders->total() }} Transaksi
                </span>
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">
                Pantau status pembayaran, tagihan transaksi, dan proses pengiriman parfum Anda.
            </p>
        </div>

        <div class="text-xs text-gray-500 flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                <span>Belum Bayar: <strong class="text-gray-900">{{ $statusCounts['pending_payment'] ?? 0 }}</strong></span>
            </span>
            <span>&bull;</span>
            <span class="inline-flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Diproses: <strong class="text-gray-900">{{ $statusCounts['processing'] ?? 0 }}</strong></span>
            </span>
        </div>
    </div>

    <!-- Status Filter Buttons Bar (Pill Tabs with Counters) -->
    <div class="flex items-center gap-2 overflow-x-auto pb-1.5 no-scrollbar text-xs">
        @php
            $currentStatus = request('status', '');
            $filterItems = [
                '' => ['label' => 'Semua Pesanan', 'count' => $statusCounts['all'] ?? $orders->total()],
                'pending_payment' => ['label' => 'Menunggu Pembayaran', 'count' => $statusCounts['pending_payment'] ?? 0, 'highlight' => true],
                'processing' => ['label' => 'Diproses', 'count' => $statusCounts['processing'] ?? 0],
                'shipped' => ['label' => 'Dikirim', 'count' => $statusCounts['shipped'] ?? 0],
                'completed' => ['label' => 'Selesai', 'count' => $statusCounts['completed'] ?? 0],
                'cancelled' => ['label' => 'Dibatalkan', 'count' => $statusCounts['cancelled'] ?? 0],
            ];
        @endphp

        @foreach($filterItems as $val => $tab)
            @php
                $isActive = ($currentStatus === $val || (!$currentStatus && $val === ''));
            @endphp
            <a href="{{ route('account.orders', $val ? ['status' => $val] : []) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all duration-150 {{ $isActive ? 'bg-[#650506] text-white shadow-sm ring-1 ring-[#650506]' : 'bg-white text-gray-700 hover:text-[#650506] hover:bg-stone-50 border border-gray-200' }}">
                <span>{{ $tab['label'] }}</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] font-mono {{ $isActive ? 'bg-white/20 text-white font-bold' : ($tab['count'] > 0 && !empty($tab['highlight']) ? 'bg-amber-100 text-amber-800 font-bold' : 'bg-gray-100 text-gray-600') }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    <!-- Orders Cards List -->
    <div class="space-y-4 sm:space-y-5">
        @forelse($orders as $order)
            @php
                $isPaid = ($order->payment_status === 'paid');
                $isPendingPayment = ($order->order_status === 'pending_payment' || $order->payment_status === 'unpaid');
                $isCancelled = ($order->order_status === 'cancelled');
                
                // Format Payment Method Label
                $methodCode = $order->payment?->payment_type ?? $order->payment_method;
                $methodLabels = [
                    'qris' => 'QRIS Instant',
                    'bri_va' => 'BRI Virtual Account',
                    'bni_va' => 'BNI Virtual Account',
                    'permata_va' => 'Permata Virtual Account',
                    'bca_va' => 'BCA Virtual Account',
                    'mandiri_va' => 'Mandiri Virtual Account',
                    'cod' => 'Cash On Delivery (COD)',
                ];
                $methodLabel = $methodLabels[$methodCode] ?? strtoupper(str_replace('_', ' ', $methodCode));

                // Order Status Config
                $orderStatusConfig = [
                    'pending_payment' => ['label' => 'Menunggu Pembayaran', 'class' => 'bg-amber-50 text-amber-900 border-amber-200'],
                    'processing' => ['label' => 'Sedang Diproses', 'class' => 'bg-blue-50 text-blue-900 border-blue-200'],
                    'shipped' => ['label' => 'Dalam Pengiriman', 'class' => 'bg-purple-50 text-purple-900 border-purple-200'],
                    'delivered' => ['label' => 'Tiba di Tujuan', 'class' => 'bg-teal-50 text-teal-900 border-teal-200'],
                    'completed' => ['label' => 'Pesanan Selesai', 'class' => 'bg-emerald-50 text-emerald-900 border-emerald-200'],
                    'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-rose-50 text-rose-900 border-rose-200'],
                ];
                $orderStatus = $orderStatusConfig[$order->order_status] ?? ['label' => strtoupper($order->order_status), 'class' => 'bg-gray-100 text-gray-700 border-gray-200'];
            @endphp

            <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs hover:shadow-md transition-all duration-200 overflow-hidden">
                
                <!-- TOP BAR: Meta Header (Order Number, Date, Fulfillment & Badges) -->
                <div class="p-4 sm:p-5 pb-3 sm:pb-4 flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-[#FCFBF9]">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-[#650506]/10 text-[#650506] flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <a href="{{ route('account.orders.show', $order->order_number) }}" class="font-bold text-gray-900 text-sm sm:text-base font-mono hover:text-[#650506] transition">
                                    #{{ $order->order_number }}
                                </a>
                                <span class="text-gray-300 text-xs hidden sm:inline">&bull;</span>
                                <span class="text-xs text-gray-500 font-sans">
                                    {{ $order->created_at->translatedFormat('d M Y, H:i') }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-gray-500 mt-0.5">
                                <span class="inline-block w-1.5 h-1.5 rounded-full {{ in_array($order->fulfillment_type, ['store_pickup', 'pickup']) ? 'bg-blue-500' : 'bg-emerald-500' }}"></span>
                                <span>{{ in_array($order->fulfillment_type, ['store_pickup', 'pickup']) ? 'Ambil di Butik (Store Pickup)' : 'Kurir ' . ($order->shipping_courier ?? 'Internal') . ($order->shipping_service ? ' (' . $order->shipping_service . ')' : '') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Dual Status Badges -->
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- Order Status Badge -->
                        <span class="px-2.5 sm:px-3 py-1 text-[11px] sm:text-xs font-semibold rounded-lg border {{ $orderStatus['class'] }}">
                            {{ $orderStatus['label'] }}
                        </span>
                    </div>
                </div>

                <!-- HERO PAYMENT & NOMINAL SHOWCASE STRIP (Utamakan Tampilan Pembayaran & Nominal) -->
                <div class="px-4 sm:px-6 py-3.5 sm:py-4 {{ $isPaid ? 'bg-gradient-to-r from-emerald-50/70 via-stone-50/40 to-emerald-50/70 border-b border-emerald-100/80' : ($isCancelled ? 'bg-gray-50/80 border-b border-gray-100' : 'bg-gradient-to-r from-amber-50/90 via-rose-50/30 to-amber-50/90 border-b border-amber-200/70') }}">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        
                        <!-- Left: Status Pembayaran & Metode -->
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-gray-500">Status Pembayaran:</span>
                                @if($isPaid)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-600 text-white shadow-xs">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        LUNAS
                                    </span>
                                @elseif($isCancelled)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-500 text-white">
                                        DIBATALKAN
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500 text-white shadow-xs">
                                        <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                                        BELUM DIBAYAR
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 text-xs text-gray-700 flex-wrap">
                                <span class="text-gray-500">Metode:</span>
                                <span class="font-bold text-gray-900 bg-white/80 px-2 py-0.5 rounded border border-gray-200/70 inline-flex items-center gap-1.5">
                                    @if(str_contains(strtolower($methodCode), 'qris'))
                                        <span class="text-[10px] font-black text-[#650506] bg-rose-50 px-1 rounded">QRIS</span>
                                    @elseif(str_contains(strtolower($methodCode), 'bri'))
                                        <span class="text-[10px] font-black text-blue-700 bg-blue-50 px-1 rounded">BRI</span>
                                    @elseif(str_contains(strtolower($methodCode), 'bni'))
                                        <span class="text-[10px] font-black text-orange-700 bg-orange-50 px-1 rounded">BNI</span>
                                    @elseif(str_contains(strtolower($methodCode), 'permata'))
                                        <span class="text-[10px] font-black text-emerald-700 bg-emerald-50 px-1 rounded">PERMATA</span>
                                    @elseif(str_contains(strtolower($methodCode), 'cod'))
                                        <span class="text-[10px] font-black text-stone-700 bg-stone-100 px-1 rounded">COD</span>
                                    @endif
                                    <span>{{ $methodLabel }}</span>
                                </span>

                                @if(!$isPaid && !$isCancelled && !empty($order->payment?->va_number))
                                    <span class="text-gray-400">&bull;</span>
                                    <span class="text-gray-500">No. VA:</span>
                                    <code class="font-mono font-bold text-gray-900 bg-white px-2 py-0.5 rounded border border-amber-300 text-xs">{{ $order->payment->va_number }}</code>
                                @endif
                            </div>
                        </div>

                        <!-- Right: Total Nominal Tagihan (Prominent Display) -->
                        <div class="text-left sm:text-right pt-2 sm:pt-0 border-t sm:border-t-0 border-gray-200/50">
                            <span class="text-[10px] sm:text-xs text-gray-500 block font-medium">Total Pembayaran</span>
                            <div class="flex items-baseline sm:justify-end gap-1">
                                <span class="text-xl sm:text-2xl lg:text-3xl font-black font-mono tracking-tight text-[#650506]">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </span>
                            </div>
                            <span class="text-[10px] text-gray-400 block mt-0.5">
                                Termasuk ongkir & potongan diskon
                            </span>
                        </div>
                    </div>
                </div>

                <!-- MIDDLE: Items Preview List -->
                <div class="p-4 sm:p-5 space-y-3">
                    @foreach($order->items as $item)
                        <div class="flex items-center justify-between gap-3 sm:gap-4">
                            <div class="flex items-center gap-3 sm:gap-4 min-w-0 flex-1">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl overflow-hidden border border-gray-200 bg-[#F4F2EE] shrink-0">
                                    <img src="{{ $item->product_image ?? ($item->product?->primary_image ?? 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=150&q=80') }}"
                                         alt="{{ $item->product_name }}"
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-xs sm:text-sm font-bold text-gray-900 truncate">
                                        {{ $item->product_name }}
                                    </h4>
                                    <p class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1.5">
                                        <span class="bg-gray-100 text-gray-700 px-1.5 py-0.2 rounded text-[10px] font-medium">{{ $item->variant_name ?? 'Default' }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $item->quantity }} &times; Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="text-right shrink-0">
                                <span class="text-xs sm:text-sm font-bold text-gray-900 font-mono">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- BOTTOM FOOTER: Resi/Pickup Info & Direct Action Buttons -->
                <div class="px-4 sm:px-6 py-3.5 sm:py-4 bg-[#FAF8F5] border-t border-gray-100 flex flex-wrap items-center justify-between gap-3">
                    
                    <!-- Left: Additional Tracking / Notes -->
                    <div class="text-xs text-gray-600 flex items-center gap-3 flex-wrap">
                        @if($order->tracking_number)
                            <div class="flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-lg border border-gray-200">
                                <span class="text-gray-400 text-[11px]">No. Resi:</span>
                                <code class="font-mono font-bold text-gray-900 text-xs">{{ $order->tracking_number }}</code>
                            </div>
                        @elseif(in_array($order->fulfillment_type, ['store_pickup', 'pickup']) && $order->pickup_code)
                            <div class="flex items-center gap-1.5 bg-white px-2.5 py-1 rounded-lg border border-gray-200">
                                <span class="text-gray-400 text-[11px]">Kode Pickup:</span>
                                <code class="font-mono font-bold text-[#650506] text-xs">{{ $order->pickup_code }}</code>
                            </div>
                        @else
                            <span class="text-gray-400 text-[11px] italic">
                                {{ $isPaid ? 'Pesanan sedang dipersiapkan butik' : 'Menunggu penyelesaian pembayaran' }}
                            </span>
                        @endif
                    </div>

                    <!-- Right: Action Buttons -->
                    <div class="flex items-center gap-2 shrink-0 ml-auto">
                        @if(!$isPaid && !$isCancelled)
                            <!-- Action: Bayar Sekarang (High Priority CTA) -->
                            <a href="{{ route('account.orders.show', $order->order_number) }}"
                               class="inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 rounded-xl bg-[#650506] hover:bg-[#4A070B] text-white font-bold text-xs shadow-sm hover:shadow transition transform active:scale-98">
                                <span>Bayar Sekarang</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </a>
                        @endif

                        <!-- Action: Detail Pesanan -->
                        <a href="{{ route('account.orders.show', $order->order_number) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 sm:px-4 py-2 rounded-xl bg-white hover:bg-stone-100 text-gray-800 border border-gray-300 font-semibold text-xs shadow-2xs transition">
                            <span>Detail Pesanan</span>
                            <span class="text-gray-400">&rarr;</span>
                        </a>
                    </div>

                </div>

            </div>
        @empty
            <!-- Luxury Empty State -->
            <div class="text-center py-12 sm:py-16 bg-white rounded-2xl border border-gray-200 p-6 sm:p-8 space-y-4">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-[#650506]/10 text-[#650506] flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-base sm:text-lg font-serif font-bold text-gray-900">Belum Ada Pesanan</h3>
                    <p class="text-xs sm:text-sm text-gray-500 max-w-sm mx-auto">
                        @if($currentStatus)
                            Tidak ada pesanan dengan status "{{ $filterItems[$currentStatus]['label'] ?? $currentStatus }}".
                        @else
                            Anda belum pernah melakukan pemesanan parfum di Aroma Palace.
                        @endif
                    </p>
                </div>
                <div class="pt-2 flex items-center justify-center gap-3">
                    @if($currentStatus)
                        <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-1 px-4 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-stone-50 text-xs font-semibold transition">
                            <span>Lihat Semua Pesanan</span>
                        </a>
                    @endif
                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl bg-[#650506] hover:bg-[#4A070B] text-white text-xs font-bold shadow-sm transition">
                        <span>Jelajahi Koleksi</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        @endforelse

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="pt-4 flex justify-center">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
