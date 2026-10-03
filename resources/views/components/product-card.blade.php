@props([
    'product',
    'badge' => null,
    'priceStacked' => false,
    'isDark' => false,
])

@php
    $discountPercent = $product->discount_percent ?? 0;
    if (!$discountPercent && $product->discount_price && $product->base_price > $product->discount_price) {
        $discountPercent = round((($product->base_price - $product->discount_price) / $product->base_price) * 100);
    }
    
    $finalPrice = $product->final_price ?? ($product->discount_price ?: $product->base_price);
    $hasDiscount = ($discountPercent > 0) || ($product->discount_price && $product->discount_price < $product->base_price);
    $reviewsCount = $product->reviews_count ?: (($product->id * 31 + 47) % 200 + 48);
    $imageSrc = $product->primary_image ?: 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=600&q=80';

    static $userWishlistIds = null;
    if ($userWishlistIds === null) {
        if (auth()->check()) {
            $userWishlistIds = \App\Models\Wishlist::where('user_id', auth()->id())->pluck('product_id')->toArray();
        } else {
            $userWishlistIds = [];
        }
    }
    $isWishlisted = in_array($product->id, $userWishlistIds);
@endphp

<div {{ $attributes->merge(['class' => 'group relative flex flex-col justify-between transition-all duration-300']) }}>
    <!-- Image Box (Full-Bleed Thumbnail, Rounded-lg) -->
    <div class="relative bg-gray-100 rounded-lg aspect-square overflow-hidden">
        <a href="{{ route('products.show', $product->slug) }}" class="w-full h-full block">
            <img src="{{ $imageSrc }}" 
                 alt="{{ $product->name }}" 
                 loading="lazy"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
        </a>

        <!-- Badge (Top-Left Pill in Royal Maroon) -->
        <div class="absolute top-2 left-2 sm:top-3 sm:left-3 flex flex-col gap-1 pointer-events-none">
            @if($badge)
                <span class="bg-[#650506] text-white text-[9.5px] sm:text-[11px] font-semibold px-1.5 sm:px-2 py-0.5 rounded-md shadow-2xs">
                    {{ $badge }}
                </span>
            @elseif($hasDiscount)
                <span class="bg-[#650506] text-white text-[9.5px] sm:text-[11px] font-semibold px-1.5 sm:px-2 py-0.5 rounded-md shadow-2xs">
                    -{{ $discountPercent }}%
                </span>
            @elseif($product->is_featured)
                <span class="bg-white text-gray-800 text-[9.5px] sm:text-[11px] font-medium px-1.5 sm:px-2 py-0.5 rounded-md border border-gray-200/80">
                    Baru
                </span>
            @elseif($product->is_popular)
                <span class="bg-white text-gray-800 text-[9.5px] sm:text-[11px] font-medium px-1.5 sm:px-2 py-0.5 rounded-md border border-gray-200/80">
                    Populer
                </span>
            @endif
        </div>

        <!-- Wishlist Button (Top-Right) -->
        @guest
            <a href="{{ route('login') }}" 
               onclick="event.stopPropagation();"
               title="Masuk untuk menambahkan ke Wishlist"
               aria-label="Wishlist"
               class="absolute top-2 right-2 sm:top-3 sm:right-3 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/90 hover:bg-white text-gray-500 opacity-0 group-hover:opacity-100 sm:opacity-0 focus:opacity-100 hover:text-rose-500 shadow-2xs flex items-center justify-center transition-all hover:scale-110 z-10 cursor-pointer">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </a>
        @else
            <button type="button" 
                    onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist({{ $product->id }}, this)"
                    data-product-id="{{ $product->id }}"
                    title="{{ $isWishlisted ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist' }}"
                    aria-label="Wishlist"
                    class="absolute top-2 right-2 sm:top-3 sm:right-3 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/90 hover:bg-white {{ $isWishlisted ? 'text-rose-500 opacity-100' : 'text-gray-500 opacity-0 group-hover:opacity-100 sm:opacity-0 focus:opacity-100' }} hover:text-rose-500 shadow-2xs flex items-center justify-center transition-all hover:scale-110 z-10 cursor-pointer">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 {{ $isWishlisted ? 'fill-rose-500 text-rose-500' : '' }}" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </button>
        @endguest
    </div>

    <!-- Product Typography -->
    <div class="pt-2 sm:pt-3 flex-1 flex flex-col justify-between">
        <div>
            <!-- Product Title -->
            <h3 class="font-semibold text-xs sm:text-base leading-snug line-clamp-1 {{ $isDark ? 'text-white group-hover:text-amber-200' : 'text-gray-900 group-hover:text-black' }}">
                <a href="{{ route('products.show', $product->slug) }}">
                    {{ $product->name }}
                </a>
            </h3>

            <!-- Subtitle / Brand / Category Descriptor -->
            <p class="text-[10px] sm:text-xs mt-0.5 line-clamp-1 font-normal {{ $isDark ? 'text-stone-300' : 'text-gray-500' }}">
                {{ $product->brand?->name ?? 'Aroma Palace' }} &middot; {{ $product->category?->name ?? 'Parfum' }}
            </p>
        </div>

        <div>
            <!-- Price Row -->
            @if($priceStacked)
                <div class="mt-1 sm:mt-1.5 min-h-[32px] sm:min-h-[38px] flex flex-col justify-center">
                    <span class="font-bold text-xs sm:text-base leading-tight {{ $isDark ? 'text-white' : 'text-gray-900' }}">
                        Rp {{ number_format($finalPrice, 0, ',', '.') }}
                    </span>
                    @if($hasDiscount)
                        <span class="text-[10px] sm:text-[11px] line-through font-normal leading-tight mt-0.5 {{ $isDark ? 'text-stone-300/80' : 'text-gray-400' }}">
                            Rp {{ number_format($product->base_price, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
            @else
                <div class="mt-1 sm:mt-1.5 flex items-baseline gap-1.5 sm:gap-2">
                    <span class="font-bold text-xs sm:text-base {{ $isDark ? 'text-white' : 'text-gray-900' }}">
                        Rp {{ number_format($finalPrice, 0, ',', '.') }}
                    </span>
                    @if($hasDiscount)
                        <span class="text-[10px] sm:text-xs line-through font-normal {{ $isDark ? 'text-stone-300/80' : 'text-gray-400' }}">
                            Rp {{ number_format($product->base_price, 0, ',', '.') }}
                        </span>
                    @endif
                </div>
            @endif

            <!-- Rating Stars + Review Count -->
            <div class="mt-1 sm:mt-1.5 flex items-center gap-1 sm:gap-1.5">
                <div class="flex text-amber-400 text-[10px] sm:text-xs">
                    @for($s = 0; $s < 5; $s++)
                        <span>★</span>
                    @endfor
                </div>
                <span class="text-[10px] sm:text-xs font-normal {{ $isDark ? 'text-stone-300' : 'text-gray-400' }}">({{ $reviewsCount }})</span>
            </div>
        </div>

        @if(!$slot->isEmpty())
            <div class="pt-3 mt-3 border-t border-gray-100">
                {{ $slot }}
            </div>
        @endif
    </div>
</div>


