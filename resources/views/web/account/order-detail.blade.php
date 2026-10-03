@extends('layouts.app')

@section('title', 'Pesanan #' . $order->order_number . ' - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-5 sm:space-y-7 pb-10">
    
    <!-- Breadcrumb & Top Action Header -->
    <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4 border-b border-gray-200/80 pb-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] sm:text-xs text-gray-500 mb-1">
                <a href="{{ route('home') }}" class="hover:text-[#650506] transition">Beranda</a>
                <span>/</span>
                <a href="{{ route('account.orders') }}" class="hover:text-[#650506] transition">Riwayat Pesanan</a>
                <span>/</span>
                <span class="text-gray-900 font-mono font-medium">#{{ $order->order_number }}</span>
            </div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-serif font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <span>Rincian Pesanan</span>
                <span class="text-xs font-mono font-normal text-stone-500 bg-stone-100 px-2 py-0.5 rounded-md border border-stone-200">
                    #{{ $order->order_number }}
                </span>
            </h1>
            <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">
                Dibuat pada {{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB
            </p>
        </div>

        <div class="flex items-center gap-2 sm:gap-3">
            @if($order->order_status === 'pending_payment')
                <form method="POST" action="{{ route('account.orders.cancel', $order->order_number) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini?');">
                    @csrf
                    <button type="submit" class="px-3.5 py-1.5 sm:py-2 rounded-xl border border-rose-300 text-rose-700 hover:bg-rose-50 text-xs font-semibold transition cursor-pointer">
                        Batalkan Pesanan
                    </button>
                </form>
            @endif
            <a href="{{ route('account.orders') }}" 
               class="inline-flex items-center gap-1.5 px-3.5 sm:px-4 py-1.5 sm:py-2 rounded-xl bg-white text-gray-700 border border-gray-300 hover:bg-stone-50 text-xs font-semibold shadow-2xs transition">
                <span>&larr;</span>
                <span>Kembali ke Riwayat</span>
            </a>
        </div>
    </div>

    <!-- Flash Messages (Alerts) -->
    @if(session('success'))
        <div class="p-3.5 sm:p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-medium flex items-center gap-2.5 shadow-2xs">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('info'))
        <div class="p-3.5 sm:p-4 rounded-2xl bg-blue-50 border border-blue-200 text-blue-800 text-xs sm:text-sm font-medium flex items-center gap-2.5 shadow-2xs">
            <svg class="w-4 h-4 text-blue-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
            <span>{{ session('info') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-3.5 sm:p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-medium flex items-center gap-2.5 shadow-2xs">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @php
        $isPaid = ($order->payment_status === 'paid');
        $isCancelled = ($order->order_status === 'cancelled');
        $isPendingPayment = (!$isPaid && !$isCancelled);
        $methodCode = $order->payment?->payment_type ?? $order->payment_method;
        $payload = $order->payment?->payload ?? [];
        $paymentUrl = $payload['payment_url'] ?? null;
        $isSandbox = !empty($payload['is_sandbox']) || str_contains($order->payment?->qr_string ?? '', 'lorem-ipsum') || ($order->payment?->va_number === '123123123');

        $methodNames = [
            'qris' => 'QRIS Instant (GoPay, OVO, Dana, BCA, ShopeePay)',
            'bri_va' => 'Bank BRI Virtual Account',
            'bni_va' => 'Bank BNI Virtual Account',
            'permata_va' => 'Bank Permata Virtual Account',
            'bca_va' => 'Bank BCA Virtual Account',
            'mandiri_va' => 'Bank Mandiri Virtual Account',
            'cod' => 'Cash On Delivery (Bayar di Tempat)',
        ];
        $methodTitle = $methodNames[$methodCode] ?? strtoupper(str_replace('_', ' ', $methodCode));
    @endphp

    <!-- HERO SECTION: STATUS PEMBAYARAN & NOMINAL TAGIHAN (UTAMAKAN TAMPILAN INI) -->
    <div class="rounded-2xl sm:rounded-3xl border overflow-hidden shadow-sm transition-all duration-200
        {{ $isPaid ? 'bg-gradient-to-br from-emerald-50 via-white to-emerald-50/60 border-emerald-300' : ($isCancelled ? 'bg-stone-50 border-stone-200' : 'bg-gradient-to-br from-[#FFF9F3] via-white to-rose-50/40 border-amber-300 shadow-md') }}">
        
        <div class="p-5 sm:p-7 lg:p-8 space-y-6">
            
            <!-- Hero Top Row: Payment Status & Grand Total Nominal -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b {{ $isPaid ? 'border-emerald-100' : 'border-amber-200/60' }}">
                
                <!-- Left: Status Badge & Payment Method Title -->
                <div class="space-y-2">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Status Pembayaran</span>
                        
                        @if($isPaid)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-600 text-white shadow-xs">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                LUNAS / TERVERIFIKASI
                            </span>
                        @elseif($isCancelled)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-stone-500 text-white">
                                DIBATALKAN
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-xs font-black bg-amber-500 text-white shadow-xs">
                                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                                MENUNGGU PEMBAYARAN
                            </span>
                            <span class="text-[11px] font-medium text-amber-800 bg-amber-100/70 px-2 py-0.5 rounded-full border border-amber-200">
                                Selesaikan sebelum 24 Jam
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 text-xs sm:text-sm text-stone-700">
                        <span class="text-stone-500">Metode:</span>
                        <strong class="text-stone-900 bg-white/90 px-2.5 py-1 rounded-lg border border-stone-200 shadow-2xs font-sans inline-flex items-center gap-1.5">
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
                            <span>{{ $methodTitle }}</span>
                        </strong>
                    </div>
                </div>

                <!-- Right: Prominent Nominal Tagihan -->
                <div class="text-left md:text-right pt-3 md:pt-0 border-t md:border-t-0 border-amber-200/50">
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500 block">Total Tagihan Pesanan</span>
                    <div class="flex items-baseline md:justify-end gap-1.5 mt-0.5">
                        <span class="text-2xl sm:text-3xl lg:text-4xl font-black font-mono tracking-tight text-[#650506]">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                    <span class="text-[11px] text-stone-400 block mt-0.5">
                        Termasuk produk, ongkos kirim, dan potongan diskon
                    </span>
                </div>

            </div>

            <!-- PAYMENT INSTRUCTIONS & ACTION CENTER (Hanya tampil jika belum lunas) -->
            @if($isPendingPayment)
                <div class="bg-white rounded-2xl p-5 sm:p-6 border border-amber-200 shadow-2xs space-y-5">
                    
                    @if($isSandbox)
                        <div class="p-3 rounded-xl bg-amber-50/80 border border-amber-200 text-amber-900 text-xs flex items-start gap-2.5">
                            <span class="text-base">🧪</span>
                            <div>
                                <strong class="font-bold block text-amber-950">Mode Sandbox (Uji Coba Pengujian)</strong>
                                <p class="text-[11px] text-amber-800 mt-0.5">Transaksi ini dibuat dalam lingkungan uji coba. Anda dapat menguji pelunasan otomatis menggunakan tombol simulasi di bawah.</p>
                            </div>
                        </div>
                    @endif

                    <!-- Instruksi Virtual Account -->
                    @if($order->payment?->va_number)
                        <div class="text-center max-w-md mx-auto py-2 space-y-3">
                            <span class="text-xs text-stone-500 block uppercase font-bold tracking-wider">
                                Nomor Virtual Account {{ strtoupper(str_replace('_va', '', $order->payment->payment_type)) }}
                            </span>
                            
                            <div class="p-3.5 bg-stone-50 rounded-2xl border-2 border-dashed border-amber-300 flex items-center justify-center gap-3 group">
                                <span id="vaCode" class="font-mono text-xl sm:text-2xl font-black tracking-widest text-stone-900 select-all">
                                    {{ $order->payment->va_number }}
                                </span>
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $order->payment->va_number }}'); alert('Nomor Virtual Account berhasil disalin!');"
                                        class="px-2.5 py-1 rounded-lg bg-white border border-stone-300 hover:bg-stone-100 text-[11px] font-bold text-stone-700 shadow-2xs transition cursor-pointer">
                                    Salin
                                </button>
                            </div>

                            <p class="text-xs text-stone-500">
                                Transfer tepat sejumlah <strong class="text-[#650506] font-mono">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</strong> melalui ATM, Mobile Banking, atau Internet Banking pilihan Anda.
                            </p>
                        </div>

                    <!-- Instruksi QRIS Instant -->
                    @elseif($order->payment?->qr_string)
                        <div class="text-center max-w-sm mx-auto space-y-3 py-1">
                            <span class="text-xs font-bold text-stone-600 block uppercase tracking-wider">Pindai Kode QRIS di Bawah Ini</span>
                            
                            <div class="inline-block p-3 bg-white rounded-2xl border-2 border-stone-200 shadow-sm">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($order->payment->qr_string) }}"
                                     alt="QRIS Code Aroma Palace"
                                     class="w-44 h-44 sm:w-48 sm:h-48 mx-auto object-contain">
                            </div>

                            <p class="text-xs text-stone-500 leading-relaxed">
                                Buka aplikasi e-wallet atau mobile banking Anda (BCA, GoPay, OVO, Dana, ShopeePay), lalu pilih menu <strong>Scan QRIS</strong>.
                            </p>
                        </div>
                    @endif

                    <!-- Action Buttons: Sinkron Status & Payment Link -->
                    <div class="pt-3 border-t border-stone-100 flex flex-wrap items-center justify-center gap-3">
                        <!-- Cek & Sinkron Status Pembayaran -->
                        <form method="POST" action="{{ route('account.orders.check_payment', $order->order_number) }}">
                            @csrf
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white hover:bg-stone-100 text-stone-800 font-bold text-xs border border-stone-300 shadow-2xs transition cursor-pointer">
                                <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span>Cek & Sinkronkan Pembayaran</span>
                            </button>
                        </form>

                        @if($paymentUrl)
                            <a href="{{ $paymentUrl }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#650506] hover:bg-[#4A070B] text-white font-bold text-xs shadow-xs transition">
                                <span>💳 Buka Halaman Bayar</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        @endif

                        @if($isSandbox || app()->environment('local', 'testing'))
                            <form method="POST" action="{{ route('account.orders.simulate_payment', $order->order_number) }}">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                                    <span>⚡ Simulasikan Pembayaran Sukses</span>
                                </button>
                            </form>
                        @endif
                    </div>

                </div>
            @elseif($isPaid)
                <!-- Banner Terkonfirmasi Sukses -->
                <div class="p-4 rounded-xl bg-emerald-100/60 border border-emerald-200 text-xs text-emerald-900 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0">✓</span>
                        <div>
                            <strong class="font-bold block text-emerald-950">Pembayaran Berhasil Diterima</strong>
                            <span class="text-emerald-800 text-[11px]">Terima kasih! Pesanan Anda saat ini sedang disiapkan oleh tim butik kami untuk segera diproses.</span>
                        </div>
                    </div>
                    <span class="text-xs font-mono font-bold text-emerald-800 shrink-0 hidden sm:inline">
                        LUNAS: Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                    </span>
                </div>
            @endif

        </div>

    </div>

    <!-- TRACKING & FULFILLMENT TIMELINE CARD -->
    <div class="bg-white rounded-2xl p-5 sm:p-7 border border-gray-200/90 shadow-2xs space-y-5">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-4">
            <div>
                <span class="text-[10px] sm:text-xs uppercase tracking-wider text-gray-400 font-semibold">Progres Pesanan</span>
                <h3 class="font-bold text-gray-900 text-base sm:text-lg mt-0.5">
                    @php
                        $fulfillmentLabels = [
                            'pending_payment' => 'Menunggu Pembayaran',
                            'processing' => 'Pesanan Sedang Diproses',
                            'shipped' => 'Dalam Pengiriman Kurir',
                            'ready_for_pickup' => 'Siap Diambil di Butik',
                            'delivered' => 'Pesanan Telah Tiba',
                            'completed' => 'Pesanan Selesai',
                            'cancelled' => 'Pesanan Dibatalkan',
                        ];
                    @endphp
                    {{ $fulfillmentLabels[$order->order_status] ?? strtoupper(str_replace('_', ' ', $order->order_status)) }}
                </h3>
            </div>

            <div class="text-left sm:text-right text-xs">
                @if(in_array($order->fulfillment_type, ['pickup', 'store_pickup']))
                    <span class="text-gray-500 block text-[11px]">Kode Pickup Butik:</span>
                    <span class="font-mono text-base font-black text-[#650506] bg-rose-50 px-2 py-0.5 rounded border border-rose-200">{{ $order->pickup_code ?? 'Siap saat notifikasi' }}</span>
                @else
                    <span class="text-gray-500 block text-[11px]">Nomor Resi ({{ $order->shipping_courier ?? 'Kurir' }}):</span>
                    @if($order->tracking_number)
                        <span class="font-mono text-sm sm:text-base font-bold text-gray-900 bg-stone-100 px-2 py-0.5 rounded border border-stone-200">{{ $order->tracking_number }}</span>
                    @else
                        <span class="text-stone-400 italic text-xs">Menunggu penyerahan ke kurir</span>
                    @endif
                @endif
            </div>
        </div>

        <!-- Timeline Log Visual -->
        <div class="relative pl-6 sm:pl-7 border-l-2 border-stone-200 space-y-5 my-2">
            @forelse($tracking['timeline'] as $step)
                <div class="relative group">
                    <span class="absolute -left-[31px] sm:-left-[35px] top-1 w-3.5 h-3.5 rounded-full bg-[#650506] ring-4 ring-rose-100"></span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h4 class="font-bold text-xs sm:text-sm text-gray-900">{{ $step['title'] }}</h4>
                            <span class="text-[10px] text-gray-400 font-mono">{{ $step['time'] }}</span>
                        </div>
                        <p class="text-xs text-gray-600 mt-0.5">{{ $step['description'] }}</p>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-400 italic">Belum ada pembaruan log pengiriman.</p>
            @endforelse
        </div>
    </div>

    <!-- 2-COLUMN MAIN CONTENT: Order Items & Delivery Details -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left 2 Cols: Purchased Items List & Calculation Breakdown -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl p-5 sm:p-7 border border-gray-200/90 shadow-2xs space-y-4">
                <h3 class="font-serif font-bold text-gray-900 text-base border-b border-gray-100 pb-3 flex items-center justify-between">
                    <span>Produk yang Dipesan</span>
                    <span class="text-xs font-sans text-gray-500 font-normal">{{ $order->items->count() }} Item Parfum</span>
                </h3>

                <div class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                        <div class="py-4 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-[#F4F2EE] border border-gray-200 p-1.5 flex items-center justify-center shrink-0 overflow-hidden">
                                    <img src="{{ $item->product_image ?? ($item->product?->primary_image ?? 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=150&q=80') }}"
                                         alt="{{ $item->product_name }}"
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-gray-900 text-xs sm:text-sm truncate">
                                        {{ $item->product_name }}
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        Varian: <strong class="text-gray-700">{{ $item->variant_name ?? 'Default' }}</strong> &bull; 
                                        {{ $item->quantity }} pcs &times; Rp {{ number_format($item->price, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="font-mono font-bold text-xs sm:text-sm text-gray-900 block">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Financial Calculation Summary -->
                <div class="border-t border-gray-100 pt-4 space-y-2.5 text-xs text-gray-600">
                    <div class="flex justify-between">
                        <span>Subtotal Produk</span>
                        <span class="font-mono font-bold text-gray-900">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span>Biaya Pengiriman (Ongkir)</span>
                        <span class="font-mono font-bold text-gray-900">
                            {{ $order->shipping_cost > 0 ? 'Rp ' . number_format($order->shipping_cost, 0, ',', '.') : 'GRATIS (Bebas Ongkir)' }}
                        </span>
                    </div>

                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-emerald-700 font-medium">
                            <span>Potongan Promo Voucher ({{ $order->promo_code ?? 'PROMO' }})</span>
                            <span class="font-mono font-bold">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    @if(!empty($order->points_discount) && $order->points_discount > 0)
                        <div class="flex justify-between text-emerald-700 font-medium">
                            <span>Diskon Penukaran Poin Loyalty</span>
                            <span class="font-mono font-bold">-Rp {{ number_format($order->points_discount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="border-t border-gray-200 pt-3 flex justify-between items-baseline">
                        <span class="font-bold text-gray-900 text-sm">Total Tagihan Final</span>
                        <span class="text-lg sm:text-xl font-black text-[#650506] font-mono">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                @if($order->notes)
                    <div class="mt-4 p-3 bg-stone-50 rounded-xl border border-stone-200 text-xs text-gray-600">
                        <strong class="text-gray-900 block mb-0.5">Catatan Pesanan:</strong>
                        <p>{{ $order->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right 1 Col: Shipping Address / Pickup Location & Help -->
        <div class="space-y-6">
            
            <!-- Address / Fulfillment Info Card -->
            <div class="bg-white rounded-2xl p-5 sm:p-6 border border-gray-200/90 shadow-2xs space-y-3.5">
                <h4 class="font-serif font-bold text-gray-900 text-sm border-b border-gray-100 pb-2">Informasi Pengiriman</h4>

                @if(in_array($order->fulfillment_type, ['pickup', 'store_pickup']))
                    <div class="p-3.5 bg-indigo-50/50 rounded-xl border border-indigo-100 text-xs space-y-2">
                        <span class="font-bold text-indigo-950 block">Pengambilan di Butik (Click & Collect)</span>
                        <p class="font-bold text-gray-900">{{ $order->store->name ?? 'Aroma Palace Boutique' }}</p>
                        <p class="text-gray-600">{{ $order->store->address ?? '' }}{{ !empty($order->store?->city) ? ', ' . $order->store->city : '' }}</p>
                        <div class="pt-2 border-t border-indigo-100 flex items-center justify-between">
                            <span class="text-gray-500">Kode Pengambilan:</span>
                            <span class="font-mono font-bold text-[#650506] text-sm">{{ $order->pickup_code ?? 'Siap saat notifikasi' }}</span>
                        </div>
                    </div>
                @else
                    @php
                        $snapshot = $order->shipping_address_snapshot ?? [];
                        $recipientName = $snapshot['recipient_name'] ?? $order->address?->recipient_name ?? $order->user?->name ?? 'Customer';
                        $label = $snapshot['label'] ?? $order->address?->label ?? 'Rumah';
                        $phone = $snapshot['phone_number'] ?? $order->address?->phone_number ?? $order->user?->phone ?? '-';
                        $fullAddress = $snapshot['full_address'] ?? $order->address?->full_address ?? 'Alamat tidak ditentukan';
                        $city = $snapshot['city'] ?? $order->address?->city ?? '';
                        $postalCode = $snapshot['postal_code'] ?? $order->address?->postal_code ?? '';
                    @endphp
                    <div class="p-3.5 bg-stone-50 rounded-xl border border-stone-200 text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-gray-900">{{ $recipientName }}</span>
                            <span class="text-[10px] bg-stone-200 text-stone-700 px-2 py-0.5 rounded font-bold">{{ $label }}</span>
                        </div>
                        <p class="text-stone-500 font-mono">{{ $phone }}</p>
                        <p class="text-stone-700 leading-relaxed">{{ $fullAddress }}{{ $city ? ', ' . $city : '' }} {{ $postalCode }}</p>
                        <div class="pt-2 border-t border-stone-200 flex items-center justify-between">
                            <span class="text-stone-500">Kurir:</span>
                            <span class="font-bold text-stone-900">{{ $order->shipping_courier ?? 'Kurir' }} {{ $order->shipping_service ? '(' . $order->shipping_service . ')' : '' }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Customer Care / Support Box -->
            <div class="bg-white rounded-2xl p-5 border border-gray-200/90 shadow-2xs space-y-2 text-xs">
                <h5 class="font-bold text-gray-900">Butuh Bantuan Pesanan?</h5>
                <p class="text-gray-500 leading-relaxed">
                    Jika Anda memiliki pertanyaan seputar pembayaran atau pengiriman, Fragrance Advisor kami siap membantu Anda.
                </p>
                <div class="pt-2">
                    <a href="https://wa.me/6281100001111?text=Halo%20Aroma%20Palace,%20saya%20ingin%20bertanya%20mengenai%20pesanan%20%23{{ $order->order_number }}" 
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-800">
                        <span>💬 Hubungi Customer Care WhatsApp</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
