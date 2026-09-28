@extends('layouts.app')

@section('title', $product->name . ' - Aroma Palace')
@section('meta_description', Str::limit(strip_tags($product->description), 155))
@section('meta_keywords', ($product->brand->name ?? 'Brand') . ', ' . $product->name . ', luxury, minimalist, ' . ($product->category->name ?? 'lifestyle'))
@section('canonical', route('products.show', $product->slug))
@section('og_type', 'product')
@section('og_image', $product->primary_image)

@push('schema')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@graph": [
    {
      "@@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "{{ route('home') }}"
        },
        {
          "@@type": "ListItem",
          "position": 2,
          "name": "Shop",
          "item": "{{ route('products.index') }}"
        },
        {
          "@@type": "ListItem",
          "position": 3,
          "name": "{{ $product->name }}",
          "item": "{{ route('products.show', $product->slug) }}"
        }
      ]
    },
    {
      "@@type": "Product",
      "name": "{{ $product->name }}",
      "image": "{{ $product->primary_image }}",
      "description": "{{ addslashes(Str::limit(strip_tags($product->description), 200)) }}",
      "sku": "AP-{{ $product->id }}",
      "brand": {
        "@@type": "Brand",
        "name": "{{ $product->brand->name ?? 'Aroma Palace' }}"
      },
      "category": "{{ $product->category->name ?? 'Essentials' }}",
      "offers": {
        "@@type": "Offer",
        "url": "{{ route('products.show', $product->slug) }}",
        "priceCurrency": "IDR",
        "price": "{{ $product->final_price ?? $product->base_price }}",
        "priceValidUntil": "{{ now()->addYear()->format('Y-12-31') }}",
        "availability": "{{ $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
        "itemCondition": "https://schema.org/NewCondition"
      }
    }
  ]
}
</script>
@endpush

@section('content')
@php
    $isWishlisted = auth()->check()
        ? \App\Models\Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->exists()
        : false;
@endphp

