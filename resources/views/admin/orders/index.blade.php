@extends('layouts.admin')

@section('title', 'Manajemen Pesanan & Pengiriman')

@section('content')
<div class="space-y-6">
    <!-- Status Filter Tabs -->
    <div class="flex flex-wrap gap-2 border-b border-gray-200 pb-4">
        <a href="{{ route('admin.orders.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ empty($status) ? 'bg-amber-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            Semua Pesanan ({{ $counts['all'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending_payment']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'pending_payment' ? 'bg-amber-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            Menunggu Bayar ({{ $counts['pending_payment'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'processing' ? 'bg-amber-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            Perlu Diproses ({{ $counts['processing'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'shipped' ? 'bg-amber-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            Dalam Pengiriman ({{ $counts['shipped'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'ready_for_pickup']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'ready_for_pickup' ? 'bg-amber-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            Siap Pickup Toko ({{ $counts['ready_for_pickup'] }})
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $status === 'completed' ? 'bg-amber-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200' }}">
            Selesai ({{ $counts['completed'] }})
        </a>
    </div>

    <!-- Search Box -->
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex items-center gap-3 w-full max-w-md">
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari no. order, resi, atau nama customer..."
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-600">
            <button type="submit" class="bg-stone-800 hover:bg-stone-900 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition">
                Cari
            </button>
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">No. Order & Tanggal</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Metode Kirim / Toko</th>
                        <th class="px-6 py-4">Total Belanja</th>
                        <th class="px-6 py-4">Pembayaran</th>
                        <th class="px-6 py-4">Status Pesanan</th>
                        <th class="px-6 py-4 text-right">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="px-6 py-4">
                            <span class="font-extrabold text-gray-900 block">#{{ $order->order_number }}</span>
                            <span class="text-xs text-gray-400">{{ $order->created_at->format('d M Y, H:i') }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-800 block">{{ $order->user?->name ?? 'Guest Customer' }}</span>
                            <span class="text-xs text-gray-400">{{ $order->user?->email ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($order->fulfillment_type === 'store_pickup')
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-md">
                                    Pickup: {{ $order->store?->name ?? 'Gerai Toko' }}
                                </span>
                                <span class="block text-xs text-gray-500 mt-0.5">Kode: {{ $order->pickup_code }}</span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md">
                                    Delivery: {{ $order->shipping_courier }}
                                </span>
                                @if($order->tracking_number)
                                    <span class="block text-xs text-gray-500 mt-0.5 font-mono">Resi: {{ $order->tracking_number }}</span>
                                @endif
                            @endif
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-0.5 text-xs font-bold rounded {{ $order->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ strtoupper($order->payment_status) }}
                            </span>
                            <span class="block text-xs text-gray-400 mt-0.5">{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-full 
                                @if($order->order_status === 'completed') bg-emerald-100 text-emerald-800
                                @elseif($order->order_status === 'shipped') bg-blue-100 text-blue-800
                                @elseif($order->order_status === 'ready_for_pickup') bg-purple-100 text-purple-800
                                @elseif($order->order_status === 'processing') bg-amber-100 text-amber-800
                                @elseif($order->order_status === 'cancelled') bg-rose-100 text-rose-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ strtoupper(str_replace('_', ' ', $order->order_status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center gap-1 bg-amber-50 text-amber-700 hover:bg-amber-100 font-semibold px-3 py-1.5 rounded-lg text-xs transition">
                                Kelola &rarr;
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">Tidak ada data pesanan ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection

