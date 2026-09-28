@extends('layouts.app')

@section('title', 'Order History - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6 pb-6 sm:pb-8">
    <!-- Breadcrumb & Header -->
    <div>
        <div class="flex items-center gap-2 text-[11px] sm:text-xs text-gray-500 mb-1.5 sm:mb-2">
            <a href="{{ route('home') }}" class="hover:text-[#650506]">Home</a>
            <span>/</span>
            <a href="{{ route('account.index') }}" class="hover:text-[#650506]">Account</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Order History</span>
        </div>
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-serif font-bold text-gray-900 tracking-tight">Order History</h1>
        <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1">Track order status, shipping details, and view your purchases.</p>
    </div>

    <!-- Navigation Tabs with Integrated Logout -->
    <div class="flex items-center justify-between gap-2 border-b border-gray-200 pb-2.5 sm:pb-3">
        <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto min-w-0 flex-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden text-xs sm:text-sm font-medium">
            <a href="{{ route('account.index') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-black transition shrink-0">Account Overview</a>
            <a href="{{ route('account.orders') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg bg-[#650506] text-white font-semibold shadow-xs shrink-0">Orders History</a>
            <a href="{{ route('account.rewards') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-black transition shrink-0">Rewards</a>
            <a href="{{ route('account.addresses') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-black transition shrink-0">Address Book</a>
            <a href="{{ route('account.points') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-black transition shrink-0">Riwayat Poin</a>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-lg border border-gray-200 hover:border-rose-200 hover:bg-rose-50 text-gray-500 hover:text-rose-600 text-[11px] sm:text-xs font-semibold transition cursor-pointer shrink-0" title="Keluar dari akun">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400 group-hover:text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>

    <!-- Status Filter Buttons -->
    <div class="flex flex-wrap gap-1.5 sm:gap-2 text-[11px] sm:text-xs font-medium">
        @php
            $currentStatus = request('status');
            $filters = [
                '' => 'All Orders',
                'pending_payment' => 'Awaiting Payment',
                'processing' => 'Processing',
                'shipped' => 'Shipped',
                'delivered' => 'Delivered',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ];
        @endphp
        @foreach($filters as $val => $label)
            <a href="{{ route('account.orders', $val ? ['status' => $val] : []) }}"
               class="px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-lg border transition {{ ($currentStatus === $val || (!$currentStatus && $val === '')) ? 'bg-[#650506] text-white border-[#650506] shadow-xs' : 'bg-white text-gray-600 border-gray-200 hover:border-[#650506] hover:text-[#650506]' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <!-- Orders List -->
    <div class="space-y-3 sm:space-y-4">
        @forelse($orders as $order)
            <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-gray-200 shadow-2xs hover:shadow-sm transition">
                <!-- Header: Order Number & Status -->
                <div class="flex flex-wrap items-center justify-between gap-2.5 sm:gap-4 pb-3 sm:pb-4 border-b border-gray-100">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-gray-100 text-gray-800 flex items-center justify-center font-bold text-xs sm:text-sm">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5 sm:gap-2">
                                <span class="font-bold text-gray-900 text-xs sm:text-sm font-mono">#{{ $order->order_number }}</span>
                                <span class="text-gray-400 text-xs">&bull; {{ $order->created_at->format('M d, Y') }}</span>
                            </div>
                            <span class="text-[10px] sm:text-xs text-gray-500">Method: {{ in_array($order->fulfillment_type, ['store_pickup', 'pickup']) ? 'Store Pickup' : 'Delivery (' . ($order->shipping_courier ?? 'Courier') . ')' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        @php
                            $statusStyles = [
                                'pending_payment' => 'bg-amber-50 text-amber-800 border-amber-200',
                                'processing' => 'bg-blue-50 text-blue-800 border-blue-200',
                                'shipped' => 'bg-purple-50 text-purple-800 border-purple-200',
                                'delivered' => 'bg-teal-50 text-teal-800 border-teal-200',
                                'completed' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                                'cancelled' => 'bg-rose-50 text-rose-800 border-rose-200',
                            ];
                            $style = $statusStyles[$order->order_status] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                        @endphp
                        <span class="px-2 sm:px-3 py-0.5 sm:py-1 text-[10px] sm:text-xs font-semibold rounded-md border {{ $style }}">
                            {{ strtoupper(str_replace('_', ' ', $order->order_status)) }}
                        </span>
                    </div>
                </div>

                <!-- Items Preview -->
                <div class="py-3 sm:py-4 space-y-2.5 sm:space-y-3">
                    @foreach($order->items as $item)
                        <div class="flex items-center justify-between gap-3 sm:gap-4">
                            <div class="flex items-center gap-2.5 sm:gap-4 min-w-0">
                                <img src="{{ $item->product?->images?->first()?->image_url ?? 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=150&q=80' }}"
                                     alt="{{ $item->product?->name ?? 'Product' }}"
                                     class="w-11 h-11 sm:w-14 sm:h-14 rounded-lg sm:rounded-xl object-cover border border-gray-200 bg-[#F4F2EE] shrink-0">
                                <div class="min-w-0">
                                    <h4 class="text-xs sm:text-sm font-bold text-gray-900 truncate">{{ $item->product?->name ?? 'Product' }}</h4>
                                    <p class="text-[10px] sm:text-xs text-gray-500">{{ $item->variant?->name ?? 'Default' }} &bull; {{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <span class="text-xs sm:text-sm font-bold text-gray-900 font-mono shrink-0">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </span>
                        </div>
                    @endforeach
                </div>

                <!-- Footer: Total & Actions -->
                <div class="flex flex-wrap items-center justify-between gap-3 sm:gap-4 pt-3 sm:pt-4 border-t border-gray-100 bg-gray-50 -mx-4 -mb-4 sm:-mx-6 sm:-mb-6 p-3.5 sm:p-6 rounded-b-xl sm:rounded-b-2xl">
                    <div>
                        <span class="text-[10px] sm:text-xs text-gray-500 block">Total:</span>
                        <span class="text-sm sm:text-base font-bold text-gray-900 font-mono">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        @if($order->tracking_number)
                            <span class="text-[10px] sm:text-xs text-gray-500 block mt-0.5">Tracking: <code class="bg-gray-200 px-1.5 py-0.5 rounded text-gray-800 font-mono">{{ $order->tracking_number }}</code></span>
                        @endif
                    </div>

                    <div>
                        <a href="{{ route('account.orders.show', $order->order_number) }}"
                           class="inline-flex items-center gap-1 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg bg-[#650506] text-white font-medium text-[11px] sm:text-xs hover:bg-[#4A070B] transition">
                            <span>View Details</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12 sm:py-16 bg-white rounded-xl border border-gray-200 p-6 sm:p-8">
                <h3 class="text-sm sm:text-base font-bold text-gray-900">No orders found</h3>
                <p class="text-xs text-gray-500 max-w-sm mx-auto mt-1 mb-4">You have no orders matching the selected filter.</p>
                <a href="{{ route('products.index') }}" class="inline-flex items-center px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg bg-[#650506] text-white text-xs font-semibold hover:bg-[#4A070B] transition">
                    Start Shopping &rarr;
                </a>
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
