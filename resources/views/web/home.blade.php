@extends('layouts.app')

@section('title', 'Aroma Palace - Luxury Fragrance & Modern Essentials')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-7 sm:space-y-11 lg:space-y-14">

    <!-- 1. BANNER PROMO DAN CAMPAIGN -->
    <section x-data="{
        activeSlide: 0,
        timer: null,
        banners: @js($banners->map(fn($b) => [
            'id' => $b->id,
            'title' => $b->title ?? 'Elevate Your Everyday',
            'subtitle' => $b->subtitle ?? 'Discover thoughtfully designed products that bring style, comfort & functionality to your life.',
            'image' => $b->image_url ?? 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1200&q=80',
            'link' => route('products.index')
        ])),
        init() {
            if (this.banners.length > 1) {
                this.startAutoplay();
            }
        },
        startAutoplay() {
            this.stopAutoplay();
            this.timer = setInterval(() => {
                this.next();
            }, 5000);
        },
        stopAutoplay() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },
        next() { 
            this.activeSlide = (this.activeSlide + 1) % this.banners.length;
        },
        prev() { 
            this.activeSlide = (this.activeSlide - 1 + this.banners.length) % this.banners.length;
        },
        goTo(idx) {
            this.activeSlide = idx;
            this.startAutoplay();
        }
    }" class="space-y-3 sm:space-y-6">

        <!-- Hero Card (Split Card with Warm Sand Background) -->
        <div @mouseenter="stopAutoplay()" 
             @mouseleave="startAutoplay()"
             class="relative bg-[#F4EFEA] rounded-xl overflow-hidden shadow-2xs">
            <template x-for="(banner, idx) in banners" :key="banner.id">
                <div x-show="activeSlide === idx"
                     x-transition:enter="transition ease-out duration-500"
                     x-transition:enter-start="opacity-0 translate-x-2"
                     x-transition:enter-end="opacity-100 translate-x-0"
                     class="grid grid-cols-1 lg:grid-cols-12 items-center">

                    <!-- Left Column: Content (Order 2 on Mobile, Order 1 on Desktop) -->
                    <div class="order-2 lg:order-1 lg:col-span-6 p-4 pb-6 sm:p-8 lg:p-14 flex flex-col justify-center">
                        <h1 class="text-2xl sm:text-4xl lg:text-6xl font-extrabold tracking-tight text-gray-900 leading-[1.15]"
                            x-text="banner.title">
                        </h1>

                        <p class="mt-2 sm:mt-4 text-xs sm:text-base text-gray-600 leading-relaxed max-w-md"
                            x-text="banner.subtitle">
                        </p>

                        <!-- Action Buttons -->
                        <div class="mt-4 sm:mt-8 flex flex-wrap items-center gap-2 sm:gap-3.5">
                            <a :href="banner.link" class="inline-flex items-center justify-center px-4 sm:px-7 py-2 sm:py-3 bg-[#650506] hover:bg-[#4A070B] text-white text-xs sm:text-sm font-semibold rounded-lg shadow-sm transition">
                                Shop Now
                            </a>
                            <a href="{{ route('products.index', ['sort' => 'popular']) }}" class="inline-flex items-center justify-center px-4 sm:px-6 py-2 sm:py-3 bg-white hover:bg-gray-50 border border-gray-300 text-gray-800 text-xs sm:text-sm font-semibold rounded-lg shadow-2xs transition">
                                Explore Collection
                            </a>
                        </div>

                        <!-- 4 Feature Bullets (Free Shipping, Secure Payment, 24/7 Support, Easy Returns) -->
                        <div class="mt-4 sm:mt-10 pt-3 sm:pt-6 border-t border-stone-300/60 grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3.5">
                            <div class="flex items-start gap-1.5 sm:gap-2">
                                <div class="text-gray-900 shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                </div>
                                <div>
                                    <p class="text-[11px] sm:text-xs font-bold text-gray-900 leading-tight">Free Shipping</p>
                                    <p class="text-[10px] sm:text-[11px] text-gray-500 mt-0.5">Over Rp 500k</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-1.5 sm:gap-2">
                                <div class="text-gray-900 shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                </div>
                                <div>
                                    <p class="text-[11px] sm:text-xs font-bold text-gray-900 leading-tight">Secure Payment</p>
                                    <p class="text-[10px] sm:text-[11px] text-gray-500 mt-0.5">100% Checkout</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-1.5 sm:gap-2">
                                <div class="text-gray-900 shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-[11px] sm:text-xs font-bold text-gray-900 leading-tight">24/7 Support</p>
                                    <p class="text-[10px] sm:text-[11px] text-gray-500 mt-0.5">Here to help</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-1.5 sm:gap-2">
                                <div class="text-gray-900 shrink-0 mt-0.5">
                                    <svg class="w-3.5 h-3.5 sm:w-4.5 sm:h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </div>
                                <div>
                                    <p class="text-[11px] sm:text-xs font-bold text-gray-900 leading-tight">Easy Returns</p>
                                    <p class="text-[10px] sm:text-[11px] text-gray-500 mt-0.5">30-day policy</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Hero Image (Order 1 on Mobile, Order 2 on Desktop) -->
                    <div class="order-1 lg:order-2 lg:col-span-6 relative h-[190px] sm:h-[320px] lg:h-[480px] p-2 sm:p-6 lg:p-8 flex items-center justify-center">
                        <div class="relative w-full h-full rounded-xl overflow-hidden shadow-xs">
                            <img :src="banner.image" 
                                 :alt="banner.title" 
                                 class="w-full h-full object-cover object-center transition-all duration-700">
                        </div>
                    </div>
                </div>
            </template>

            <!-- Slide Indicator Dots -->
            <div x-show="banners.length > 1" class="absolute bottom-2 sm:bottom-4 left-4 sm:left-12 flex items-center gap-1.5 sm:gap-2 z-10">
                <template x-for="(b, i) in banners" :key="i">
                    <button @click="goTo(i)"
                            class="h-1.5 sm:h-2 rounded-full transition-all duration-300"
                            :class="activeSlide === i ? 'w-5 sm:w-6 bg-[#650506]' : 'w-1.5 sm:w-2 bg-gray-300 hover:bg-gray-400'">
                    </button>
                </template>
            </div>
        </div>
    </section>

    <!-- 2. KATEGORI PRODUK (Shop by Category) -->
    <section>
        <div class="flex items-center justify-between mb-3 sm:mb-5">
            <div>
                <h2 class="text-lg sm:text-2xl font-bold text-gray-900 tracking-tight">Kategori Produk</h2>
                <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1">Jelajahi beragam koleksi wewangian dan produk gaya hidup pilihan kami.</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-xs sm:text-sm font-semibold text-[#650506] hover:text-[#4A070B] inline-flex items-center gap-1 group shrink-0 transition-colors">
                <span>Lihat Semua</span>
                <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-4">
            @foreach($categories as $cat)
            <a href="{{ route('products.index', ['category' => $cat->slug]) }}"
               class="group relative aspect-square rounded-lg overflow-hidden block shadow-2xs hover:shadow-md transition-all duration-300">
                <!-- Full-bleed Image -->
                <img src="{{ $cat->image_url ?? 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=600&q=80' }}"
                     class="w-full h-full object-cover object-center group-hover:scale-108 transition-transform duration-500"
                     alt="{{ $cat->name }}">
                
                <!-- Dark Gradient Overlay for Maximum Readability -->
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-black/5 group-hover:from-black/90 transition-colors duration-300"></div>

                <!-- Text Overlay at Bottom -->
                <div class="absolute inset-x-0 bottom-0 p-2.5 sm:p-4 text-left flex flex-col justify-end">
                    <h3 class="text-xs sm:text-base font-bold text-white tracking-wide leading-snug line-clamp-1 group-hover:translate-x-0.5 transition-transform duration-300">
                        {{ $cat->name }}
                    </h3>
                    <p class="text-[10px] sm:text-xs text-gray-300 mt-0.5 font-medium flex items-center justify-between">
                        <span>{{ $cat->products()->count() }} Produk</span>
                        <span class="opacity-0 group-hover:opacity-100 transition-opacity text-white">&rarr;</span>
                    </p>
                </div>
            </a>
            @endforeach
        </div>
    </section>

    <!-- 3. PRODUK POPULER (Best Sellers) -->
    <section>
        <div class="flex items-center justify-between mb-3 sm:mb-5">
            <div>
                <h2 class="text-lg sm:text-2xl font-bold text-gray-900 tracking-tight">Produk Populer</h2>
                <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1">Koleksi terlaris dan paling diminati pelanggan minggu ini.</p>
            </div>
            <a href="{{ route('products.index', ['sort' => 'popular']) }}" class="text-xs sm:text-sm font-semibold text-[#650506] hover:text-[#4A070B] inline-flex items-center gap-1 group shrink-0 transition-colors">
                <span>Lihat Semua</span>
                <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5 sm:gap-4 lg:gap-5">
            @foreach($popularProducts->take(10) as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>

    <!-- 4. INFORMASI PROMO ATAU EXCLUSIVE OFFER (Mid-Banner Campaign) -->
    <section class="space-y-5 sm:space-y-8">
        <!-- Promotional Campaign Banner (Summer Sale!, Royal Maroon Card) -->
        <div class="relative bg-gradient-to-br from-[#4A070B] via-[#650506] to-[#360407] rounded-xl sm:rounded-2xl p-4 sm:p-10 lg:p-14 text-white overflow-hidden shadow-md">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 sm:gap-8 items-center relative z-10">
                <!-- Text Content (Order 2 on Mobile, Order 1 on Desktop) -->
                <div class="order-2 md:order-1 md:col-span-7 space-y-2 sm:space-y-4">
                    <div class="text-[10px] sm:text-[11px] font-bold uppercase tracking-widest text-amber-300 flex items-center gap-1.5">
                        <span>✦</span>
                        <span>EXCLUSIVE OFFER &amp; CAMPAIGN</span>
                    </div>
                    <h2 class="text-xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                        Summer Sale!
                    </h2>
                    <p class="text-stone-200 text-[11px] sm:text-base leading-relaxed max-w-md">
                        Dapatkan diskon istimewa untuk koleksi wewangian dan essentials pilihan. Gunakan voucher eksklusif sebelum periode promo berakhir.
                    </p>
                    <div class="pt-1 sm:pt-3 flex flex-wrap items-center gap-2 sm:gap-3">
                        <a href="{{ route('products.index', ['discount' => 1]) }}" class="flex-1 sm:flex-initial inline-flex items-center justify-center px-4 sm:px-7 py-2.5 sm:py-3.5 bg-white text-[#4A070B] hover:bg-amber-50 font-semibold rounded-xl text-xs sm:text-sm transition shadow-sm text-center">
                            Shop the Sale
                        </a>
                        <a href="{{ route('cart.index') }}" class="flex-1 sm:flex-initial inline-flex items-center justify-center px-4 sm:px-6 py-2.5 sm:py-3.5 bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold rounded-xl text-xs sm:text-sm transition text-center">
                            Cek Keranjang
                        </a>
                    </div>
                </div>
                
                <!-- Image Banner (Order 1 on Mobile, Full Width, Order 2 on Desktop) -->
                <div class="order-1 md:order-2 md:col-span-5 w-full flex justify-center lg:justify-end">
                    <div class="relative w-full h-40 sm:h-72 md:w-80 md:h-80 rounded-lg sm:rounded-xl overflow-hidden shadow-xl border border-white/15">
                        <img src="https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=700&q=80" alt="Exclusive Summer Sale" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>

        <!-- Exclusive Voucher Cards -->
        @if($exclusivePromos->count() > 0)
        <div x-data="{
            scroll(dir) {
                const el = this.$refs.voucherSlider;
                if (!el) return;
                const card = el.querySelector('[data-voucher-card]');
                const cardWidth = card ? card.offsetWidth + 16 : 300;
                
                // Jika sudah di ujung kanan dan ditekan kanan, putar balik ke awal dengan halus
                if (dir === 1 && (el.scrollLeft + el.clientWidth >= el.scrollWidth - 10)) {
                    el.scrollTo({ left: 0, behavior: 'smooth' });
                    return;
                }
                // Jika di awal dan ditekan kiri, lompat ke ujung kanan
                if (dir === -1 && el.scrollLeft <= 5) {
                    el.scrollTo({ left: el.scrollWidth, behavior: 'smooth' });
                    return;
                }
                
                el.scrollBy({ left: dir * cardWidth, behavior: 'smooth' });
            }
        }">
            <div class="mb-3 sm:mb-5">
                <h3 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight flex items-center gap-2">
                    <span>Voucher &amp; Promo Eksklusif Hari Ini</span>
                </h3>
                <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1">Gunakan kode voucher di keranjang belanja untuk menikmati potongan harga istimewa.</p>
            </div>
            
            <!-- Horizontal Voucher Slider with Floating Left/Right Arrows -->
            <div class="relative group/slider">
                <!-- Floating Left Arrow -->
                <button type="button"
                        @click="scroll(-1)"
                        aria-label="Scroll left"
                        class="absolute -left-2 sm:-left-5 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full border border-gray-200 bg-white/95 text-gray-800 hover:bg-[#650506] hover:text-white hover:border-[#650506] flex items-center justify-center transition-all shadow-md cursor-pointer hover:scale-105 opacity-90 sm:opacity-0 group-hover/slider:opacity-100">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>

                <!-- Slider Track -->
                <div x-ref="voucherSlider"
                     class="flex items-stretch gap-2.5 sm:gap-5 overflow-x-auto scroll-smooth pb-3 sm:pb-4 pt-1 snap-x snap-mandatory [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    @foreach($exclusivePromos as $promo)
                    <div data-voucher-card
                         class="w-[75%] sm:w-[calc((100%-16px)/2.2)] lg:w-[calc((100%-2*20px)/3)] xl:w-[calc((100%-3*20px)/3.5)] shrink-0 snap-start relative bg-white rounded-xl border border-gray-200/90 shadow-2xs hover:shadow-md transition-all duration-300 flex flex-col justify-between overflow-hidden group hover:border-[#650506]/40"
                         x-data="{ copied: false }">
                        
                        <!-- Top Ribbon Accent -->
                        <div class="h-1 w-full bg-gradient-to-r from-[#650506] via-[#8B1E21] to-[#D4AF37]"></div>

                        <div class="p-3 sm:p-4 space-y-2 sm:space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 px-2 sm:px-2.5 py-0.5 bg-[#FFF5F5] text-[#650506] border border-rose-100 text-[10px] sm:text-[11px] font-extrabold tracking-wide rounded-md font-mono">
                                    <span>🎟️</span>
                                    @if($promo->discount_type === 'percentage')
                                        Diskon {{ (int)$promo->discount_value }}%
                                    @else
                                        Potongan Rp {{ number_format($promo->discount_value, 0, ',', '.') }}
                                    @endif
                                </span>
                                <span class="text-[9.5px] sm:text-[10px] text-gray-400 font-medium flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <span>{{ $promo->end_date ? $promo->end_date->format('d M') : 'Segera' }}</span>
                                </span>
                            </div>

                            <h4 class="text-xs sm:text-sm font-bold text-gray-900 line-clamp-1 group-hover:text-[#650506] transition">{{ $promo->title }}</h4>
                            <p class="text-[10px] sm:text-[11px] text-gray-500 line-clamp-2 leading-relaxed min-h-[28px] sm:min-h-[32px]">{{ $promo->description ?? 'Gunakan kode promo ini saat checkout untuk mendapatkan potongan harga spesial.' }}</p>
                        </div>

                        <!-- Voucher Ticket Divider with Cutout Notches -->
                        <div class="relative flex items-center px-3 sm:px-4 my-0.5 sm:my-1">
                            <!-- Left Notch -->
                            <div class="absolute -left-2 sm:-left-2.5 w-4 h-4 sm:w-5 sm:h-5 bg-[#FAF9F5] rounded-full border-r border-gray-200/90 shadow-inner"></div>
                            <!-- Dashed Divider Line -->
                            <div class="w-full border-b-2 border-dashed border-gray-200"></div>
                            <!-- Right Notch -->
                            <div class="absolute -right-2 sm:-right-2.5 w-4 h-4 sm:w-5 sm:h-5 bg-[#FAF9F5] rounded-full border-l border-gray-200/90 shadow-inner"></div>
                        </div>

                        <!-- Voucher Footer with Code & Copy Button -->
                        <div class="p-3 sm:p-4 pt-2.5 sm:pt-3 flex items-center justify-between gap-2.5 sm:gap-3 bg-stone-50/60">
                            <div class="flex-1 min-w-0">
                                <span class="text-[8.5px] sm:text-[9px] uppercase tracking-wider text-gray-400 font-bold block">Kode Promo</span>
                                <span class="text-xs sm:text-sm font-mono font-extrabold text-[#650506] tracking-wider truncate block">{{ $promo->code }}</span>
                            </div>
                            <button type="button"
                                    @click="copyPromoCode('{{ $promo->code }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                    class="px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-lg text-[11px] sm:text-xs font-semibold transition-all shadow-2xs flex items-center gap-1 sm:gap-1.5 shrink-0 cursor-pointer"
                                    :class="copied ? 'bg-emerald-600 text-white ring-2 ring-emerald-200' : 'bg-[#650506] hover:bg-[#4A070B] text-white hover:scale-102'">
                                <svg x-show="!copied" class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                <svg x-show="copied" class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                                </svg>
                                <span x-text="copied ? 'Tersalin' : 'Salin'"></span>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Floating Right Arrow -->
                <button type="button"
                        @click="scroll(1)"
                        aria-label="Scroll right"
                        class="absolute -right-2 sm:-right-5 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full border border-gray-200 bg-white/95 text-gray-800 hover:bg-[#650506] hover:text-white hover:border-[#650506] flex items-center justify-center transition-all shadow-md cursor-pointer hover:scale-105 opacity-90 sm:opacity-0 group-hover/slider:opacity-100">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
        @endif
    </section>

    <!-- 5. PRODUK REKOMENDASI (Standard Grid) -->
    <section>
        <div class="flex items-center justify-between mb-3 sm:mb-5">
            <div>
                <h2 class="text-lg sm:text-2xl font-bold text-gray-900 tracking-tight">Produk Rekomendasi</h2>
                <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1">Pilihan wewangian dan essentials terbaik yang dikurasi khusus untuk Anda.</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-xs sm:text-sm font-semibold text-[#650506] hover:text-[#4A070B] inline-flex items-center gap-1 group shrink-0 transition-colors">
                <span>Lihat Semua</span>
                <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5 sm:gap-4 lg:gap-5">
            @foreach($recommendedProducts->take(10) as $product)
                <x-product-card :product="$product" />
            @endforeach
        </div>
    </section>