<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-6 sm:space-y-12">
    <!-- Breadcrumb -->
    <nav class="text-[11px] sm:text-xs text-gray-500 flex items-center gap-1.5 sm:gap-2">
        <a href="{{ route('home') }}" class="hover:text-[#650506] transition">Home</a>
        <span>/</span>
        <a href="{{ route('products.index') }}" class="hover:text-[#650506] transition">Shop</a>
        @if($product->category)
            <span>/</span>
            <a href="{{ route('products.index', ['category' => $product->category->slug]) }}" class="hover:text-[#650506] transition">{{ $product->category->name }}</a>
        @endif
        <span>/</span>
        <span class="text-gray-900 font-medium truncate max-w-xs">{{ $product->name }}</span>
    </nav>

    <!-- Product Overview Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-10 lg:gap-14 items-start">
        @php
            $galleryImages = collect([$product->primary_image])
                ->merge($product->images->pluck('image_url'))
                ->filter()
                ->unique()
                ->values();
        @endphp

        <!-- Images Gallery (7 cols) -->
        <div class="lg:col-span-7" 
             x-data="{
                images: {{ json_encode($galleryImages) }},
                currentIndex: 0,
                isLightboxOpen: false,
                isPaused: false,
                timer: null,
                get activeImage() {
                    return this.images[this.currentIndex] || '{{ $product->primary_image }}';
                },
                init() {
                    if (this.images.length > 1) {
                        this.startAutoSlide();
                    }
                },
                startAutoSlide() {
                    this.stopAutoSlide();
                    this.timer = setInterval(() => {
                        if (!this.isPaused && !this.isLightboxOpen) {
                            this.nextImage();
                        }
                    }, 3500);
                },
                stopAutoSlide() {
                    if (this.timer) {
                        clearInterval(this.timer);
                        this.timer = null;
                    }
                },
                nextImage() {
                    this.currentIndex = (this.currentIndex + 1) % this.images.length;
                },
                selectImage(idx) {
                    this.currentIndex = idx;
                    this.startAutoSlide();
                }
             }">
            <!-- Main Featured Image (Full bleed, crisp, rounded-xl sm:rounded-2xl) -->
            <div class="relative rounded-xl sm:rounded-2xl overflow-hidden bg-stone-100/60 border border-gray-200/90 aspect-square shadow-2xs group"
                 @mouseenter="isPaused = true" 
                 @mouseleave="isPaused = false">
                
                <img :src="activeImage" 
                     src="{{ $product->primary_image }}" 
                     class="w-full h-full object-cover transition-all duration-700 group-hover:scale-105 cursor-zoom-in" 
                     @click="isLightboxOpen = true"
                     alt="{{ $product->name }}">

                <!-- Discount Badge (Top Left) -->
                @if($product->is_discount && $product->discount_percent > 0)
                    <span class="absolute top-2.5 left-2.5 sm:top-4 sm:left-4 z-10 px-2 sm:px-3 py-0.5 sm:py-1 bg-[#650506] text-white text-[10px] sm:text-xs font-extrabold uppercase tracking-wider rounded-md sm:rounded-lg shadow-sm">
                        Hemat {{ $product->discount_percent }}%
                    </span>
                @endif

                <!-- Magnifying Glass / Zoom Button (Top Right) -->
                <button type="button" 
                        @click="isLightboxOpen = true" 
                        title="Perbesar Gambar"
                        class="absolute top-2.5 right-2.5 sm:top-4 sm:right-4 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-black/30 hover:bg-black/50 text-white backdrop-blur-xs flex items-center justify-center transition-all duration-200 hover:scale-105 cursor-pointer">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                    </svg>
                </button>

                <!-- Floating Thumbnails Inside Image (Bottom Left) -->
                @if($galleryImages->count() > 1)
                <div class="absolute bottom-2.5 left-2.5 sm:bottom-4 sm:left-4 z-20 flex items-center gap-2 p-1 sm:p-1.5 max-w-[calc(100%-1.25rem)] sm:max-w-[calc(100%-2rem)] overflow-x-auto no-scrollbar">
                    @foreach($galleryImages as $idx => $imgUrl)
                        <button type="button" 
                                @click="selectImage({{ $idx }})"
                                :class="currentIndex === {{ $idx }} 
                                    ? 'border-2 border-white ring-2 ring-[#650506] opacity-100 shadow-md' 
                                    : 'border border-white/40 opacity-50 hover:opacity-90 shadow-xs'"
                                class="relative w-11 h-11 sm:w-14 sm:h-14 rounded-lg sm:rounded-xl overflow-hidden transition-all duration-200 shrink-0 cursor-pointer">
                            <img src="{{ $imgUrl }}" class="w-full h-full object-cover">
                            <!-- Bottom active accent indicator bar -->
                            <div x-show="currentIndex === {{ $idx }}" 
                                 class="absolute bottom-0 inset-x-0 h-1 sm:h-1.5 bg-[#650506]"></div>
                        </button>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Lightbox Zoom Modal -->
            <template x-teleport="body">
                <div x-show="isLightboxOpen" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     @keydown.escape.window="isLightboxOpen = false"
                     class="fixed inset-0 z-[9999] bg-black/90 backdrop-blur-md flex items-center justify-center p-4" 
                     style="display: none;">
                    
                    <!-- Close button -->
                    <button type="button" 
                            @click="isLightboxOpen = false" 
                            class="absolute top-6 right-6 z-50 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer border border-white/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <!-- Enlarged Image -->
                    <div class="relative max-w-4xl max-h-[85vh] w-full flex items-center justify-center" @click.away="isLightboxOpen = false">
                        <img :src="activeImage" 
                             class="max-w-full max-h-[85vh] object-contain rounded-2xl shadow-2xl">
                    </div>
                </div>
            </template>
        </div>

        @php
            $variantsData = $product->variants->map(function($v) {
                $discountPct = ($v->discount_price && $v->discount_price > 0 && $v->price > $v->discount_price)
                    ? round((($v->price - $v->discount_price) / $v->price) * 100)
                    : 0;
                return [
                    'id' => $v->id,
                    'name' => $v->name,
                    'price' => (float) $v->price,
                    'discount_price' => $v->discount_price ? (float) $v->discount_price : null,
                    'final_price' => (float) $v->final_price,
                    'discount_percent' => $discountPct,
                    'stock' => (int) $v->stock,
                ];
            })->values();

            $defaultVariant = $variantsData->first();
            $defaultPrice = $defaultVariant ? $defaultVariant['final_price'] : (float) $product->final_price;
            $defaultOriginalPrice = $defaultVariant 
                ? (($defaultVariant['discount_price'] && $defaultVariant['discount_price'] > 0 && $defaultVariant['price'] > $defaultVariant['discount_price']) ? $defaultVariant['price'] : null)
                : ($product->discount_price ? (float) $product->base_price : null);
            $defaultDiscountPct = $defaultVariant ? $defaultVariant['discount_percent'] : ($product->discount_percent ?? 0);
            $defaultStock = $defaultVariant ? $defaultVariant['stock'] : (int) $product->stock;
        @endphp

        <!-- Product Actions & Buying Info (5 cols) -->
        <div class="lg:col-span-5 space-y-6"
             x-data="{
                variants: @js($variantsData),
                hasVariants: {{ $variantsData->isNotEmpty() ? 'true' : 'false' }},
                selectedVariantId: {{ $defaultVariant ? $defaultVariant['id'] : 'null' }},
                basePrice: {{ (float) $product->base_price }},
                baseDiscountPrice: {{ $product->discount_price ? (float) $product->discount_price : 'null' }},
                baseDiscountPercent: {{ (int) ($product->discount_percent ?? 0) }},
                baseStock: {{ (int) $product->stock }},
                qty: 1,

                get currentVariant() {
                    if (!this.hasVariants) return null;
                    return this.variants.find(v => v.id == this.selectedVariantId) || this.variants[0];
                },

                get currentPrice() {
                    if (this.currentVariant) {
                        return this.currentVariant.final_price;
                    }
                    return (this.baseDiscountPrice && this.baseDiscountPrice > 0) ? this.baseDiscountPrice : this.basePrice;
                },

                get currentOriginalPrice() {
                    if (this.currentVariant) {
                        return (this.currentVariant.discount_price && this.currentVariant.discount_price > 0 && this.currentVariant.price > this.currentVariant.discount_price)
                            ? this.currentVariant.price 
                            : null;
                    }
                    return (this.baseDiscountPrice && this.baseDiscountPrice > 0) ? this.basePrice : null;
                },

                get currentDiscountPercent() {
                    if (this.currentVariant) {
                        return this.currentVariant.discount_percent;
                    }
                    return this.baseDiscountPercent;
                },

                get currentStock() {
                    if (this.currentVariant) {
                        return this.currentVariant.stock;
                    }
                    return this.baseStock;
                },

                get subtotal() {
                    return this.currentPrice * this.qty;
                },

                selectVariant(id) {
                    this.selectedVariantId = id;
                    if (this.qty > this.currentStock) {
                        this.qty = Math.max(1, this.currentStock);
                    }
                },

                formatRupiah(amount) {
                    if (!amount && amount !== 0) return 'Rp 0';
                    return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
                }
             }">
            <div class="space-y-2 border-b border-gray-100 pb-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase tracking-widest text-[#650506] font-bold">
                        {{ $product->brand?->name ?? 'Aroma Palace Exclusive' }}
                    </span>
                    <span class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full"
                          :class="currentStock > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'"
                          x-text="currentStock > 0 ? 'Stok Tersedia (' + currentStock + ')' : 'Stok Habis'">
                        {{ $defaultStock > 0 ? 'Stok Tersedia (' . $defaultStock . ')' : 'Stok Habis' }}
                    </span>
                </div>

                <h1 class="text-xl sm:text-3xl font-extrabold text-gray-900 tracking-tight leading-snug">{{ $product->name }}</h1>
                
                <!-- Ratings -->
                <div class="flex items-center gap-2 pt-1">
                    <div class="flex text-amber-400 text-sm">
                        @for($s = 0; $s < 5; $s++)
                            <span>★</span>
                        @endfor
                    </div>
                    <span class="text-xs font-bold text-gray-900">{{ number_format($product->rating_avg, 1) }}</span>
                    <span class="text-xs text-gray-400">({{ $product->reviews_count }} ulasan)</span>
                    @if($product->category)
                        <span class="text-gray-300">•</span>
                        <span class="text-xs text-gray-500 font-medium">{{ $product->category->name }}</span>
                    @endif
                </div>
            </div>

            <!-- Price Box -->
            <div class="flex flex-wrap items-center gap-2 sm:gap-2.5 py-0.5 sm:py-1">
                <span class="text-2xl sm:text-4xl font-extrabold text-gray-900 tracking-tight" x-text="formatRupiah(currentPrice)">
                    Rp {{ number_format($defaultPrice, 0, ',', '.') }}
                </span>
                <div class="inline-flex items-center gap-1.5" x-show="currentOriginalPrice">
                    <span class="text-xs sm:text-base text-gray-400 line-through tracking-tight" x-text="formatRupiah(currentOriginalPrice)">
                        @if($defaultOriginalPrice)
                            Rp {{ number_format($defaultOriginalPrice, 0, ',', '.') }}
                        @endif
                    </span>
                    <span class="inline-flex items-center px-1.5 sm:px-2 py-0.5 rounded-full text-[10px] sm:text-[11px] font-bold tracking-tight bg-[#650506]/10 text-[#650506] border border-[#650506]/20"
                          x-text="'Hemat ' + currentDiscountPercent + '%'">
                        @if($defaultDiscountPct > 0)
                            Hemat {{ $defaultDiscountPct }}%
                        @endif
                    </span>
                </div>
            </div>

            <p class="text-xs sm:text-sm text-gray-600 leading-relaxed">{{ $product->short_description }}</p>

            <!-- Add to Cart Form -->
            @auth
            <form method="POST" action="{{ route('cart.store') }}" class="space-y-4 sm:space-y-6 pt-1 sm:pt-2"
                  onsubmit="event.preventDefault(); addToCart(this, this.querySelector('[type=submit]'))">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                @if($product->variants->isNotEmpty())
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-[11px] sm:text-xs font-bold text-gray-900 uppercase tracking-wider">Pilih Varian:</label>
                        <span class="text-[10px] sm:text-[11px] text-[#650506] font-semibold" x-show="currentVariant" x-text="currentVariant ? currentVariant.name : ''"></span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 sm:gap-3">
                        @foreach($product->variants as $index => $variant)
                        <label @click="selectVariant({{ $variant->id }})"
                                :class="selectedVariantId === {{ $variant->id }} ? 'border-[#650506] bg-[#650506]/[0.03] ring-1 ring-[#650506]/20 shadow-xs' : 'border-gray-200 hover:border-gray-300'"
                                class="border rounded-lg sm:rounded-xl p-2.5 sm:p-3 cursor-pointer transition flex flex-col justify-between">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-[11px] sm:text-xs text-gray-900">{{ $variant->name }}</span>
                                <input type="radio" 
                                       name="product_variant_id" 
                                       value="{{ $variant->id }}" 
                                       x-model="selectedVariantId"
                                       class="text-[#650506] focus:ring-0 focus:ring-offset-0">
                            </div>
                            <div class="mt-1.5 sm:mt-2 flex items-baseline justify-between">
                                <span class="text-[11px] sm:text-xs text-gray-900 font-bold font-mono">Rp {{ number_format($variant->final_price, 0, ',', '.') }}</span>
                                <span class="text-[9.5px] sm:text-[10px] text-gray-400">Stok: {{ $variant->stock }}</span>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="flex items-end gap-2.5 sm:gap-3.5">
                    <div class="w-28 sm:w-32 shrink-0">
                        <label class="block text-[10px] sm:text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-1 sm:mb-1.5">Jumlah</label>
                        <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white">
                            <button type="button" @click="if(qty > 1) qty--" :disabled="qty <= 1"
                                class="w-8 sm:w-10 h-10 sm:h-11 flex items-center justify-center font-bold text-gray-700 hover:bg-gray-100 disabled:opacity-30 transition cursor-pointer">
                                -
                            </button>
                            <input type="number" name="quantity" x-model="qty" min="1" :max="currentStock"
                                class="w-full bg-transparent text-center text-xs sm:text-sm font-bold text-gray-900 focus:outline-none font-mono [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" readonly>
                            <button type="button" @click="if(qty < currentStock) qty++" :disabled="qty >= currentStock"
                                class="w-8 sm:w-10 h-10 sm:h-11 flex items-center justify-center font-bold text-gray-700 hover:bg-gray-100 disabled:opacity-30 transition cursor-pointer">
                                +
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 flex items-center gap-1.5 sm:gap-2">
                        <button type="submit" :disabled="currentStock <= 0" 
                            class="flex-1 bg-[#650506] hover:bg-[#4A070B] text-white font-semibold py-2.5 sm:py-3 px-3 sm:px-6 rounded-lg shadow-sm transition disabled:opacity-40 flex items-center justify-center gap-1.5 sm:gap-2 text-xs sm:text-sm cursor-pointer h-10 sm:h-11">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <span x-text="currentStock > 0 ? 'Masukkan Keranjang' : 'Stok Habis'">Masukkan Keranjang</span>
                        </button>
                        <button type="button" 
                            onclick="toggleWishlist({{ $product->id }}, this)"
                            data-product-id="{{ $product->id }}"
                            title="{{ $isWishlisted ? 'Hapus dari Wishlist' : 'Tambah ke Wishlist' }}"
                            class="w-10 h-10 sm:w-11 sm:h-11 border {{ $isWishlisted ? 'border-rose-200 bg-rose-50 text-rose-500' : 'border-gray-300 text-gray-700 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-500' }} rounded-lg transition flex items-center justify-center shrink-0 cursor-pointer">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 {{ $isWishlisted ? 'fill-rose-500 text-rose-500' : '' }}" fill="{{ $isWishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        </button>
                    </div>
                </div>
            </form>
            @else
            <div class="pt-1 sm:pt-2">
                <div class="w-full p-3 sm:p-4 bg-stone-50 border border-gray-200 rounded-xl text-xs text-gray-700 flex items-center justify-between">
                    <span>Masuk untuk menambahkan produk ini ke keranjang belanja atau wishlist Anda.</span>
                    <a href="{{ route('login') }}" class="font-bold underline text-[#650506] hover:text-[#4A070B] ml-2 shrink-0">Masuk &rarr;</a>
                </div>
            </div>
            @endauth

            <!-- Perks Summary -->
            <div class="grid grid-cols-2 gap-2.5 sm:gap-3.5 pt-4 sm:pt-6 border-t border-gray-200 text-[11px] sm:text-xs text-gray-600">
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <span class="text-[#650506]">✓</span>
                    <span>100% Produk Original</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <span class="text-[#650506]">✓</span>
                    <span>Gratis Ongkir > Rp 500rb</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <span class="text-[#650506]">✓</span>
                    <span>Garansi Retur 30 Hari</span>
                </div>
                <div class="flex items-center gap-1.5 sm:gap-2">
                    <span class="text-[#650506]">✓</span>
                    <span>Pembayaran Aman Terenkripsi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Description & Accordion Specs -->
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-8 border border-gray-200 shadow-2xs space-y-4 sm:space-y-6">
        <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 border-b border-gray-100 pb-3 sm:pb-4">Detail Produk &amp; Komposisi Aroma</h3>
        <div class="prose prose-stone max-w-none text-xs sm:text-sm text-gray-600 leading-relaxed">
            {!! nl2br(e($product->description)) !!}
        </div>

        @if($product->how_to_use)
        <div class="border-t border-gray-100 pt-5 space-y-1.5">
            <h4 class="font-bold text-xs sm:text-sm text-gray-900 uppercase tracking-wide">Cara Pemakaian</h4>
            <p class="text-xs text-gray-600 leading-relaxed">{{ $product->how_to_use }}</p>
        </div>
        @endif

        @if($product->ingredients)
        <div class="border-t border-gray-100 pt-5 space-y-1.5">
            <h4 class="font-bold text-xs sm:text-sm text-gray-900 uppercase tracking-wide">Bahan &amp; Catatan Aroma (Ingredients)</h4>
            <p class="text-xs text-gray-500 font-mono leading-relaxed bg-stone-50 p-3 rounded-xl border border-gray-100">{{ $product->ingredients }}</p>
        </div>
        @endif
    </div>

    <!-- Customer Reviews -->
    <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-8 border border-gray-200 shadow-2xs space-y-4 sm:space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-2.5 sm:gap-3 border-b border-gray-100 pb-3 sm:pb-4">
            <div>
                <h3 class="text-lg sm:text-xl font-extrabold text-gray-900">Ulasan Pelanggan</h3>
                <span class="text-[11px] sm:text-xs text-gray-500">Rata-rata: ★ {{ number_format($product->rating_avg, 1) }} dari {{ $product->reviews_count }} ulasan</span>
            </div>
        </div>

        <div class="space-y-3 sm:space-y-4">
            @forelse($product->reviews as $rev)
            <div class="p-3 sm:p-4 bg-stone-50/70 rounded-xl border border-gray-100 space-y-1.5 sm:space-y-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ $rev->user?->avatar ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80' }}" class="w-8 h-8 rounded-full object-cover ring-1 ring-gray-200" alt="User">
                        <div>
                            <span class="font-bold text-xs text-gray-900 block">{{ $rev->user?->name ?? 'Pelanggan Terverifikasi' }}</span>
                            @if($rev->is_verified_purchase)
                                <span class="text-[9px] text-emerald-700 font-semibold uppercase tracking-wider">Pembeli Terverifikasi</span>
                            @endif
                        </div>
                    </div>
                    <span class="text-xs text-amber-500 font-bold font-mono">★ {{ $rev->rating }}.0</span>
                </div>
                <p class="text-xs text-gray-600 leading-relaxed">{{ $rev->comment }}</p>
            </div>
            @empty
            <div class="py-10 text-center text-gray-400 space-y-1">
                <p class="text-xs">Belum ada ulasan untuk produk ini.</p>
                <p class="text-[11px] text-gray-400">Jadilah yang pertama memberikan ulasan setelah melakukan pembelian.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
