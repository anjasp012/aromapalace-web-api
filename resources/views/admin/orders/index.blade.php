@extends('layouts.admin')

@section('title', 'Manajemen Pesanan & Transaksi')

@section('content')
<div class="space-y-6">
    
    <!-- Top Summary Stat Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <!-- All Orders -->
        <a href="{{ route('admin.orders.index') }}" 
           class="p-4 rounded-2xl bg-white border border-stone-200/80 shadow-2xs hover:shadow-sm transition {{ empty($status) ? 'ring-2 ring-[#38050D] bg-stone-50/50' : '' }}">
            <span class="text-[11px] font-bold uppercase tracking-wider text-stone-500 block">Semua Pesanan</span>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-xl sm:text-2xl font-black font-mono text-stone-900">{{ $counts['all'] ?? 0 }}</span>
                <span class="text-[10px] text-stone-400">Total</span>
            </div>
        </a>

        <!-- Pending Payment -->
        <a href="{{ route('admin.orders.index', ['status' => 'pending_payment']) }}" 
           class="p-4 rounded-2xl bg-white border border-stone-200/80 shadow-2xs hover:shadow-sm transition {{ $status === 'pending_payment' ? 'ring-2 ring-amber-500 bg-amber-50/30' : '' }}">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-amber-500 {{ ($counts['pending_payment'] ?? 0) > 0 ? 'animate-pulse' : '' }}"></span>
                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800">Belum Bayar</span>
            </div>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-xl sm:text-2xl font-black font-mono text-amber-700">{{ $counts['pending_payment'] ?? 0 }}</span>
                <span class="text-[10px] text-amber-600/70">Tagihan</span>
            </div>
        </a>

        <!-- Processing (Needs packing) -->
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" 
           class="p-4 rounded-2xl bg-white border border-stone-200/80 shadow-2xs hover:shadow-sm transition {{ $status === 'processing' ? 'ring-2 ring-blue-500 bg-blue-50/30' : '' }}">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-800">Perlu Diproses</span>
            </div>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-xl sm:text-2xl font-black font-mono text-blue-700">{{ $counts['processing'] ?? 0 }}</span>
                <span class="text-[10px] text-blue-600/70">Kemas</span>
            </div>
        </a>

        <!-- Shipped -->
        <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" 
           class="p-4 rounded-2xl bg-white border border-stone-200/80 shadow-2xs hover:shadow-sm transition {{ $status === 'shipped' ? 'ring-2 ring-purple-500 bg-purple-50/30' : '' }}">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                <span class="text-[11px] font-bold uppercase tracking-wider text-purple-800">Dikirim</span>
            </div>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-xl sm:text-2xl font-black font-mono text-purple-700">{{ $counts['shipped'] ?? 0 }}</span>
                <span class="text-[10px] text-purple-600/70">Kurir</span>
            </div>
        </a>

        <!-- Ready for Pickup -->
        <a href="{{ route('admin.orders.index', ['status' => 'ready_for_pickup']) }}" 
           class="p-4 rounded-2xl bg-white border border-stone-200/80 shadow-2xs hover:shadow-sm transition {{ $status === 'ready_for_pickup' ? 'ring-2 ring-indigo-500 bg-indigo-50/30' : '' }}">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-800">Siap Ambil</span>
            </div>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-xl sm:text-2xl font-black font-mono text-indigo-700">{{ $counts['ready_for_pickup'] ?? 0 }}</span>
                <span class="text-[10px] text-indigo-600/70">Butik</span>
            </div>
        </a>

        <!-- Completed -->
        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" 
           class="p-4 rounded-2xl bg-white border border-stone-200/80 shadow-2xs hover:shadow-sm transition {{ $status === 'completed' ? 'ring-2 ring-emerald-500 bg-emerald-50/30' : '' }}">
            <div class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Selesai</span>
            </div>
            <div class="flex items-baseline justify-between mt-1">
                <span class="text-xl sm:text-2xl font-black font-mono text-emerald-700">{{ $counts['completed'] ?? 0 }}</span>
                <span class="text-[10px] text-emerald-600/70">Sukses</span>
            </div>
        </a>
    </div>

    <!-- Filter Pills & Search Bar -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-stone-200/80 shadow-2xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Status Filter Links -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 lg:pb-0 text-xs font-semibold">
            @php
                $tabFilters = [
                    '' => 'Semua (' . ($counts['all'] ?? 0) . ')',
                    'pending_payment' => 'Menunggu Bayar (' . ($counts['pending_payment'] ?? 0) . ')',
                    'processing' => 'Diproses (' . ($counts['processing'] ?? 0) . ')',
                    'shipped' => 'Dikirim (' . ($counts['shipped'] ?? 0) . ')',
                    'ready_for_pickup' => 'Siap Pickup (' . ($counts['ready_for_pickup'] ?? 0) . ')',
                    'completed' => 'Selesai (' . ($counts['completed'] ?? 0) . ')',
                    'cancelled' => 'Dibatalkan (' . ($counts['cancelled'] ?? 0) . ')',
                ];
            @endphp
            @foreach($tabFilters as $key => $tabLabel)
                <a href="{{ route('admin.orders.index', array_merge(request()->except('page'), ['status' => $key])) }}"
                   class="px-3.5 py-1.5 rounded-xl whitespace-nowrap transition {{ ($status === $key || (empty($status) && $key === '')) ? 'bg-[#38050D] text-white shadow-xs font-bold' : 'bg-stone-50 text-stone-600 hover:bg-stone-100 border border-stone-200' }}">
                    {{ $tabLabel }}
                </a>
            @endforeach
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex items-center gap-2 w-full lg:w-80 shrink-0">
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <div class="relative w-full">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari No. Order / Customer / Resi..."
                       class="w-full bg-stone-50 border border-stone-300 rounded-xl pl-9 pr-3 py-2 text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#38050D]">
                <svg class="w-4 h-4 text-stone-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button type="submit" class="bg-[#38050D] hover:bg-[#520813] text-white text-xs font-bold px-3.5 py-2 rounded-xl transition shrink-0">
                Cari
            </button>
            @if($search)
                <a href="{{ route('admin.orders.index', $status ? ['status' => $status] : []) }}" class="text-xs text-stone-500 hover:text-stone-800 underline shrink-0">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Orders Table Container -->
    <div class="bg-white rounded-2xl border border-stone-200/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#FAF8F5] text-stone-600 uppercase text-[10px] font-bold tracking-wider border-b border-stone-200">
                    <tr>
                        <th class="px-5 py-3.5">Pesanan & Tanggal</th>
                        <th class="px-5 py-3.5">Pelanggan</th>
                        <th class="px-5 py-3.5">Nominal Tagihan</th>
                        <th class="px-5 py-3.5">Status Pembayaran</th>
                        <th class="px-5 py-3.5">Status Pemrosesan</th>
                        <th class="px-5 py-3.5">Pengiriman / Butik</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($orders as $order)
                        @php
                            $isPaid = ($order->payment_status === 'paid');
                            $methodCode = $order->payment?->payment_type ?? $order->payment_method;
                        @endphp
                        <tr class="hover:bg-stone-50/80 transition">
                            <!-- Column 1: Order No & Date -->
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="font-mono font-bold text-stone-900 text-sm hover:text-[#650506] block">
                                    #{{ $order->order_number }}
                                </a>
                                <span class="text-[11px] text-stone-400 block mt-0.5">
                                    {{ $order->created_at->format('d M Y, H:i') }}
                                </span>
                                <span class="text-[10px] text-stone-500 mt-1 inline-block">
                                    {{ $order->items->count() }} Produk Parfum
                                </span>
                            </td>

                            <!-- Column 2: Customer -->
                            <td class="px-5 py-4">
                                <span class="font-bold text-stone-900 block">{{ $order->user?->name ?? 'Guest User' }}</span>
                                <span class="text-[11px] text-stone-500 block">{{ $order->user?->email ?? '-' }}</span>
                                @if(!empty($order->shipping_address_snapshot['phone_number']) || !empty($order->address?->phone_number))
                                    <span class="text-[10px] text-stone-400 block mt-0.5">
                                        {{ $order->shipping_address_snapshot['phone_number'] ?? $order->address?->phone_number }}
                                    </span>
                                @endif
                            </td>

                            <!-- Column 3: Total Tagihan / Nominal (Prominent Display!) -->
                            <td class="px-5 py-4">
                                <span class="font-mono font-black text-sm sm:text-base text-[#650506] block">
                                    Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                                </span>
                                <span class="text-[10px] text-stone-400 block">
                                    Ongkir: Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}
                                </span>
                            </td>

                            <!-- Column 4: Payment Status & Method -->
                            <td class="px-5 py-4">
                                @if($isPaid)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <svg class="w-3 h-3 text-emerald-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                        LUNAS
                                    </span>
                                @elseif($order->order_status === 'cancelled')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-stone-100 text-stone-600 border border-stone-200">
                                        BATAL
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse"></span>
                                        BELUM BAYAR
                                    </span>
                                @endif
                                <span class="text-[10px] text-stone-500 font-semibold block mt-1 uppercase">
                                    {{ strtoupper(str_replace('_', ' ', $methodCode)) }}
                                </span>
                            </td>

                            <!-- Column 5: Order / Fulfillment Status -->
                            <td class="px-5 py-4">
                                @php
                                    $orderStyles = [
                                        'pending_payment' => 'bg-amber-50 text-amber-900 border-amber-200',
                                        'processing' => 'bg-blue-50 text-blue-900 border-blue-200',
                                        'shipped' => 'bg-purple-50 text-purple-900 border-purple-200',
                                        'ready_for_pickup' => 'bg-indigo-50 text-indigo-900 border-indigo-200',
                                        'delivered' => 'bg-teal-50 text-teal-900 border-teal-200',
                                        'completed' => 'bg-emerald-50 text-emerald-900 border-emerald-200',
                                        'cancelled' => 'bg-rose-50 text-rose-900 border-rose-200',
                                    ];
                                    $orderLabels = [
                                        'pending_payment' => 'Menunggu Bayar',
                                        'processing' => 'Perlu Dikemas',
                                        'shipped' => 'Dikirimkan',
                                        'ready_for_pickup' => 'Siap Pickup',
                                        'delivered' => 'Tiba di Tujuan',
                                        'completed' => 'Selesai',
                                        'cancelled' => 'Dibatalkan',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg border {{ $orderStyles[$order->order_status] ?? 'bg-stone-100 text-stone-700 border-stone-200' }}">
                                    {{ $orderLabels[$order->order_status] ?? strtoupper(str_replace('_', ' ', $order->order_status)) }}
                                </span>
                            </td>

                            <!-- Column 6: Courier / Store Pickup -->
                            <td class="px-5 py-4">
                                @if($order->fulfillment_type === 'store_pickup')
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-800 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-200">
                                        🏬 {{ $order->store?->name ?? 'Butik Toko' }}
                                    </span>
                                    <span class="block text-[10px] text-stone-500 font-mono mt-0.5">Kode: {{ $order->pickup_code ?? '-' }}</span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-800 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200">
                                        🚚 {{ $order->shipping_courier ?? 'Kurir' }} {{ $order->shipping_service ? '(' . $order->shipping_service . ')' : '' }}
                                    </span>
                                    @if($order->tracking_number)
                                        <span class="block text-[10px] text-stone-600 font-mono mt-0.5">Resi: {{ $order->tracking_number }}</span>
                                    @endif
                                @endif
                            </td>

                            <!-- Column 7: Actions -->
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if(in_array($order->order_status, ['pending_payment', 'processing']))
                                        <form method="POST" action="{{ route('admin.orders.accept', $order->id) }}" class="inline">
                                            @csrf
                                            <button type="submit" title="Terima Pesanan & Terbitkan Resi Otomatis" 
                                                    class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2.5 py-1.5 rounded-xl text-xs shadow-xs transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                <span>Terima</span>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.orders.reject', $order->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin MENOLAK pesanan #{{ $order->order_number }}? Stok produk akan otomatis dikembalikan.');">
                                            @csrf
                                            <button type="submit" title="Tolak Pesanan" 
                                                    class="inline-flex items-center bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-300 font-bold p-1.5 rounded-xl text-xs shadow-2xs transition cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    @endif

                                    <a href="{{ route('admin.orders.show', $order->id) }}" 
                                       class="inline-flex items-center gap-1 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold px-2.5 py-1.5 rounded-xl text-xs border border-stone-200 transition">
                                        <span>Detail</span>
                                        <span>&rarr;</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-stone-400">
                                <div class="w-12 h-12 rounded-full bg-stone-100 text-stone-400 mx-auto flex items-center justify-center mb-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <span class="font-semibold text-stone-600 block">Tidak ada data pesanan</span>
                                <span class="text-[11px] text-stone-400">Pesanan pelanggan akan otomatis muncul di tabel ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-stone-200 bg-[#FAF8F5]">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