</div>

<!-- 6. PRODUK DENGAN DISKON (Full Bleed Royal Maroon Horizontal Slider) -->
<section class="w-full bg-gradient-to-br from-[#4A070B] via-[#52090F] to-[#360407] py-7 sm:py-16 text-white my-7 sm:my-14 shadow-xs"
         x-data="{
            scroll(dir) {
                const el = this.$refs.slider;
                if (!el) return;
                const card = el.querySelector('[data-deal-card]');
                const cardWidth = card ? card.offsetWidth + 16 : 240;
                
                // Jika sudah di ujung kanan dan ditekan kanan, putar balik ke awal dengan halus
                if (dir === 1 && (el.scrollLeft + el.clientWidth >= el.scrollWidth - 10)) {
                    el.scrollTo({ left: 0, behavior: 'smooth' });
                    return;
                }
                // Jika di awal dan ditekan kiri, lompat ke ujung kanan
                if (dir === -1 && el.scrollLeft <= 5) {
                    el.scrollTo({ left: el.scrollWidth, behavior: 'smooth' });
                    return;
                }
                
                el.scrollBy({ left: dir * cardWidth, behavior: 'smooth' });
            }
         }">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-4 sm:mb-7">
            <div>
                <h2 class="text-lg sm:text-2xl lg:text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
                    <span>Produk dengan Diskon</span>
                    <span class="text-[10px] sm:text-xs bg-[#D4AF37] text-[#4A070B] px-2 sm:px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider">Sale</span>
                </h2>
                <p class="text-[11px] sm:text-sm text-stone-300 mt-0.5 sm:mt-1">Penawaran harga terbaik dengan potongan harga spesial terbatas.</p>
            </div>
            
            <a href="{{ route('products.index', ['discount' => 1]) }}" class="text-xs sm:text-sm font-semibold text-amber-300 hover:text-white inline-flex items-center gap-1 group shrink-0 transition-colors">
                <span>Lihat Semua</span>
                <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
            </a>
        </div>

        <!-- Horizontal Slider with Floating Left & Right Arrows -->
        <div class="relative group/dealslider">
            <!-- Floating Left Arrow -->
            <button type="button"
                    @click="scroll(-1)"
                    aria-label="Scroll left"
                    class="absolute -left-2 sm:-left-5 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full border border-white/20 bg-black/40 hover:bg-white text-white hover:text-[#650506] flex items-center justify-center transition-all shadow-lg backdrop-blur-xs cursor-pointer hover:scale-105 opacity-90 sm:opacity-0 group-hover/dealslider:opacity-100">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Slider Track -->
            <div x-ref="slider" 
                 class="flex items-stretch gap-2.5 sm:gap-4 overflow-x-auto scroll-smooth pb-3 sm:pb-4 pt-1 snap-x snap-mandatory [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach($discountProducts as $product)
                    <div data-deal-card
                         class="w-[calc((100%-10px)/2.15)] sm:w-[calc((100%-2*16px)/3)] md:w-[calc((100%-3*16px)/4)] lg:w-[calc((100%-5*16px)/5.5)] shrink-0 snap-start hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <x-product-card :product="$product" :price-stacked="true" :is-dark="true" />
                    </div>
                @endforeach
            </div>

            <!-- Floating Right Arrow -->
            <button type="button"
                    @click="scroll(1)"
                    aria-label="Scroll right"
                    class="absolute -right-2 sm:-right-5 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full border border-white/20 bg-black/40 hover:bg-white text-white hover:text-[#650506] flex items-center justify-center transition-all shadow-lg backdrop-blur-xs cursor-pointer hover:scale-105 opacity-90 sm:opacity-0 group-hover/dealslider:opacity-100">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
