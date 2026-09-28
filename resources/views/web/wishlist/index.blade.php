@extends('layouts.app')

@section('title', 'Wishlist - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6" data-wishlist-page>
    <!-- Breadcrumb -->
    <nav class="text-[11px] sm:text-xs text-gray-500 flex items-center gap-1.5 sm:gap-2">
        <a href="{{ route('home') }}" class="hover:text-[#650506] transition">Home</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Wishlist</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-wrap items-center justify-between gap-2.5 sm:gap-4 border-b border-gray-200/80 pb-3 sm:pb-5">
        <div>
            <span class="text-[10px] sm:text-xs uppercase tracking-widest text-gray-500 font-semibold block mb-0.5 sm:mb-1">Saved For Later</span>
            <h1 class="text-xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Wishlist</h1>
        </div>
        @if($wishlists->isNotEmpty())
            <div id="wishlist-total-wrapper" class="text-[11px] sm:text-xs text-gray-500 font-medium">
                Total Items: <span id="wishlist-total-count" class="font-bold text-gray-900">{{ $wishlists->count() }}</span>
            </div>
        @endif
    </div>

    <!-- Wishlist Items -->
    {{-- Empty state: shown initially if empty, or revealed by JS when last item removed --}}
    <div id="wishlist-empty-state"
         class="py-12 sm:py-16 text-center max-w-lg mx-auto space-y-3 sm:space-y-4 {{ $wishlists->isNotEmpty() ? 'hidden' : '' }}">
        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-[#F4F2EE] text-[#650506] rounded-full flex items-center justify-center mx-auto text-xl sm:text-2xl">
            <svg class="w-7 h-7 sm:w-8 sm:h-8 text-[#650506]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Your wishlist is empty</h3>
        <p class="text-[11px] sm:text-xs text-gray-500 max-w-sm mx-auto leading-relaxed">
            You haven't saved any items yet. Explore our luxury fragrances and essentials, and tap the heart icon to save your favorites here.
        </p>
        <div class="pt-2 sm:pt-4">
            <a href="{{ route('products.index') }}"
               class="bg-[#650506] hover:bg-[#4A070B] text-white font-semibold text-xs px-5 sm:px-6 py-2.5 sm:py-3 rounded-lg shadow-sm transition inline-block">
                Start Shopping &rarr;
            </a>
        </div>
    </div>

    @if($wishlists->isNotEmpty())
        <!-- Products Grid -->
        <div id="wishlist-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5 sm:gap-4 lg:gap-5">
            @foreach($wishlists as $item)
                @php $product = $item->product; @endphp
                <div data-wishlist-card data-product-id="{{ $product->id }}">
                    <x-product-card :product="$product" />
                </div>
            @endforeach
        </div>

        <!-- Footer Cross-Navigation -->
        <div id="wishlist-footer" class="pt-4 sm:pt-6 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3 sm:gap-4">
            <a href="{{ route('products.index') }}" class="text-[11px] sm:text-xs font-semibold text-gray-500 hover:text-black transition">
                &larr; Continue Shopping
            </a>
            <a href="{{ route('cart.index') }}" class="text-[11px] sm:text-xs font-semibold text-[#650506] hover:text-[#4A070B] transition flex items-center gap-1">
                <span>View Shopping Bag</span>
                <span>&rarr;</span>
            </a>
        </div>
    @endif
</div>
@endsection


