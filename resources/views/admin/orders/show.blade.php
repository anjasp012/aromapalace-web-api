@extends('layouts.admin')

@section('title', 'Detail Pesanan #' . $order->order_number)

@section('content')
<div class="space-y-6 max-w-5xl">
    <!-- Back button -->
    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-gray-500 hover:text-gray-900 transition">
        &larr; Kembali ke Daftar Pesanan
    </a>

    <!-- Order Header Info -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h3 class="text-2xl font-extrabold text-gray-900">#{{ $order->order_number }}</h3>
                <span class="px-3 py-1 text-xs font-bold rounded-full 
                    @if($order->order_status === 'completed') bg-emerald-100 text-emerald-800
                    @elseif($order->order_status === 'shipped') bg-blue-100 text-blue-800
                    @elseif($order->order_status === 'ready_for_pickup') bg-purple-100 text-purple-800
                    @elseif($order->order_status === 'processing') bg-amber-100 text-amber-800
                    @elseif($order->order_status === 'cancelled') bg-rose-100 text-rose-800
                    @else bg-gray-100 text-gray-800 @endif">
                    {{ strtoupper(str_replace('_', ' ', $order->order_status)) }}
                </span>
            </div>
            <p class="text-xs text-gray-400 mt-1">Dibuat pada {{ $order->created_at->format('d M Y, H:i:s') }}</p>
        </div>

        <div class="text-right">
            <span class="text-xs text-gray-500 uppercase tracking-wider block">Total Tagihan</span>
            <span class="text-2xl font-extrabold text-amber-600">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- Status Update Box -->
    <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-6 shadow-sm">
        <h4 class="font-bold text-gray-900 text-base mb-2">Perbarui Status Pesanan</h4>
        <p class="text-xs text-gray-600 mb-4">Setiap pembaruan status akan mencatat riwayat log dan mengirimkan notifikasi instan kepada customer.</p>

        <form method="POST" action="{{ route('admin.orders.update_status', $order->id) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Ubah Status Menjadi *</label>
                    <select name="order_status" id="statusSelect" required class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-amber-600 font-semibold">
                        <option value="processing" {{ $order->order_status === 'processing' ? 'selected' : '' }}>Processing (Sedang Disiapkan)</option>
                        <option value="shipped" {{ $order->order_status === 'shipped' ? 'selected' : '' }}>Shipped (Diserahkan ke Kurir)</option>
                        <option value="ready_for_pickup" {{ $order->order_status === 'ready_for_pickup' ? 'selected' : '' }}>Ready for Pickup (Siap di Toko)</option>
                        <option value="completed" {{ $order->order_status === 'completed' ? 'selected' : '' }}>Completed (Selesai Diterima)</option>
                        <option value="cancelled" {{ $order->order_status === 'cancelled' ? 'selected' : '' }}>Cancelled (Batalkan Pesanan)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nomor Resi Pengiriman</label>
                    <input type="text" name="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}" placeholder="Contoh: JNE018274928"
                        class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-amber-600">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Catatan Tambahan (Opsional)</label>
                    <input type="text" name="notes" placeholder="Catatan internal atau info paket..."
                        class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-amber-600">
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                @if($order->fulfillment_type === 'home_delivery' && empty($order->tracking_number))
                    <button type="submit" form="kiriminAjaForm" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl shadow transition flex items-center gap-1.5">
                        <span>📦</span>
                        <span>Auto-Generate Resi via KiriminAja</span>
                    </button>
                @else
                    <div></div>
                @endif

                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-sm px-6 py-2.5 rounded-xl shadow transition">
                    Simpan Perubahan Status
                </button>
            </div>
        </form>

        @if($order->fulfillment_type === 'home_delivery' && empty($order->tracking_number))
            <form id="kiriminAjaForm" method="POST" action="{{ route('admin.orders.kiriminaja_awb', $order->id) }}" class="hidden">
                @csrf
            </form>
        @endif
    </div>

    <!-- Details Grid (Items & Customer/Address) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Purchased Items (2 Cols) -->
        <div class="md:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
            <h4 class="font-bold text-gray-900 text-base border-b border-gray-100 pb-3">Daftar Produk Pesanan</h4>
            <div class="divide-y divide-gray-100">
                @foreach($order->items as $item)
                <div class="py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ $item->product_image ?? 'https://via.placeholder.com/60' }}" class="w-12 h-12 rounded-lg object-cover border border-gray-200" alt="{{ $item->product_name }}">
                        <div>
                            <h5 class="text-sm font-bold text-gray-900">{{ $item->product_name }}</h5>
                            <span class="text-xs text-gray-500">{{ $item->variant_name ?? 'Standard' }} &times; {{ $item->quantity }}</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-bold text-gray-900 block">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        <span class="text-xs text-gray-400">@ Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Cost Summary -->
            <div class="border-t border-gray-100 pt-4 space-y-2 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal Produk</span>
                    <span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Ongkos Kirim</span>
                    <span>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                </div>
                @if($order->discount_amount > 0)
                <div class="flex justify-between text-emerald-600 font-semibold">
                    <span>Potongan Diskon ({{ $order->promo_code }})</span>
                    <span>- Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                </div>
                @endif
                <div class="flex justify-between text-base font-extrabold text-gray-900 border-t border-gray-200 pt-3">
                    <span>Total Pembayaran</span>
                    <span class="text-amber-600">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Customer & Delivery Info (1 Col) -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-6">
            <!-- Customer -->
            <div>
                <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Informasi Customer</h5>
                <p class="font-bold text-gray-900 text-sm">{{ $order->user?->name ?? 'Guest' }}</p>
                <p class="text-xs text-gray-500">{{ $order->user?->email }}</p>
                <p class="text-xs text-gray-500">{{ $order->user?->phone ?? '-' }}</p>
            </div>

            <!-- Delivery / Pickup -->
            <div class="border-t border-gray-100 pt-4">
                <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pemenuhan Pesanan</h5>
                @if($order->fulfillment_type === 'store_pickup')
                    <span class="px-2 py-0.5 bg-purple-100 text-purple-800 text-xs font-bold rounded">Store Pickup (Ambil di Toko)</span>
                    <div class="mt-2 text-xs text-gray-600 space-y-1">
                        <p class="font-bold text-gray-900">{{ $order->store?->name }}</p>
                        <p>{{ $order->store?->address }}</p>
                        <p class="text-purple-700 font-bold mt-1">Kode Pickup: {{ $order->pickup_code }}</p>
                    </div>
                @else
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-xs font-bold rounded">Home Delivery</span>
                    <div class="mt-2 text-xs text-gray-600 space-y-1">
                        <p class="font-bold text-gray-900">{{ $order->shipping_courier }} ({{ $order->shipping_service }})</p>
                        @if($order->shipping_address_snapshot)
                            <p class="font-semibold">{{ $order->shipping_address_snapshot['recipient_name'] }} ({{ $order->shipping_address_snapshot['phone_number'] }})</p>
                            <p>{{ $order->shipping_address_snapshot['full_address'] }}, {{ $order->shipping_address_snapshot['city'] }}</p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Payment Info -->
            <div class="border-t border-gray-100 pt-4">
                <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pembayaran</h5>
                <p class="text-xs font-bold text-gray-900 uppercase">{{ str_replace('_', ' ', $order->payment_method) }}</p>
                <span class="px-2 py-0.5 text-xs font-bold rounded mt-1 inline-block {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    STATUS: {{ strtoupper($order->payment_status) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Status Timeline Log -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h4 class="font-bold text-gray-900 text-base mb-4">Riwayat Timeline Pesanan</h4>
        <div class="space-y-4 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-gray-200">
            @foreach($order->statusHistories as $log)
            <div class="relative flex items-start gap-4">
                <div class="w-7 h-7 rounded-full bg-amber-500 text-white flex items-center justify-center font-bold text-xs shrink-0 z-10">
                    ✓
                </div>
                <div>
                    <h6 class="text-sm font-bold text-gray-900">{{ $log->title }}</h6>
                    <p class="text-xs text-gray-600 mt-0.5">{{ $log->description }}</p>
                    <span class="text-xs text-gray-400 mt-1 block">{{ $log->created_at->format('d M Y, H:i') }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

