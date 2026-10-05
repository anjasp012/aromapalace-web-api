@php
    $courierTracking = $tracking['courier_tracking'] ?? null;
    $hasCourierTracking = !empty($courierTracking) && !empty($courierTracking['milestones']);
    $isHomeDelivery = !in_array($order->fulfillment_type, ['pickup', 'store_pickup']);
    $isDelivered = ($order->order_status === 'delivered') || (!empty($courierTracking['is_delivered']));
    $isCompleted = ($order->order_status === 'completed');
    $isPending = ($order->order_status === 'pending_payment');
    $isShipped = ($order->order_status === 'shipped');

    // Alamat Snapshot
    $snap = $order->shipping_address_snapshot ?? [];
    $addressLabel = strtolower($snap['label'] ?? 'Rumah');
    $recipientName = $snap['recipient_name'] ?? ($order->address?->recipient_name ?? ($order->user?->name ?? 'Pelanggan'));
    $phone = $snap['phone_number'] ?? ($order->address?->phone_number ?? '-');
    $fullAddress = ($snap['full_address'] ?? ($order->address?->full_address ?? '-')) . 
        (!empty($snap['district']) ? ', ' . $snap['district'] : '') . 
        (!empty($snap['city']) ? ', ' . $snap['city'] : '') . 
        (!empty($snap['province']) ? ', ' . $snap['province'] : '') . 
        (!empty($snap['postal_code']) ? ' ' . $snap['postal_code'] : '');

    // Ekspedisi
    $courierName = $courierTracking['courier'] ?? ($order->shipping_courier ?? 'Kurir Ekspedisi');
    $serviceName = $courierTracking['service'] ?? ($order->shipping_service ?? 'Reguler');

    // Metode Pembayaran Label
    $methodCode = strtolower($order->payment_method ?? ($order->payment?->payment_type ?? ''));
    $methodTitle = match(true) {
        str_contains($methodCode, 'qris') => 'QRIS Instant',
        str_contains($methodCode, 'bca') => 'BCA Virtual Account',
        str_contains($methodCode, 'bri') => 'BRI Virtual Account',
        str_contains($methodCode, 'bni') => 'BNI Virtual Account',
        str_contains($methodCode, 'mandiri') => 'Mandiri Virtual Account',
        str_contains($methodCode, 'permata') => 'Permata Virtual Account',
        str_contains($methodCode, 'cod') => 'Cash On Delivery (COD)',
        default => strtoupper(str_replace('_', ' ', $order->payment_method ?: 'Virtual Account'))
    };

    // Status Badge & Stepper Stage (1 = Dipesan/Paid, 2 = Dikirim, 3 = In Transit/Out for delivery, 4 = Selesai)
    $stepperStage = match($order->order_status) {
        'pending_payment' => 1,
        'processing' => 1,
        'shipped' => $isDelivered ? 3 : 2,
        'ready_for_pickup' => 3,
        'delivered' => 3,
        'completed' => 4,
        'cancelled' => 0,
        default => 2,
    };

    $statusBadges = [
        'pending_payment' => ['label' => 'Menunggu Pembayaran', 'class' => 'bg-amber-100 text-amber-900 border-amber-300'],
        'processing' => ['label' => 'Diproses Butik', 'class' => 'bg-blue-100 text-blue-900 border-blue-300'],
        'shipped' => ['label' => 'Dalam Pengiriman', 'class' => 'bg-indigo-100 text-indigo-900 border-indigo-300'],
        'ready_for_pickup' => ['label' => 'Siap Diambil', 'class' => 'bg-purple-100 text-purple-900 border-purple-300'],
        'delivered' => ['label' => 'Terkirim', 'class' => 'bg-teal-100 text-teal-900 border-teal-300'],
        'completed' => ['label' => 'Selesai', 'class' => 'bg-emerald-100 text-emerald-900 border-emerald-300 font-bold'],
        'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-rose-100 text-rose-900 border-rose-300'],
    ];
    $currentBadge = $statusBadges[$order->order_status] ?? ['label' => strtoupper($order->order_status), 'class' => 'bg-stone-100 text-stone-800 border-stone-300'];

    // Stepper Headline & Subtext
    $stepHeadline = match(true) {
        $isCompleted => 'Pesanan Selesai!',
        $order->order_status === 'delivered' || $isDelivered => 'Paket Telah Tiba di Penerima!',
        $order->order_status === 'shipped' => (!empty($courierTracking['status_label']) ? $courierTracking['status_label'] . '!' : 'Dalam Pengiriman Kurir!'),
        $order->order_status === 'ready_for_pickup' => 'Pesanan Siap Diambil di Butik!',
        $order->order_status === 'processing' => 'Pesanan Sedang Dipersiapkan Butik!',
        $order->order_status === 'pending_payment' => 'Menunggu Pembayaran!',
        $order->order_status === 'cancelled' => 'Pesanan Telah Dibatalkan',
        default => 'Pesanan Sedang Diproses'
    };

    $stepSubtext = match(true) {
        $isCompleted => 'Pesanan telah diterima dengan baik oleh pembeli.',
        $order->order_status === 'delivered' || $isDelivered => 'Paket telah sukses diserahkan oleh kurir ke alamat tujuan.',
        $order->order_status === 'shipped' => (!empty($courierTracking['current_location']) ? 'Posisi terkini: ' . $courierTracking['current_location'] : 'Paket dalam perjalanan ekspedisi menuju kota tujuan.'),
        $order->order_status === 'ready_for_pickup' => 'Silakan tunjukkan kode pickup di butik kami.',
        $order->order_status === 'processing' => 'Tim butik sedang meracik dan mengemas wewangian Anda.',
        $order->order_status === 'pending_payment' => 'Selesaikan pembayaran sebelum batas waktu berakhir.',
        default => ''
    };
