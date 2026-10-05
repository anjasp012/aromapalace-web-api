@php
    $courierTracking = $tracking['courier_tracking'] ?? null;
    $hasCourierTracking = !empty($courierTracking) && !empty($courierTracking['milestones']);
    $isHomeDelivery = !in_array($order->fulfillment_type, ['pickup', 'store_pickup']);
    $snap = $order->shipping_address_snapshot ?? [];

    $recipientName = $snap['recipient_name'] ?? ($order->address?->recipient_name ?? ($order->user?->name ?? 'Pelanggan'));
    $phone = $snap['phone_number'] ?? ($order->address?->phone_number ?? '-');
    $fullAddress = ($snap['full_address'] ?? ($order->address?->full_address ?? '-')) . 
        (!empty($snap['district']) ? ', ' . $snap['district'] : '') . 
        (!empty($snap['city']) ? ', ' . $snap['city'] : '') . 
        (!empty($snap['province']) ? ', ' . $snap['province'] : '') . 
        (!empty($snap['postal_code']) ? ' ' . $snap['postal_code'] : '');

    $courierName = $courierTracking['courier'] ?? ($order->shipping_courier ?? 'Kurir Ekspedisi');
    $serviceName = $courierTracking['service'] ?? ($order->shipping_service ?? 'Reguler');
    $podImages = $courierTracking['pod_images'] ?? [];
    $milestones = $courierTracking['milestones'] ?? [];

    // Fallback milestone jika pengiriman internal butik
    if (empty($milestones) && !empty($tracking['timeline'])) {
        foreach ($tracking['timeline'] as $item) {
            $milestones[] = [
                'title' => $item['title'],
                'note' => $item['description'],
                'time' => $item['time'],
                'location' => 'Butik Aroma Palace',
            ];
        }
    }
@endphp

@php
    $courierLogoUrl = \App\Services\KiriminAjaShippingService::getCourierLogoUrl($courierName);
@endphp

<!-- TRACKING MODAL DIALOG (EXACT TOCO / MARKETPLACE STYLE) -->
<div class="bg-white rounded-3xl overflow-hidden shadow-2xl border border-stone-200">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4.5 border-b border-stone-100 bg-white sticky top-0 z-10">
        <div class="flex items-center gap-2">
            <h3 class="font-serif font-black text-stone-900 text-lg sm:text-xl tracking-tight">Lacak Pesanan</h3>
            @if(!empty($courierTracking['is_live']))
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                    LIVE
                </span>
            @endif
        </div>
        <button type="button" onclick="closeTrackingModal()" 
                class="w-9 h-9 rounded-full bg-stone-100 hover:bg-stone-200 text-stone-500 hover:text-stone-900 flex items-center justify-center transition cursor-pointer text-lg font-bold"
                aria-label="Tutup">
            &times;
        </button>
    </div>

    <!-- Body (2-Column Grid matching Image 2) -->
    <div class="p-6 sm:p-7 max-h-[80vh] overflow-y-auto">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT INFO CARD (md:col-span-5) -->
            <div class="md:col-span-5 bg-white p-5 rounded-2xl border border-stone-200 space-y-4 text-xs shadow-2xs">
                <!-- Courier Logo & Brand (Using KiriminAja CDN Logo) -->
                <div class="space-y-1.5">
                    @if($courierLogoUrl)
                        <div class="h-9 flex items-center">
                            <img src="{{ $courierLogoUrl }}" alt="{{ $courierName }}" class="h-8 max-w-[130px] object-contain">
                        </div>
                    @else
                        <div class="font-black text-stone-900 text-sm flex items-center gap-1.5">
                            <span>📦</span>
                            <span>{{ $courierName }}</span>
                        </div>
                    @endif
                    <div class="font-bold text-stone-900 text-xs">{{ $courierName }}</div>
                    <span class="text-[11px] text-stone-400 block">{{ $serviceName }}</span>
                </div>

                <hr class="border-stone-100">

                <!-- No. Resi -->
                <div>
                    <span class="text-[11px] text-stone-400 block font-medium">No. Resi</span>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="font-mono font-bold text-stone-900 text-xs sm:text-sm select-all">{{ $order->tracking_number ?? 'Belum terbit' }}</span>
                        @if($order->tracking_number)
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $order->tracking_number }}'); alert('Nomor Resi berhasil disalin!');" 
                                    class="text-stone-400 hover:text-stone-700 transition cursor-pointer p-0.5" title="Salin Resi">
                                <svg class="w-4 h-4 text-stone-500 hover:text-stone-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Penjual -->
                <div>
                    <span class="text-[11px] text-stone-400 block font-medium">Penjual</span>
                    <strong class="font-bold text-stone-900 block text-xs">Aroma Palace Official</strong>
                    <span class="text-[11px] text-stone-500">Jakarta Pusat, DKI Jakarta</span>
                </div>

                <!-- Penerima -->
                <div>
                    <span class="text-[11px] text-stone-400 block font-medium">Penerima</span>
                    <strong class="font-bold text-stone-900 block text-xs">{{ $recipientName }} ({{ $phone }})</strong>
                    <p class="text-stone-500 text-[11px] leading-relaxed mt-0.5">{{ $fullAddress }}</p>
                </div>
            </div>

            <!-- RIGHT TIMELINE COLUMN (md:col-span-7) -->
            <div class="md:col-span-7 space-y-4">
                <div class="relative pl-6 sm:pl-7 border-l-2 border-dashed border-blue-500 space-y-6 my-1">
                    @forelse(array_reverse($milestones) as $idx => $m)
                        @php
                            $isLatest = ($idx === 0);
                        @endphp
                        <div class="relative group">
                            <!-- Blue Checkmark Circle (Exact match to Image 2: White circle with blue border & blue checkmark) -->
                            <span class="absolute -left-[33px] sm:-left-[37px] top-0.5 w-5 h-5 rounded-full bg-white border-2 border-blue-600 text-blue-600 flex items-center justify-center text-[10px] font-bold shadow-2xs">
                                <svg class="w-3 h-3 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>

                            <div class="space-y-1">
                                <!-- Checkpoint Text / Note -->
                                <p class="text-xs sm:text-[13px] font-medium text-stone-800 leading-snug">
                                    {{ $m['note'] ?? ($m['description'] ?? 'Pembaruan lokasi paket.') }}
                                </p>

                                <!-- Lihat Bukti Pengiriman link jika ada POD image pada checkpoint terkirim -->
                                @if(!empty($podImages) && ($isLatest || str_contains(strtolower($m['note'] ?? ''), 'sampai') || str_contains(strtolower($m['note'] ?? ''), 'terima')))
                                    @php
                                        $podUrl = $podImages['camera_img'] ?? ($podImages['signature_img'] ?? null);
                                    @endphp
                                    @if($podUrl)
                                        <div class="pt-0.5">
                                            <a href="{{ $podUrl }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-bold text-xs inline-flex items-center gap-1 transition">
                                                <span>Lihat Bukti Pengiriman</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        </div>
                                    @endif
                                @endif

                                <!-- Timestamp -->
                                <div class="text-[11px] text-stone-400 font-mono">
                                    {{ $m['time'] }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-stone-400 text-xs italic py-4">
                            Belum ada pembaruan log pengiriman dari ekspedisi.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</div>
