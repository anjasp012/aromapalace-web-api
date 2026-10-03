@extends('layouts.app')

@section('title', 'Pesanan #' . $order->order_number . ' - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6 pb-6 sm:pb-8">
    <!-- Breadcrumb & Actions Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4 border-b border-gray-200 pb-4 sm:pb-6">
        <div>
            <div class="flex items-center gap-2 text-[11px] sm:text-xs text-gray-500 mb-1.5 sm:mb-2">
                <a href="{{ route('home') }}" class="hover:text-[#650506]">Beranda</a>
                <span>/</span>
                <a href="{{ route('account.orders') }}" class="hover:text-[#650506]">Riwayat Pesanan</a>
                <span>/</span>
                <span class="text-gray-900 font-medium">#{{ $order->order_number }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-serif font-bold text-gray-900 tracking-tight">Pesanan #{{ $order->order_number }}</h1>
            <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1">Dibuat pada {{ $order->created_at->format('d M Y, H:i') }}</p>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            @if($order->order_status === 'pending_payment')
                <form method="POST" action="{{ route('account.orders.cancel', $order->order_number) }}" onsubmit="return confirm('Yakin ingin membatalkan pesanan ini?');">
                    @csrf
                    <button type="submit" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg border border-rose-300 text-rose-700 hover:bg-rose-50 text-[11px] sm:text-xs font-semibold transition">
                        Batalkan Pesanan
                    </button>
                </form>
            @endif
            <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-1 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg bg-gray-100 text-gray-800 border border-gray-200 hover:bg-gray-200 text-[11px] sm:text-xs font-semibold transition">
                <span>&larr;</span>
                <span>Kembali ke Pesanan</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 sm:p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-medium flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('info'))
        <div class="p-3 sm:p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-xs sm:text-sm font-medium flex items-center gap-2">
            <span>ℹ️</span>
            <span>{{ session('info') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-3 sm:p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-medium flex items-center gap-2">
            <span>⚠️</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Tracking Timeline Card -->
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8 border border-gray-200 shadow-2xs space-y-4 sm:space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4 border-b border-gray-100 pb-3 sm:pb-4">
            <div>
                <span class="text-[10px] sm:text-xs uppercase tracking-wider text-gray-400 font-semibold">Fulfillment & Tracking</span>
                <h3 class="font-bold text-gray-900 text-base sm:text-lg mt-0.5">
                    {{ strtoupper(str_replace('_', ' ', $order->order_status)) }}
                </h3>
            </div>
            <div class="text-left sm:text-right text-xs">
                @if(in_array($order->fulfillment_type, ['pickup', 'store_pickup']))
                    <span class="text-gray-500 block text-[10px] sm:text-xs">Pickup Code (Click & Collect):</span>
                    <span class="font-mono text-sm sm:text-base font-bold text-[#650506]">{{ $order->pickup_code ?? 'Ready upon notification' }}</span>
                @else
                    <span class="text-gray-500 block text-[10px] sm:text-xs">Tracking Number ({{ $order->shipping_courier ?? 'Courier' }}):</span>
                    <span class="font-mono text-sm sm:text-base font-bold text-gray-900">{{ $order->tracking_number ?? 'In preparation' }}</span>
                @endif
            </div>
        </div>

        <!-- Visual Timeline -->
        <div class="relative pl-5 sm:pl-6 border-l-2 border-gray-300 space-y-4 sm:space-y-6 my-2 sm:my-4">
            @forelse($tracking['timeline'] as $step)
                <div class="relative group">
                    <span class="absolute -left-[27px] sm:-left-[31px] top-0.5 w-3 sm:w-3.5 h-3 sm:h-3.5 rounded-full bg-[#650506] ring-4 ring-red-100"></span>
                    <div>
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                            <h4 class="font-bold text-xs sm:text-sm text-gray-900">{{ $step['title'] }}</h4>
                            <span class="text-[10px] sm:text-[11px] text-gray-400 font-mono">{{ $step['time'] }}</span>
                        </div>
                        <p class="text-[11px] sm:text-xs text-gray-600 mt-0.5">{{ $step['description'] }}</p>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-400">Belum ada pembaruan status.</p>
            @endforelse
        </div>
    </div>

    <!-- 2 Column Details: Items & Delivery/Payment -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
        <!-- Order Items (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8 border border-gray-200 shadow-2xs space-y-4 sm:space-y-6">
            <h3 class="font-bold text-gray-900 text-sm sm:text-base border-b border-gray-100 pb-2.5 sm:pb-3">Produk Dipesan</h3>

            <div class="divide-y divide-gray-100">
                @foreach($order->items as $item)
                    <div class="py-3 sm:py-4 flex items-center justify-between gap-3 sm:gap-4">
                        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                            <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-lg bg-[#F4F2EE] border border-gray-200 p-1.5 sm:p-2 flex items-center justify-center shrink-0">
                                <img src="{{ $item->product?->images?->first()?->image_url ?? 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=150&q=80' }}"
                                     alt="{{ $item->product?->name ?? 'Product' }}"
                                     class="max-h-full max-w-full object-contain mix-blend-multiply">
                            </div>
                            <div class="min-w-0">
                                @if($item->product)
                                    <a href="{{ route('products.show', $item->product->slug) }}" class="font-bold text-gray-900 text-xs sm:text-sm hover:text-[#650506] truncate block">
                                        {{ $item->product->name }}
                                    </a>
                                @else
                                    <span class="font-bold text-gray-900 text-xs sm:text-sm">Product</span>
                                @endif
                                <p class="text-[10px] sm:text-xs text-gray-500 mt-0.5">
                                    Varian: <span class="font-semibold text-gray-700">{{ $item->variant?->name ?? 'Standar' }}</span> &bull; 
                                    Jml: <span class="font-semibold text-gray-700">{{ $item->quantity }}</span> &times; Rp {{ number_format($item->price, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                        <span class="text-xs sm:text-sm font-bold text-gray-900 whitespace-nowrap font-mono shrink-0">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </span>
                    </div>
                @endforeach
            </div>

            <!-- Notes if any -->
            @if($order->notes)
                <div class="p-3 sm:p-4 bg-gray-50 rounded-lg sm:rounded-xl border border-gray-200 text-xs text-gray-600">
                    <span class="font-bold text-gray-700 block mb-1">Catatan Pelanggan:</span>
                    <p>{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Sidebar: Fulfillment & Payment Summary (1 Col) -->
        <div class="space-y-4 sm:space-y-6">
            <!-- Fulfillment Info Card -->
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-200 shadow-2xs space-y-3 sm:space-y-4">
                <h4 class="font-bold text-gray-900 text-sm">Informasi Pengiriman</h4>

                @if(in_array($order->fulfillment_type, ['pickup', 'store_pickup']))
                    <div class="text-xs space-y-2 text-gray-600 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <span class="font-bold text-gray-900 block">Lokasi Pengambilan (Store Pickup):</span>
                        <p class="font-bold text-sm text-gray-900">{{ $order->store->name ?? 'Aroma Palace Boutique' }}</p>
                        <p class="text-gray-600">{{ $order->store->address ?? '' }}{{ !empty($order->store?->city) ? ', ' . $order->store->city : '' }}</p>
                        <div class="pt-2 border-t border-gray-200 flex items-center justify-between">
                            <span class="text-gray-500">Kode Pengambilan:</span>
                            <span class="font-mono font-bold text-[#650506] text-sm">{{ $order->pickup_code ?? 'Siap saat notifikasi' }}</span>
                        </div>
                        <p class="text-[11px] text-gray-400">Tunjukkan kode ini kepada staf butik kami saat tiba.</p>
                    </div>
                @else
                    @php
                        $snapshot = $order->shipping_address_snapshot ?? [];
                        $recipientName = $snapshot['recipient_name'] ?? $order->address?->recipient_name ?? $order->user?->name ?? 'Customer';
                        $label = $snapshot['label'] ?? $order->address?->label ?? 'Home';
                        $phone = $snapshot['phone_number'] ?? $order->address?->phone_number ?? $order->user?->phone ?? '-';
                        $fullAddress = $snapshot['full_address'] ?? $order->address?->full_address ?? 'Address not specified';
                        $city = $snapshot['city'] ?? $order->address?->city ?? '';
                        $postalCode = $snapshot['postal_code'] ?? $order->address?->postal_code ?? '';
                    @endphp
                    <div class="text-xs space-y-2 text-gray-600 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-900">{{ $recipientName }}</span>
                            <span class="text-[10px] bg-gray-200 text-gray-700 px-2 py-0.5 rounded font-bold">{{ $label }}</span>
                        </div>
                        <p class="text-gray-500">{{ $phone }}</p>
                        <p class="text-gray-700">{{ $fullAddress }}{{ $city ? ', ' . $city : '' }} {{ $postalCode }}</p>
                        <p class="text-gray-500 pt-1 border-t border-gray-200">Kurir: <span class="font-bold text-gray-800">{{ $order->shipping_courier ?? 'Kurir' }} {{ $order->shipping_service ? '(' . $order->shipping_service . ')' : '' }}</span></p>
                    </div>
                @endif
            </div>

            <!-- Payment Summary Card -->
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-200 shadow-2xs space-y-3 sm:space-y-4">
                <h4 class="font-bold text-gray-900 text-sm">Detail Pembayaran</h4>

                <div class="space-y-2 sm:space-y-2.5 text-[11px] sm:text-xs text-gray-600">
                    <div class="flex justify-between">
                        <span>Subtotal Produk</span>
                        <span class="font-bold text-gray-900 font-mono">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Biaya Pengiriman</span>
                        <span class="font-bold text-gray-900 font-mono">
                            {{ $order->shipping_cost > 0 ? 'Rp ' . number_format($order->shipping_cost, 0, ',', '.') : 'GRATIS' }}
                        </span>
                    </div>

                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-emerald-700 font-medium">
                            <span>Diskon</span>
                            <span class="font-mono">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if(!empty($order->points_discount) && $order->points_discount > 0)
                        <div class="flex justify-between text-emerald-700 font-medium">
                            <span>Penukaran Poin</span>
                            <span class="font-mono">-Rp {{ number_format($order->points_discount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="border-t border-gray-200 pt-2.5 sm:pt-3 flex justify-between items-baseline">
                        <span class="font-bold text-gray-900 text-xs sm:text-sm">Total</span>
                        <span class="text-lg sm:text-xl font-extrabold text-gray-900 font-mono">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>

                @if($order->payment)
                    <div class="pt-2.5 sm:pt-3 border-t border-gray-100 text-[11px] sm:text-xs space-y-1.5 sm:space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Gateway:</span>
                            <span class="px-2 py-0.5 rounded bg-gray-100 text-gray-800 border border-gray-200 font-semibold uppercase text-[10px]">
                                {{ strtoupper($order->payment->payment_gateway ?? 'Pakasir') }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Metode:</span>
                            <span class="font-bold text-gray-800">{{ strtoupper($order->payment->payment_type ?? $order->payment_method) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status Pembayaran:</span>
                            <span class="font-bold {{ $order->payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-600' }}">
                                {{ strtoupper($order->payment_status) }}
                            </span>
                        </div>

                        <!-- Pakasir QRIS / VA Instructions if pending -->
                        @if($order->payment_status !== 'paid')
                            @php
                                $payload = $order->payment->payload ?? [];
                                $isSandbox = !empty($payload['is_sandbox']) || str_contains($order->payment->qr_string ?? '', 'lorem-ipsum') || ($order->payment->va_number === '123123123');
                                $paymentUrl = $payload['payment_url'] ?? null;
                            @endphp

                            <div class="mt-3 sm:mt-4 p-3 sm:p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-gray-900 text-xs">Instruksi Pembayaran</span>
                                    <span class="text-[10px] text-gray-500">Selesaikan dalam 24 jam</span>
                                </div>

                                @if($isSandbox)
                                    <div class="p-2.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-900 text-[11px] leading-relaxed">
                                        <div class="flex items-center gap-1.5 font-bold mb-0.5 text-amber-800">
                                            <span>🧪</span>
                                            <span>Mode Sandbox Pakasir (Testing)</span>
                                        </div>
                                        <p class="text-amber-700">Transaksi aktif dalam mode sandbox Pakasir. Gunakan tombol <strong>Simulasikan Pembayaran Sukses</strong> di bawah untuk menguji perubahan status menjadi Lunas secara instan.</p>
                                    </div>
                                @endif

                                @if($order->payment->qr_string)
                                    <div class="text-center space-y-1.5 sm:space-y-2">
                                        <div class="inline-block p-1.5 sm:p-2 bg-white rounded-xl shadow-2xs border border-gray-200">
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($order->payment->qr_string) }}"
                                                 alt="QRIS Code"
                                                 class="w-32 h-32 sm:w-36 sm:h-36 mx-auto">
                                        </div>
                                        <p class="text-[10px] sm:text-[11px] text-gray-500">Scan QRIS dengan aplikasi perbankan atau e-wallet Anda</p>
                                    </div>
                                @elseif($order->payment->va_number)
                                    <div class="p-2.5 sm:p-3 bg-white rounded-xl border border-gray-200 text-center">
                                        <span class="text-[10px] sm:text-[11px] text-gray-400 block uppercase">Virtual Account {{ strtoupper(str_replace('_va', '', $order->payment->payment_type)) }}</span>
                                        <span class="font-mono text-sm sm:text-base font-extrabold text-gray-900 tracking-wider block my-1 select-all">
                                            {{ $order->payment->va_number }}
                                        </span>
                                        <span class="text-[10px] text-gray-500">Jumlah Transfer: <strong>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong></span>
                                    </div>
                                @endif

                                <!-- Actions: Cek Status & Simulasi -->
                                <div class="pt-2 border-t border-gray-200 space-y-2">
                                    <form method="POST" action="{{ route('account.orders.check_payment', $order->order_number) }}">
                                        @csrf
                                        <button type="submit" class="w-full bg-white hover:bg-gray-100 text-gray-800 font-bold py-2 px-3 border border-gray-300 rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-2xs transition cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            <span>Cek & Sinkron Status Pembayaran</span>
                                        </button>
                                    </form>

                                    @if($paymentUrl)
                                        <a href="{{ $paymentUrl }}" target="_blank" rel="noopener noreferrer"
                                           class="w-full bg-[#650506] hover:bg-[#4A070B] text-white font-bold py-2 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition text-center shadow-2xs">
                                            <span>💳 Buka Halaman Bayar Pakasir</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    @endif

                                    @if($isSandbox || app()->environment('local', 'testing'))
                                        <form method="POST" action="{{ route('account.orders.simulate_payment', $order->order_number) }}">
                                            @csrf
                                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 shadow-2xs transition cursor-pointer">
                                                <span>⚡ Simulasikan Pembayaran Sukses</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