@endphp

<!-- MODAL CONTENT CONTAINER -->
<div class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-stone-200">
    <!-- MODAL HEADER -->
    <div class="flex items-center justify-between px-6 py-4.5 border-b border-stone-100 bg-white sticky top-0 z-10">
        <h3 class="font-serif font-black text-stone-900 text-lg sm:text-xl tracking-tight">Detail Transaksi</h3>
        <button type="button" onclick="closeTransactionModal()" 
                class="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-500 hover:text-stone-900 flex items-center justify-center transition cursor-pointer text-lg font-bold"
                aria-label="Tutup">
            &times;
        </button>
    </div>

    <!-- MODAL BODY 2-COLUMN GRID -->
    <div class="p-6 sm:p-7 max-h-[82vh] overflow-y-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-7">
            
            <!-- LEFT COLUMN: Metadata, Shipping & Stepper Status (7 Cols) -->
            <div class="lg:col-span-7 space-y-5 text-xs text-stone-700">
                
                <!-- TOP META INFO (Invoice, Date, Payment, Status) -->
                <div class="space-y-2.5">
                    <!-- Invoice Row -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-stone-500 font-medium">INV/{{ $order->created_at->format('dmy') }}/AP/{{ str_replace('AP-', '', $order->order_number) }}</span>
                        <a href="{{ route('account.orders.invoice', $order->order_number) }}" target="_blank"
                           class="text-blue-600 hover:text-blue-800 font-bold inline-flex items-center gap-1 transition text-xs">
                            <span>Lihat Invoice</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>

                    <!-- Date Row -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-stone-500 font-medium">Tanggal Pembelian</span>
                        <span class="font-medium text-stone-900">{{ $order->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                    </div>

                    <!-- Payment Method Row -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-stone-500 font-medium">Metode Pembayaran</span>
                        <span class="font-bold text-stone-900">{{ $methodTitle }}</span>
                    </div>

                    <!-- Order Status Row -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-stone-500 font-medium">Status Pesanan</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $currentBadge['class'] }}">
                            {{ $currentBadge['label'] }}
                        </span>
                    </div>
                </div>

                <hr class="border-stone-100">

                <!-- PENGIRIMAN SECTION -->
                <div class="space-y-2.5">
                    <h4 class="font-bold text-stone-900 text-xs uppercase tracking-wider">Pengiriman</h4>
                    
                    @if($isHomeDelivery)
                        @php
                            $courierLogoUrl = \App\Services\KiriminAjaShippingService::getCourierLogoUrl($courierName);
                        @endphp
                        <div class="flex items-center gap-3">
                            @if($courierLogoUrl)
                                <div class="h-9 px-2 bg-stone-50 rounded-lg border border-stone-200 flex items-center justify-center shrink-0">
                                    <img src="{{ $courierLogoUrl }}" alt="{{ $courierName }}" class="h-6 max-w-[85px] object-contain">
                                </div>
                            @endif
                            <div>
                                <div class="font-bold text-stone-900 text-xs sm:text-sm">
                                    {{ $courierName }}
                                </div>
                                <div class="text-stone-500 text-[11px]">
                                    {{ $serviceName }} ({{ $order->estimated_delivery ?: '1 - 3 Hari' }})
                                    @if($order->tracking_number)
                                        &bull; No. Resi: <strong class="font-mono text-stone-800 select-all">{{ $order->tracking_number }}</strong>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $order->tracking_number }}'); alert('No. Resi berhasil disalin!');" 
                                                class="text-blue-600 hover:text-blue-800 font-bold ml-1 cursor-pointer">Salin</button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Recipient Address Details -->
                        <div class="pt-1 space-y-0.5">
                            <div class="font-bold text-stone-900 capitalize">{{ $addressLabel }}</div>
                            <div class="text-stone-800 font-medium">{{ $recipientName }} ({{ $phone }})</div>
                            <p class="text-stone-500 text-[11px] leading-relaxed">{{ $fullAddress }}</p>
                        </div>
                    @else
                        <!-- Store Pickup Details -->
                        <div class="p-3 bg-amber-50/70 rounded-xl border border-amber-200 space-y-1">
                            <div class="font-bold text-stone-900 text-xs">Ambil di Butik: {{ $order->store?->name ?? 'Aroma Palace Flagship Boutique' }}</div>
                            <p class="text-stone-600 text-[11px]">{{ $order->store?->address ?? 'Grand Indonesia Mall, East Mall Lt. 1, Jakarta Pusat' }}</p>
                            <div class="pt-1.5 flex items-center justify-between text-xs">
                                <span class="text-stone-500">Kode Pengambilan:</span>
                                <span class="font-mono font-black text-sm text-[#650506] bg-rose-50 px-2 py-0.5 rounded border border-rose-200">{{ $order->pickup_code ?? 'Siap saat konfirmasi' }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <hr class="border-stone-100">

                <!-- STATUS PESANAN (STEPPER & COLLAPSIBLE TIMELINE) -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h4 class="font-bold text-stone-900 text-xs uppercase tracking-wider">Status Pesanan</h4>
                        <button type="button" onclick="toggleTrackingTimeline()" 
                                id="timelineToggleBtn"
                                class="text-stone-500 hover:text-stone-900 text-xs font-semibold inline-flex items-center gap-1 transition cursor-pointer">
                            <span id="timelineToggleLabel">Sembunyikan</span>
                            <svg id="timelineToggleIcon" class="w-3.5 h-3.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                        </button>
                    </div>

                    <!-- HORIZONTAL VISUAL STEPPER (Matches Mockup) -->
                    <div id="modalStatusStepper" class="p-4 bg-stone-50/70 rounded-2xl border border-stone-200 space-y-3.5 transition-all duration-300">
                        
                        <!-- 4 Step Icons Row -->
                        <div class="flex items-center justify-between px-2 sm:px-6">
                            <!-- Step 1: Pesanan Dibuat / Dibayar -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg {{ $stepperStage >= 1 ? 'bg-amber-100 text-amber-800 ring-2 ring-amber-300' : 'bg-stone-200 text-stone-400' }}">
                                    📦
                                </div>
                            </div>

                            <!-- Connector 1-2 -->
                            <div class="flex-1 border-t-2 border-dashed mx-2 {{ $stepperStage >= 2 ? 'border-amber-400' : 'border-stone-300' }}"></div>

                            <!-- Step 2: Diserahkan ke Kurir -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg {{ $stepperStage >= 2 ? 'bg-amber-100 text-amber-800 ring-2 ring-amber-300' : 'bg-stone-200 text-stone-400' }}">
                                    🚚
                                </div>
                            </div>

                            <!-- Connector 2-3 -->
                            <div class="flex-1 border-t-2 border-dashed mx-2 {{ $stepperStage >= 3 ? 'border-amber-400' : 'border-stone-300' }}"></div>

                            <!-- Step 3: Transit / Diantar Kurir -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg {{ $stepperStage >= 3 ? 'bg-amber-100 text-amber-800 ring-2 ring-amber-300' : 'bg-stone-200 text-stone-400' }}">
                                    🏢
                                </div>
                            </div>

                            <!-- Connector 3-4 -->
                            <div class="flex-1 border-t-2 border-dashed mx-2 {{ $stepperStage >= 4 ? 'border-emerald-500' : 'border-stone-300' }}"></div>

                            <!-- Step 4: Selesai / Diterima -->
                            <div class="flex flex-col items-center">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg {{ $stepperStage >= 4 ? 'bg-emerald-100 text-emerald-800 ring-2 ring-emerald-400' : 'bg-stone-200 text-stone-400' }}">
                                    🎁
                                </div>
                            </div>
                        </div>

                        <!-- Stepper Dots & Checkmarks Indicator (Matches Mockup) -->
                        <div class="flex items-center justify-between px-5 sm:px-9">
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold {{ $stepperStage >= 1 ? 'bg-amber-400 text-stone-950' : 'bg-stone-300 text-white' }}">✓</span>
                            <div class="flex-1 border-t border-dashed mx-2 {{ $stepperStage >= 2 ? 'border-amber-400' : 'border-stone-200' }}"></div>
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold {{ $stepperStage >= 2 ? 'bg-amber-400 text-stone-950' : 'bg-stone-300 text-white' }}">✓</span>
                            <div class="flex-1 border-t border-dashed mx-2 {{ $stepperStage >= 3 ? 'border-amber-400' : 'border-stone-200' }}"></div>
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold {{ $stepperStage >= 3 ? 'bg-amber-400 text-stone-950' : 'bg-stone-300 text-white' }}">✓</span>
                            <div class="flex-1 border-t border-dashed mx-2 {{ $stepperStage >= 4 ? 'border-emerald-400' : 'border-stone-200' }}"></div>
                            <span class="w-4 h-4 rounded-full flex items-center justify-center text-[10px] font-bold {{ $stepperStage >= 4 ? 'bg-emerald-500 text-white' : 'bg-stone-300 text-white' }}">✓</span>
                        </div>

                        <!-- Stepper Status Summary (Prominent & Centered) -->
                        <div class="text-center pt-1">
                            <div class="font-serif font-black text-sm sm:text-base text-stone-900">
                                {{ $stepHeadline }}
                            </div>
                            <p class="text-stone-500 text-[11px] mt-0.5 max-w-sm mx-auto">
                                {{ $stepSubtext }}
                            </p>

                            <!-- TOMBOL LACAK PESANAN (PERSIS SEPERTI GAMBAR 1) -->
                            <div class="pt-2">
                                <button type="button" onclick="openTrackingModal('{{ $order->order_number }}')" 
                                        class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-bold text-xs hover:underline transition cursor-pointer">
                                    <span>Lacak Pesanan</span>
                                    <span class="text-sm font-bold">&rarr;</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PRODUK YANG DIPESAN SECTION (PERSIS SEPERTI GAMBAR 1) -->
                <div class="pt-3 border-t border-stone-100 space-y-2.5">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-stone-600 font-medium">Produk yang dipesan dari <strong class="text-stone-900 font-bold">Aroma Palace Official</strong></span>
                        <a href="{{ route('products.index') }}" class="text-stone-400 hover:text-[#650506] font-bold text-sm leading-none">&rarr;</a>
                    </div>

                    @php $firstItem = $order->items->first(); @endphp
                    @if($firstItem)
                        <div class="flex items-center gap-3 bg-stone-50/70 p-3 rounded-2xl border border-stone-200">
                            <img src="{{ $firstItem->product_image ?? ($firstItem->product?->primary_image ?? 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=120&q=80') }}"
                                 alt="{{ $firstItem->product_name }}"
                                 class="w-12 h-12 rounded-xl object-cover bg-white border border-stone-200 shrink-0">
                            <div class="min-w-0">
                                <h5 class="font-bold text-stone-900 text-xs truncate">{{ $firstItem->product_name }}</h5>
                                <p class="text-[11px] text-stone-500 mt-0.5">
                                    {{ $firstItem->variant_name ?? 'Default' }} &bull; {{ $firstItem->quantity }}x &bull; 
                                    <span class="font-mono font-bold text-stone-800">Rp {{ number_format($firstItem->price, 0, ',', '.') }}</span>
                                    @if($order->items->count() > 1)
                                        <span class="text-stone-400 text-[10px] block mt-0.5">(+{{ $order->items->count() - 1 }} produk lainnya)</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

            </div>

            <!-- RIGHT COLUMN: Items & Payment Breakdown (5 Cols) -->
            <div class="lg:col-span-5 space-y-5 border-t lg:border-t-0 lg:border-l border-stone-200 lg:pl-7 text-xs">
                
                <!-- ITEMS PURCHASED PREVIEW -->
                <div class="space-y-3">
                    <span class="font-bold text-stone-900 text-xs uppercase tracking-wider block">Produk yang Dibeli</span>
                    <div class="divide-y divide-stone-100 max-h-48 overflow-y-auto pr-1">
                        @foreach($order->items as $item)
                            <div class="py-2.5 first:pt-0 last:pb-0 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <img src="{{ $item->product_image ?? ($item->product?->primary_image ?? 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=120&q=80') }}"
                                         alt="{{ $item->product_name }}"
                                         class="w-12 h-12 rounded-xl object-cover bg-stone-50 border border-stone-200 shrink-0">
                                    <div class="min-w-0">
                                        <h5 class="font-bold text-stone-900 text-xs truncate">{{ $item->product_name }}</h5>
                                        <p class="text-[11px] text-stone-500 mt-0.5">
                                            {{ $item->variant_name ?? 'Default' }} &bull; {{ $item->quantity }} &times; Rp {{ number_format($item->price, 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                                <span class="font-mono font-bold text-xs text-stone-900 shrink-0">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr class="border-stone-100">

                <!-- FINANCIAL CALCULATION BREAKDOWN (Exact match to mockup) -->
                <div class="space-y-2 text-xs">
                    <div class="flex justify-between text-stone-600">
                        <span>Subtotal ({{ $order->items->sum('quantity') }} Barang)</span>
                        <span class="font-mono font-medium text-stone-900">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-stone-600">
                        <span>Total Ongkos Kirim</span>
                        <span class="font-mono font-medium text-stone-900">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                    </div>

                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-emerald-600 font-semibold">
                            <span>Diskon Promosi</span>
                            <span class="font-mono">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif

                    <div class="flex justify-between text-stone-600">
                        <span>Biaya Layanan</span>
                        <span class="font-mono text-stone-900">Rp 0</span>
                    </div>

                    <div class="pt-2.5 border-t border-stone-200 flex justify-between items-baseline">
                        <span class="font-bold text-stone-900 text-sm">Total Belanja</span>
                        <span class="font-mono font-black text-base sm:text-lg text-stone-900">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <!-- ACTION BUTTONS (Matches Mockup) -->
                <div class="space-y-2.5 pt-2">
                    @if($isCompleted)
                        <!-- Tulis Ulasan / Beli Lagi Button -->
                        <a href="{{ route('products.index') }}" 
                           class="w-full py-3 px-4 rounded-xl bg-[#F5B800] hover:bg-[#E0A700] text-stone-950 font-bold text-xs sm:text-sm text-center block shadow-xs transition">
                            Tulis Ulasan / Beli Lagi
                        </a>
                    @elseif($isDelivered || $isShipped)
                        <!-- Konfirmasi Terima Pesanan Button -->
                        <form method="POST" action="{{ route('account.orders.confirm_received', $order->order_number) }}">
                            @csrf
                            <button type="submit" 
                                    class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm text-center shadow-xs transition cursor-pointer">
                                ✓ Konfirmasi Terima Pesanan
                            </button>
                        </form>
                    @elseif($isPending)
                        <!-- Bayar Sekarang Button -->
                        <a href="{{ route('account.orders.show', $order->order_number) }}" 
                           class="w-full py-3 px-4 rounded-xl bg-[#650506] hover:bg-[#4A070B] text-white font-bold text-xs sm:text-sm text-center block shadow-xs transition">
                            Selesaikan Pembayaran
                        </a>
                    @else
                        <!-- Hubungi Concierge Button -->
                        <a href="{{ route('products.index') }}" 
                           class="w-full py-3 px-4 rounded-xl bg-[#650506] hover:bg-[#4A070B] text-white font-bold text-xs sm:text-sm text-center block shadow-xs transition">
                            Katalog Parfum Eksklusif
                        </a>
                    @endif

                    <!-- Secondary Button: Chat Penjual (WhatsApp Concierge) -->
                    <a href="https://wa.me/6281188888888?text=Halo%20Aroma%20Palace%2C%20saya%20ingin%20bertanya%20mengenai%20pesanan%20%23{{ $order->order_number }}" 
                       target="_blank" rel="noopener noreferrer"
                       class="w-full py-2.5 px-4 rounded-xl bg-white border border-stone-300 hover:bg-stone-50 text-stone-800 font-bold text-xs text-center flex items-center justify-center gap-2 shadow-2xs transition">
                        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/>
                        </svg>
                        <span>Chat Penjual</span>
                    </a>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
    function toggleTrackingTimeline() {
        const stepper = document.getElementById('modalStatusStepper');
        const icon = document.getElementById('timelineToggleIcon');
        const label = document.getElementById('timelineToggleLabel');
        if (!stepper) return;

        if (stepper.classList.contains('hidden')) {
            stepper.classList.remove('hidden');
            if (label) label.textContent = 'Sembunyikan';
            if (icon) icon.classList.remove('rotate-180');
        } else {
            stepper.classList.add('hidden');
            if (label) label.textContent = 'Tampilkan';
            if (icon) icon.classList.add('rotate-180');
        }
    }
</script>
