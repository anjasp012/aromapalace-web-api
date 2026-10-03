@extends('layouts.app')

@section('title', 'Riwayat Pesanan - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6 pb-6 sm:pb-8">
    
    <!-- Unified Luxury Account Header & Tabs -->
    @include('web.account.header')

    <!-- Status Filter Buttons Bar -->
    <div class="flex items-center justify-between gap-3 flex-wrap pt-1 sm:pt-2">
        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap text-[11px] sm:text-xs font-medium">
            @php
                $currentStatus = request('status');
                $filters = [
                    '' => 'Semua Pesanan',
                    'pending_payment' => 'Menunggu Pembayaran',
                    'processing' => 'Diproses',
                    'shipped' => 'Dalam Pengiriman',
                    'delivered' => 'Tiba di Tujuan',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                ];
            @endphp
            @foreach($filters as $val => $label)
                <a href="{{ route('account.orders', $val ? ['status' => $val] : []) }}"
                   class="px-3 sm:px-3.5 py-1.5 rounded-lg border transition {{ ($currentStatus === $val || (!$currentStatus && $val === '')) ? 'bg-[#650506] text-white border-[#650506] shadow-xs font-bold' : 'bg-white text-gray-700 border-gray-200 hover:border-[#650506] hover:text-[#650506]' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="text-[11px] sm:text-xs text-gray-500 font-medium">
            Total {{ $orders->total() }} pesanan
        </div>
    </div>

    <!-- Orders List -->
    <div class="space-y-3 sm:space-y-4">
        @forelse($orders as $order)
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-200 shadow-2xs hover:shadow-sm transition">
                <!-- Header: Order Number, Date & Status -->
                <div class="flex flex-wrap items-center justify-between gap-2.5 sm:gap-4 pb-3 sm:pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-[#650506]/10 text-[#650506] flex items-center justify-center font-bold text-xs sm:text-sm">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <span class="font-bold text-gray-900 text-xs sm:text-sm font-mono">#{{ $order->order_number }}</span>
                                <span class="text-gray-400 text-xs">&bull; {{ $order->created_at->format('d M Y, H:i') }}</span>
                            </div>
                            <span class="text-[10px] sm:text-xs text-gray-500 flex items-center gap-1 mt-0.5">
                                <svg class="w-3 h-3 text-[#650506]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ in_array($order->fulfillment_type, ['store_pickup', 'pickup']) ? 'Ambil di Butik (Click & Collect)' : 'Pengiriman Kurir (' . ($order->shipping_courier ?? 'Reguler') . ')' }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        @php
                            $statusStyles = [
                                'pending_payment' => 'bg-amber-50 text-amber-900 border-amber-200',
                                'processing' => 'bg-blue-50 text-blue-900 border-blue-200',
                                'shipped' => 'bg-purple-50 text-purple-900 border-purple-200',
                                'delivered' => 'bg-teal-50 text-teal-900 border-teal-200',
                                'completed' => 'bg-emerald-50 text-emerald-900 border-emerald-200',
                                'cancelled' => 'bg-rose-50 text-rose-900 border-rose-200',
                            ];
                            $style = $statusStyles[$order->order_status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                            
                            $statusLabels = [
                                'pending_payment' => 'Menunggu Pembayaran',
                                'processing' => 'Diproses',
                                'shipped' => 'Dalam Pengiriman',
                                'delivered' => 'Tiba di Tujuan',
                                'completed' => 'Pesanan Selesai',
                                'cancelled' => 'Dibatalkan',
                            ];
                            $label = $statusLabels[$order->order_status] ?? strtoupper(str_replace('_', ' ', $order->order_status));
                        @endphp
                        <span class="px-2.5 sm:px-3 py-1 text-[10px] sm:text-xs font-semibold rounded-md border {{ $style }}">
                            {{ $label }}
                        </span>
                    </div>
                </div>

                <!-- Items Preview -->
                <div class="py-3 sm:py-4 space-y-2.5 sm:space-y-3">
                    @foreach($order->items as $item)
                        <div class="flex items-center justify-between gap-3 sm:gap-4">
                            <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
                                <img src="{{ $item->product?->images?->first()?->image_url ?? 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=150&q=80' }}"
                                     alt="{{ $item->product?->name ?? 'Produk Parfum' }}"
                                     class="w-11 h-11 sm:w-14 sm:h-14 rounded-lg sm:rounded-xl object-cover border border-gray-200 bg-[#F4F2EE] shrink-0">
                                <div class="min-w-0">
                                    <h4 class="text-xs sm:text-sm font-bold text-gray-900 truncate">{{ $item->product?->name ?? 'Parfum Eksklusif' }}</h4>
                                    <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5">
                                        {{ $item->variant?->name ?? 'Default' }} &bull; {{ $item->quantity }} &times; Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-gray-900 font-mono shrink-0">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <!-- Footer: Total & Actions -->
                <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4 pt-3 sm:pt-4 border-t border-gray-100 bg-[#FBF9F6] -mx-4 -mb-4 sm:-mx-6 sm:-mb-6 p-3.5 sm:p-5 rounded-b-xl sm:rounded-b-2xl">
                    <div>
                        <span class="text-[10px] sm:text-xs text-gray-500 block">Total Pembayaran:</span>
                        <span class="text-sm sm:text-base font-bold text-[#650506] font-mono">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        @if($order->tracking_number)
                            <span class="text-[10px] sm:text-xs text-gray-600 block mt-0.5">
                                No. Resi: <code class="bg-stone-200/70 px-1.5 py-0.5 rounded text-gray-800 font-mono">{{ $order->tracking_number }}</code>
                            </span>
                        @endif
                    </div>

                    <div>
                        <a href="{{ route('account.orders.show', $order->order_number) }}"
                           class="inline-flex items-center gap-1.5 px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-lg bg-[#650506] hover:bg-[#4A070B] text-white font-semibold text-[11px] sm:text-xs shadow-xs transition">
                            <span>Detail Pesanan</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
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
                <p class="text-xs text-gray-500 max-w-sm mx-auto">Anda belum memiliki riwayat pesanan dengan status yang dipilih.</p>
                <div class="pt-2">
                    <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg bg-[#650506] hover:bg-[#4A070B] text-white text-xs font-semibold shadow-xs transition">
                        <span>Mulai Belanja</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        @endforelse

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="pt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