</section>

<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pb-8 sm:pb-12 space-y-7 sm:space-y-11 lg:space-y-14">

    <!-- 7. BRAND PILIHAN (Featured Brands) -->
    <section>
        <div class="flex items-center justify-between mb-3 sm:mb-5">
            <div>
                <h2 class="text-lg sm:text-2xl font-bold text-gray-900 tracking-tight">Brand Pilihan</h2>
                <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1">Kurasi merek parfum mewah dan desainer ternama dunia.</p>
            </div>
            <a href="{{ route('products.index') }}" class="text-xs sm:text-sm font-medium text-gray-600 hover:text-black flex items-center gap-1 group shrink-0">
                <span>Jelajahi Brand</span>
                <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 sm:gap-4">
            @foreach($brands->take(6) as $brand)
            @php
                $brandImg = $brand->logo_url ?: 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=600&q=80';
                $productCount = $brand->products()->count();
            @endphp
            <a href="{{ route('products.index', ['brand' => $brand->slug]) }}"
               class="group relative bg-white rounded-lg border border-gray-200/80 hover:border-[#650506] p-2 sm:p-3 hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                <!-- Brand Visual Image (Full-bleed cover) -->
                <div class="relative bg-gray-100 rounded-md aspect-square overflow-hidden mb-2 sm:mb-2.5">
                    <img src="{{ $brandImg }}" 
                         alt="{{ $brand->name }}" 
                         loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                    <span class="absolute top-1.5 left-1.5 sm:top-2 sm:left-2 px-1.5 sm:px-2 py-0.5 rounded bg-white/90 backdrop-blur-xs text-[9px] sm:text-[10px] font-bold text-gray-900 uppercase tracking-wider shadow-2xs">
                        Brand
                    </span>
                </div>

                <!-- Brand Details -->
                <div class="px-0.5 sm:px-1 flex-1 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-xs sm:text-sm text-gray-900 tracking-tight leading-tight group-hover:text-[#650506] line-clamp-1 uppercase transition-colors">
                            {{ $brand->name }}
                        </h3>
                        <p class="text-[10px] sm:text-[11px] text-gray-500 mt-0.5 line-clamp-1 font-normal">
                            {{ $brand->description ?? 'Luxury Fragrance House' }}
                        </p>
                    </div>
                    
                    <div class="mt-2 sm:mt-2.5 pt-1.5 sm:pt-2 border-t border-gray-100 flex items-center justify-between text-[10px] sm:text-[11px] text-gray-500 group-hover:text-[#650506] transition-colors font-medium">
                        <span>{{ $productCount }} Koleksi</span>
                        <span class="group-hover:translate-x-0.5 transition-transform font-bold">&rarr;</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </section>

    <!-- 8. ARTIKEL TERBARU (The Aroma Palace Journal) -->
    @if(!empty($latestArticles) && $latestArticles->count() > 0)
    <section>
        <div class="flex items-center justify-between mb-3 sm:mb-6">
            <div>
                <h2 class="text-lg sm:text-2xl font-bold text-gray-900 tracking-tight">Artikel &amp; Cerita Wewangian Terbaru</h2>
                <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1">Eksplorasi wawasan parfum mewah, piramida aroma, dan tips wewangian dari para ahli.</p>
            </div>
            <a href="{{ route('articles.index') }}" class="text-xs sm:text-sm font-semibold text-[#650506] hover:text-[#4A070B] inline-flex items-center gap-1 group shrink-0 transition-colors">
                <span>Lihat Semua</span>
                <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-5">
            @foreach($latestArticles->take(4) as $art)
                <article class="bg-white rounded-xl sm:rounded-2xl overflow-hidden border border-gray-200/90 shadow-2xs hover:shadow-xl hover:border-[#650506]/30 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                    <div>
                        <!-- Thumbnail Box with 16:10 Aspect Ratio -->
                        <div class="relative aspect-[16/10] overflow-hidden bg-stone-100">
                            <a href="{{ route('articles.show', $art->slug) }}" class="block w-full h-full">
                                <img src="{{ $art->cover_image ?? 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=600&q=80' }}"
                                     alt="{{ $art->title }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            </a>
                            
                            <!-- Badges Overlay -->
                            <div class="absolute inset-x-2 top-2 sm:inset-x-3.5 sm:top-3.5 flex items-center justify-between pointer-events-none">
                                @if($art->topic)
                                    <span class="px-1.5 sm:px-2.5 py-0.5 bg-white/95 backdrop-blur-sm text-[#650506] font-bold text-[8px] sm:text-[9px] uppercase tracking-wider rounded-full shadow-2xs border border-white/60 truncate max-w-[85px] sm:max-w-none">
                                        {{ $art->topic->name }}
                                    </span>
                                @else
                                    <span></span>
                                @endif

                                <span class="px-1.5 sm:px-2 py-0.5 bg-black/60 backdrop-blur-sm text-white font-medium text-[8px] sm:text-[9px] rounded-full flex items-center gap-1 shadow-2xs shrink-0">
                                    <svg class="w-2.5 h-2.5 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ $art->reading_time_minutes ?? 3 }} mnt</span>
                                </span>
                            </div>
                        </div>

                        <!-- Article Content -->
                        <div class="p-2.5 sm:p-5 space-y-1 sm:space-y-2.5">
                            <!-- Meta Line (Date) -->
                            <div class="flex items-center gap-1.5 text-[9.5px] sm:text-[11px] font-medium text-gray-400">
                                <svg class="w-2.5 h-2.5 sm:w-3.5 sm:h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>{{ $art->published_at ? $art->published_at->format('d M Y') : $art->created_at->format('d M Y') }}</span>
                            </div>

                            <!-- Article Title -->
                            <h3 class="text-xs sm:text-base font-bold text-gray-900 group-hover:text-[#650506] transition-colors line-clamp-2 leading-snug tracking-tight">
                                <a href="{{ route('articles.show', $art->slug) }}">{{ $art->title }}</a>
                            </h3>

                            <!-- Article Excerpt -->
                            <p class="text-[11px] sm:text-xs text-gray-500 line-clamp-2 leading-relaxed hidden sm:block">
                                {{ $art->summary }}
                            </p>
                        </div>
                    </div>

                    <!-- Article Card Footer -->
                    <div class="px-2.5 sm:px-5 pb-2.5 sm:pb-5 pt-2 sm:pt-3.5 border-t border-gray-100 flex items-center justify-between gap-2 mt-auto">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <div class="w-4 h-4 sm:w-5 sm:h-5 rounded-full bg-[#650506]/10 text-[#650506] flex items-center justify-center font-bold text-[8px] sm:text-[9px] shrink-0">
                                {{ strtoupper(substr($art->author_name ?? 'A', 0, 1)) }}
                            </div>
                            <span class="text-[10px] sm:text-[11px] text-gray-600 font-medium truncate">
                                {{ $art->author_name ?? 'Aroma Palace' }}
                            </span>
                        </div>

                        <a href="{{ route('articles.show', $art->slug) }}" 
                           class="inline-flex items-center gap-0.5 sm:gap-1 text-[10px] sm:text-xs font-semibold text-[#650506] hover:text-[#4A070B] shrink-0 transition-colors">
                            <span>Baca</span>
                            <svg class="w-3 h-3 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
    @endif

    <!-- 9. BRAND VALUE PROPOSITIONS STRIP -->
    <div id="about-us" class="bg-[#F8F6F2] rounded-xl p-4 sm:p-7 scroll-mt-28">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-8">
            <div class="flex items-center gap-2.5 sm:gap-3.5">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-white shadow-2xs flex items-center justify-center text-gray-900 shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-bold text-gray-900">Quality You Can Trust</h4>
                    <p class="text-[10px] sm:text-[11px] text-gray-500">Carefully curated premium products</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 sm:gap-3.5">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-white shadow-2xs flex items-center justify-center text-gray-900 shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-bold text-gray-900">Designed for Modern Life</h4>
                    <p class="text-[10px] sm:text-[11px] text-gray-500">Stylish, functional & practical</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 sm:gap-3.5">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-white shadow-2xs flex items-center justify-center text-gray-900 shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-bold text-gray-900">Loved by Thousands</h4>
                    <p class="text-[10px] sm:text-[11px] text-gray-500">Join our happy customers</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 sm:gap-3.5">
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-lg bg-white shadow-2xs flex items-center justify-center text-gray-900 shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h4 class="text-xs sm:text-sm font-bold text-gray-900">Satisfaction Guaranteed</h4>
                    <p class="text-[10px] sm:text-[11px] text-gray-500">Your happiness is our priority</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
