@extends('layouts.admin')

@section('title', 'Kelola Pesanan #' . $order->order_number)

@section('content')
<div class="space-y-6 max-w-6xl">
    
    <!-- Top Bar: Back & Status Overview -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.orders.index') }}" 
               class="p-2 rounded-xl bg-white border border-stone-300 text-stone-700 hover:bg-stone-100 transition shadow-2xs">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-xl sm:text-2xl font-black font-mono text-stone-900">#{{ $order->order_number }}</h3>
                    <span class="text-xs text-stone-400">&bull;</span>
                    <span class="text-xs text-stone-500 font-sans">{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                </div>
                <p class="text-xs text-stone-500 mt-0.5">
                    Pelanggan: <strong class="text-stone-800">{{ $order->user?->name ?? 'Guest User' }}</strong> ({{ $order->user?->email ?? '-' }})
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <span class="px-3 py-1.5 rounded-xl text-xs font-bold border 
                @if($order->order_status === 'completed') bg-emerald-50 text-emerald-800 border-emerald-300
                @elseif($order->order_status === 'shipped') bg-purple-50 text-purple-800 border-purple-300
                @elseif($order->order_status === 'ready_for_pickup') bg-indigo-50 text-indigo-800 border-indigo-300
                @elseif($order->order_status === 'processing') bg-blue-50 text-blue-800 border-blue-300
                @elseif($order->order_status === 'cancelled') bg-rose-50 text-rose-800 border-rose-300
                @else bg-amber-50 text-amber-800 border-amber-300 @endif">
                Status: {{ strtoupper(str_replace('_', ' ', $order->order_status)) }}
            </span>
        </div>
    </div>

    <!-- HERO PAYMENT & NOMINAL SHOWCASE BOX (Paling Utama) -->
    <div class="rounded-2xl border p-5 sm:p-6 shadow-sm {{ $order->payment_status === 'paid' ? 'bg-gradient-to-r from-emerald-50 via-white to-emerald-50/50 border-emerald-300' : 'bg-gradient-to-r from-amber-50 via-white to-rose-50/50 border-amber-300' }}">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            
            <!-- Left: Payment Status & Method Details -->
            <div class="space-y-1.5">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Status Pembayaran:</span>
                    @if($order->payment_status === 'paid')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-600 text-white shadow-xs">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            LUNAS / SETTLEMENT
                        </span>
                    @elseif($order->order_status === 'cancelled')
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-stone-500 text-white">
                            DIBATALKAN
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-500 text-white shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                            BELUM DIBAYAR (PENDING)
                        </span>
                    @endif
                </div>

                <div class="text-xs text-stone-700 space-y-1 pt-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-stone-500">Metode Transaksi:</span>
                        <strong class="bg-white px-2 py-0.5 rounded border border-stone-200 text-stone-900 font-mono">
                            {{ strtoupper(str_replace('_', ' ', $order->payment?->payment_type ?? $order->payment_method)) }}
                        </strong>
                        @if($order->payment?->va_number)
                            <span class="text-stone-400">&bull;</span>
                            <span class="text-stone-500">No. Virtual Account:</span>
                            <code class="bg-white px-2 py-0.5 rounded border border-amber-300 font-mono font-bold text-stone-900">{{ $order->payment->va_number }}</code>
                        @endif
                    </div>

                    @if($order->payment?->transaction_id)
                        <div class="text-[11px] text-stone-500">
                            ID Transaksi Gateway: <code class="font-mono text-stone-700 bg-stone-100 px-1.5 py-0.5 rounded">{{ $order->payment->transaction_id }}</code>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Total Nominal Tagihan -->
            <div class="text-left md:text-right pt-3 md:pt-0 border-t md:border-t-0 border-stone-200">
                <span class="text-xs font-bold uppercase tracking-wider text-stone-500 block">Total Tagihan Pesanan</span>
                <span class="text-2xl sm:text-3xl font-black font-mono text-[#650506] block mt-0.5">
                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                </span>
                <span class="text-[11px] text-stone-500 block mt-0.5">
                    Subtotal: Rp {{ number_format($order->subtotal, 0, ',', '.') }} &bull; Ongkir: Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
                </span>
            </div>

        </div>
    </div>

    <!-- ADMIN ACTION BAR: HANYA TERIMA PESANAN & TOLAK PESANAN (RESI OTOMATIS) -->
    <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-5 sm:p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-stone-100 pb-3">
            <div>
                <h4 class="font-bold text-stone-900 text-base">Keputusan Pesanan</h4>
                <p class="text-xs text-stone-500 mt-0.5">
                    @if(in_array($order->order_status, ['pending_payment', 'processing']))
                        Pilih <strong>Terima Pesanan</strong> untuk menerbitkan resi pengiriman kurir otomatis, atau <strong>Tolak Pesanan</strong> untuk membatalkan transaksi dan mengembalikan stok.
                    @elseif(in_array($order->order_status, ['shipped', 'ready_for_pickup', 'delivered']))
                        Pesanan telah diterima dan resi otomatis aktif.
                    @elseif($order->order_status === 'completed')
                        Pesanan ini telah selesai diproses.
                    @else
                        Pesanan ini telah ditolak / dibatalkan.
                    @endif
                </p>
            </div>
            <span class="text-xs text-stone-400 font-mono">Order ID #{{ $order->id }}</span>
        </div>

        @if(in_array($order->order_status, ['pending_payment', 'processing']))
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-1">
                <div class="text-xs text-stone-600">
                    <span class="inline-flex items-center gap-1.5 font-bold text-amber-800 bg-amber-50 px-2.5 py-1 rounded-lg border border-amber-200">
                        <span>⏳</span>
                        <span>Menunggu Keputusan Butik</span>
                    </span>
                    <span class="text-stone-400 block mt-1.5 text-[11px]">
                        Mengklik <strong>Terima Pesanan</strong> akan otomatis menerbitkan nomor resi kurir resmi dan mengubah status pesanan.
                    </span>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <!-- Tombol Tolak Pesanan -->
                    <form method="POST" action="{{ route('admin.orders.reject', $order->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin MENOLAK pesanan #{{ $order->order_number }}? Stok produk akan otomatis dikembalikan ke etalase.');">
                        @csrf
                        <button type="submit" 
                                class="px-5 py-2.5 rounded-xl border border-rose-300 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs shadow-2xs transition cursor-pointer flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>Tolak Pesanan</span>
                        </button>
                    </form>

                    <!-- Tombol Terima Pesanan (Resi Otomatis) -->
                    <form method="POST" action="{{ route('admin.orders.accept', $order->id) }}">
                        @csrf
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition cursor-pointer flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Terima Pesanan (Resi Otomatis)</span>
                        </button>
                    </form>
                </div>
            </div>

        @elseif($order->order_status === 'shipped' || $order->order_status === 'ready_for_pickup')
            <div class="p-4 rounded-xl bg-emerald-50/80 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0">✓</span>
                    <div class="text-xs">
                        <strong class="font-bold text-emerald-950 block">Pesanan Telah Diterima & Resi Otomatis Aktif</strong>
                        @if($order->tracking_number)
                            <span class="text-emerald-800">
                                Nomor Resi ({{ $order->shipping_courier ?? 'Kurir' }}): 
                                <code class="font-mono font-bold bg-white px-2 py-0.5 rounded border border-emerald-300 text-emerald-950 text-xs">{{ $order->tracking_number }}</code>
                            </span>
                        @elseif($order->pickup_code)
                            <span class="text-emerald-800">
                                Kode Pickup Butik: 
                                <code class="font-mono font-bold bg-white px-2 py-0.5 rounded border border-emerald-300 text-emerald-950 text-xs">{{ $order->pickup_code }}</code>
                            </span>
                        @endif
                    </div>
                <div class="flex items-center gap-2">
                    @if($order->tracking_number)
                        <a href="{{ route('admin.orders.kiriminaja_print', $order->id) }}" target="_blank" 
                           class="px-3.5 py-2 rounded-xl bg-white border border-stone-300 hover:bg-stone-50 text-stone-700 font-bold text-xs shadow-2xs transition flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Cetak Label Kurir</span>
                        </a>
                    @endif

                    <form method="POST" action="{{ route('admin.orders.complete', $order->id) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                            ✓ Tandai Selesai Diterima
                        </button>
                    </form>
                </div>
            </div>

        @elseif($order->order_status === 'completed')
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0">✓</span>
                    <span>Pesanan telah <strong>Selesai</strong> dan diterima oleh pelanggan.</span>
                </div>
                @if($order->tracking_number)
                    <span class="font-mono text-emerald-800 bg-white px-2 py-0.5 rounded border border-emerald-200">Resi: {{ $order->tracking_number }}</span>
                @endif
            </div>

        @elseif($order->order_status === 'cancelled')
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-full bg-rose-600 text-white flex items-center justify-center font-bold text-xs shrink-0">✕</span>
                    <span>Pesanan telah <strong>Ditolak / Dibatalkan</strong>. Seluruh stok produk telah dikembalikan ke etalase.</span>
                </div>
            </div>
        @endif
    </div>

    <!-- 2-COLUMN GRID: Products & Fulfillment Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Purchased Products & Calculation (2 Cols) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Product List Card -->
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-6 space-y-4">
                <h4 class="font-bold text-stone-900 text-sm border-b border-stone-100 pb-3 flex items-center justify-between">
                    <span>Produk yang Dipesan</span>
                    <span class="text-xs text-stone-500 font-normal">{{ $order->items->count() }} item</span>
                </h4>

                <div class="divide-y divide-stone-100">
                    @foreach($order->items as $item)
                        <div class="py-3.5 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-14 h-14 rounded-xl overflow-hidden border border-stone-200 bg-[#F4F2EE] shrink-0">
                                    <img src="{{ $item->product_image ?? ($item->product?->primary_image ?? 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=150&q=80') }}" 
                                         alt="{{ $item->product_name }}"
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="min-w-0">
                                    <h5 class="text-sm font-bold text-stone-900 truncate">{{ $item->product_name }}</h5>
                                    <p class="text-xs text-stone-500 mt-0.5">
                                        Varian: <strong class="text-stone-700">{{ $item->variant_name ?? 'Default' }}</strong> &bull; 
                                        {{ $item->quantity }} pcs &times; Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-mono font-bold text-sm text-stone-900 block">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Financial Calculation -->
                <div class="border-t border-stone-100 pt-4 space-y-2 text-xs">
                    <div class="flex justify-between text-stone-600">
                        <span>Subtotal Produk</span>
                        <span class="font-mono font-bold text-stone-900">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-stone-600">
                        <span>Biaya Pengiriman (Ongkir)</span>
                        <span class="font-mono font-bold text-stone-900">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>

                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-emerald-700 font-medium">
                            <span>Potongan Diskon / Promo ({{ $order->promo_code ?? 'VOUCHER' }})</span>
                            <span class="font-mono font-bold">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="border-t border-stone-200 pt-3 flex justify-between items-baseline text-sm">
                        <span class="font-bold text-stone-900">Total Tagihan Final</span>
                        <span class="font-mono font-black text-lg text-[#650506]">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Status Histories Timeline -->
            @if($order->statusHistories->isNotEmpty())
                <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-6 space-y-4">
                    <h4 class="font-bold text-stone-900 text-sm border-b border-stone-100 pb-3">Riwayat Log Pesanan</h4>
                    <div class="relative pl-5 border-l-2 border-stone-200 space-y-4">
                        @foreach($order->statusHistories as $log)
                            <div class="relative group">
                                <span class="absolute -left-[25px] top-1 w-2.5 h-2.5 rounded-full bg-[#38050D]"></span>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-xs text-stone-900">{{ $log->title }}</span>
                                    <span class="text-[10px] text-stone-400 font-mono">{{ $log->created_at->format('d M Y, H:i') }}</span>
                                </div>
                                <p class="text-xs text-stone-500 mt-0.5">{{ $log->description }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Right: Shipping / Delivery & Customer Information (1 Col) -->
        <div class="space-y-6">
            
            <!-- Fulfillment & Address Card -->
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-5 space-y-3">
                <h4 class="font-bold text-stone-900 text-sm border-b border-stone-100 pb-2">Informasi Pengiriman</h4>

                @if($order->fulfillment_type === 'store_pickup')
                    <div class="p-3.5 bg-indigo-50/50 rounded-xl border border-indigo-100 text-xs space-y-2">
                        <span class="font-bold text-indigo-900 block">Ambil di Butik (Click & Collect)</span>
                        <p class="font-bold text-stone-900">{{ $order->store?->name ?? 'Aroma Palace Boutique' }}</p>
                        <p class="text-stone-600">{{ $order->store?->address ?? '-' }}</p>
                        <div class="pt-2 border-t border-indigo-100 flex items-center justify-between">
                            <span class="text-stone-500">Kode Pengambilan:</span>
                            <span class="font-mono font-bold text-[#650506] text-sm">{{ $order->pickup_code ?? '-' }}</span>
                        </div>
                    </div>
                @else
                    @php
                        $snap = $order->shipping_address_snapshot ?? [];
                        $recipient = $snap['recipient_name'] ?? $order->address?->recipient_name ?? $order->user?->name ?? 'Customer';
                        $phone = $snap['phone_number'] ?? $order->address?->phone_number ?? '-';
                        $fullAddr = $snap['full_address'] ?? $order->address?->full_address ?? '-';
                        $city = $snap['city'] ?? $order->address?->city ?? '';
                        $postal = $snap['postal_code'] ?? $order->address?->postal_code ?? '';
                    @endphp
                    <div class="p-3.5 bg-stone-50 rounded-xl border border-stone-200 text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-stone-900">{{ $recipient }}</span>
                            <span class="text-[10px] bg-stone-200 text-stone-700 px-2 py-0.5 rounded font-bold">
                                {{ $snap['label'] ?? 'Alamat' }}
                            </span>
                        </div>
                        <p class="text-stone-500 font-mono">{{ $phone }}</p>
                        <p class="text-stone-700 leading-relaxed">{{ $fullAddr }}{{ $city ? ', ' . $city : '' }} {{ $postal }}</p>
                        <div class="pt-2 border-t border-stone-200 text-stone-600 flex items-center justify-between">
                            <span>Kurir Dipilih:</span>
                            <span class="font-bold text-stone-900">{{ $order->shipping_courier ?? 'Internal' }} ({{ $order->shipping_service ?? 'REG' }})</span>
                        </div>
                    </div>
                @endif

                @if($order->notes)
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs">
                        <span class="font-bold text-amber-900 block mb-0.5">Catatan Customer:</span>
                        <p class="text-amber-800">{{ $order->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Customer Profile Card -->
            <div class="bg-white rounded-2xl border border-stone-200 shadow-sm p-5 space-y-3">
                <h4 class="font-bold text-stone-900 text-sm border-b border-stone-100 pb-2">Data Pelanggan</h4>
                <div class="text-xs space-y-2 text-stone-600">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-[#38050D] text-white flex items-center justify-center font-bold text-sm shrink-0">
                            {{ strtoupper(substr($order->user?->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <span class="font-bold text-stone-900 block">{{ $order->user?->name ?? 'Guest User' }}</span>
                            <span class="text-[11px] text-stone-400">{{ $order->user?->email ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-stone-100 flex justify-between">
                        <span class="text-stone-400">ID Customer:</span>
                        <span class="font-mono font-bold text-stone-700">#{{ $order->user?->id ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-400">Total Pesanan:</span>
                        <span class="font-bold text-stone-700">{{ $order->user?->orders()->count() ?? 1 }} kali transaksi</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
