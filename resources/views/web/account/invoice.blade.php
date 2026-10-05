<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }} - Aroma Palace</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .print-shadow-none { box-shadow: none !important; border: none !important; }
        }
    </style>
</head>
<body class="bg-stone-100 text-stone-800 antialiased p-4 sm:p-8 font-sans">
    
    <!-- Top Action Bar (No Print) -->
    <div class="max-w-3xl mx-auto mb-4 flex items-center justify-between no-print">
        <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-600 hover:text-stone-900 bg-white px-3 py-1.5 rounded-lg border border-stone-200 shadow-2xs">
            <span>&larr; Kembali ke Riwayat Pesanan</span>
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-[#650506] hover:bg-[#4A070B] px-4 py-1.5 rounded-lg shadow-xs cursor-pointer">
                <span>🖨️ Cetak Invoice</span>
            </button>
        </div>
    </div>

    <!-- Main Invoice Paper Document -->
    <div class="max-w-3xl mx-auto bg-white rounded-2xl sm:rounded-3xl p-6 sm:p-10 shadow-sm border border-stone-200 print-shadow-none space-y-6">
        
        <!-- Header: Brand & Invoice Meta -->
        <div class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 pb-6">
            <div>
                <span class="font-serif tracking-widest text-xs uppercase text-stone-400 font-bold">Haute Parfumerie</span>
                <h1 class="font-serif font-black text-2xl sm:text-3xl text-stone-900 tracking-tight">AROMA PALACE</h1>
                <p class="text-xs text-stone-500 mt-1">Jl. M.H. Thamrin No. 88, Menteng, Jakarta Pusat 10310</p>
                <p class="text-xs text-stone-500">support@aromapalace.id &bull; www.aromapalace.id</p>
            </div>

            <div class="text-left sm:text-right">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ in_array($order->order_status, ['completed', 'delivered', 'shipped', 'processing']) ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                    {{ $order->payment_status === 'paid' ? 'LUNAS' : strtoupper($order->payment_status) }}
                </span>
                <div class="mt-2 text-xs">
                    <span class="text-stone-400 block font-medium">Nomor Invoice:</span>
                    <strong class="font-mono text-sm sm:text-base text-stone-900 font-black">INV/{{ $order->created_at->format('Ymd') }}/AP/{{ str_replace('AP-', '', $order->order_number) }}</strong>
                </div>
                <div class="mt-1 text-xs">
                    <span class="text-stone-400">Tanggal Transaksi: </span>
                    <strong class="text-stone-800 font-medium">{{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</strong>
                </div>
            </div>
        </div>

        @php
            $snap = $order->shipping_address_snapshot ?? [];
            $recipientName = $snap['recipient_name'] ?? ($order->address?->recipient_name ?? ($order->user?->name ?? 'Pelanggan'));
            $phone = $snap['phone_number'] ?? ($order->address?->phone_number ?? '-');
            $fullAddress = ($snap['full_address'] ?? ($order->address?->full_address ?? '-')) . 
                (!empty($snap['city']) ? ', ' . $snap['city'] : '') . 
                (!empty($snap['province']) ? ', ' . $snap['province'] : '') . 
                (!empty($snap['postal_code']) ? ' ' . $snap['postal_code'] : '');
        @endphp

        <!-- 2-Column: Customer & Delivery Info -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs border-b border-stone-200 pb-6">
            <div>
                <span class="text-[10px] uppercase font-bold text-stone-400 block mb-1">Diterbitkan Untuk:</span>
                <strong class="font-bold text-sm text-stone-900 block">{{ $order->user?->name ?? 'Pelanggan Aroma Palace' }}</strong>
                <span class="text-stone-500 block">{{ $order->user?->email ?? '-' }}</span>
                <span class="text-stone-500 block font-mono">{{ $phone }}</span>
            </div>

            <div>
                <span class="text-[10px] uppercase font-bold text-stone-400 block mb-1">Pengiriman & Tujuan:</span>
                @if(in_array($order->fulfillment_type, ['pickup', 'store_pickup']))
                    <strong class="font-bold text-stone-900 block">Ambil di Butik (Click & Collect)</strong>
                    <span class="text-stone-600 block">{{ $order->store?->name ?? 'Aroma Palace Flagship Boutique' }}</span>
                    <span class="text-stone-500 block">{{ $order->store?->address ?? '-' }}</span>
                    <span class="text-[#650506] font-bold block mt-1">Kode Pickup: {{ $order->pickup_code ?? '-' }}</span>
                @else
                    <strong class="font-bold text-stone-900 block">{{ $order->shipping_courier ?? 'Kurir' }} - {{ $order->shipping_service ?? 'Reguler' }}</strong>
                    <span class="text-stone-600 block font-medium">Penerima: {{ $recipientName }}</span>
                    <p class="text-stone-500 leading-relaxed mt-0.5">{{ $fullAddress }}</p>
                    @if($order->tracking_number)
                        <span class="text-stone-700 font-mono block mt-1">No. Resi: <strong>{{ $order->tracking_number }}</strong></span>
                    @endif
                @endif
            </div>
        </div>

        <!-- Products Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead>
                    <tr class="border-b-2 border-stone-200 text-stone-400 font-bold uppercase text-[10px]">
                        <th class="py-2.5">Produk Wewangian</th>
                        <th class="py-2.5 text-center">Varian</th>
                        <th class="py-2.5 text-center">Jumlah</th>
                        <th class="py-2.5 text-right">Harga Satuan</th>
                        <th class="py-2.5 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($order->items as $item)
                        <tr>
                            <td class="py-3 font-bold text-stone-900">
                                {{ $item->product_name }}
                            </td>
                            <td class="py-3 text-center text-stone-600">
                                {{ $item->variant_name ?? 'Default' }}
                            </td>
                            <td class="py-3 text-center font-mono text-stone-800">
                                {{ $item->quantity }}
                            </td>
                            <td class="py-3 text-right font-mono text-stone-600">
                                Rp {{ number_format($item->price, 0, ',', '.') }}
                            </td>
                            <td class="py-3 text-right font-mono font-bold text-stone-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary Totals -->
        <div class="border-t-2 border-stone-200 pt-4 flex justify-end text-xs">
            <div class="w-full sm:w-72 space-y-2">
                <div class="flex justify-between text-stone-600">
                    <span>Subtotal Produk</span>
                    <span class="font-mono">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-stone-600">
                    <span>Ongkos Kirim</span>
                    <span class="font-mono">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
                @if($order->discount_amount > 0)
                    <div class="flex justify-between text-emerald-600 font-medium">
                        <span>Potongan Promosi</span>
                        <span class="font-mono">-Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-stone-600">
                    <span>Biaya Layanan</span>
                    <span class="font-mono">Rp 0</span>
                </div>
                <div class="pt-2 border-t border-stone-300 flex justify-between items-baseline font-bold text-stone-900 text-sm">
                    <span>Total Pembayaran</span>
                    <span class="font-mono text-base font-black text-[#650506]">
                        Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Footer / Authentic Signature Note -->
        <div class="pt-6 border-t border-dashed border-stone-200 text-center text-[11px] text-stone-400 space-y-1">
            <p>Terima kasih atas kepercayaan Anda memilih koleksi wewangian Haute Parfumerie Aroma Palace.</p>
            <p>Dokumen ini merupakan bukti transaksi elektronik sah dan tidak memerlukan tanda tangan basah.</p>
        </div>

    </div>

</body>
</html>
