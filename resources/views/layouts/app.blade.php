<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Aroma Palace - Luxury Perfume & Fragrance')</title>
    <meta name="description" content="@yield('meta_description', 'Aroma Palace - Luxury fragrances and artisanal lifestyle essentials. Quality products, exceptional service.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Fonts: Inter / Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Production Assets via Vite (Tailwind CSS v4) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('schema')

    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        /* Hide browser default spin buttons for number inputs */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</head>
<body class="bg-white font-sans text-[#111827] antialiased flex flex-col min-h-screen" style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;">
    <!-- Top Announcement Bar (Aroma Palace Royal Maroon Style) -->
    <div class="bg-[#4A070B] border-b border-[#3B0407] text-white text-xs py-2 relative z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <!-- Left: Promo Announcement -->
            <div class="flex items-center gap-2 text-[11px] sm:text-xs text-amber-100 font-medium">
                <span class="text-amber-400 text-sm leading-none">✦</span>
                <span>Summer Sale is Live – Up to 40% Off!</span>
            </div>

            <!-- Right: Free Shipping & Authentic Brands -->
            <div class="hidden md:flex items-center gap-6 text-[10px] text-gray-200">
                <!-- Free Shipping -->
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                    <div class="flex flex-col leading-tight">
                        <span class="font-bold text-white uppercase text-[9.5px] tracking-wider">Free Shipping</span>
                        <span class="text-[8.5px] text-amber-200/90 uppercase tracking-wider">Across Indonesia</span>
                    </div>
                </div>

                <div class="h-3.5 w-px bg-white/20"></div>

                <!-- Authentic Brands -->
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M12 8v13m0-13V4a2 2 0 114 0v4m-4 0V4a2 2 0 10-4 0v4m-5 4h18M5 12a2 2 0 00-2 2v7a2 2 0 002 2h14a2 2 0 002-2v-7a2 2 0 00-2-2H5z" />
                    </svg>
                    <div class="flex flex-col leading-tight">
                        <span class="font-bold text-white uppercase text-[9.5px] tracking-wider">Authentic Brands</span>
                        <span class="text-[8.5px] text-amber-200/90 uppercase tracking-wider">Direct from Dubai</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <header x-data class="bg-white border-b border-gray-100 sticky top-0 z-40 shadow-2xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Upper Row: Search & Hamburger | Centered Logo | User Actions -->
            <div class="flex items-center justify-between py-3 sm:py-3.5 lg:py-4 gap-2 sm:gap-4">
                <!-- Left: Search Box (Desktop) & Search Icon (Mobile) -->
                <div class="flex items-center justify-start flex-1 lg:w-auto lg:max-w-[260px]">
                    <!-- Desktop Search Bar Trigger -->
                    <button onclick="window.dispatchEvent(new CustomEvent('open-spotlight'))"
                            @click="$dispatch('open-spotlight')" 
                            type="button" 
                            class="hidden lg:flex items-center gap-2.5 border-b border-gray-300 pb-1.5 w-full hover:border-[#650506] cursor-pointer group transition text-left">
                        <svg class="w-4 h-4 text-gray-500 group-hover:text-[#650506] transition-colors shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <span class="text-xs text-gray-400 group-hover:text-gray-600 transition-colors select-none font-normal">Search for perfumes...</span>
                    </button>

                    <!-- Mobile Search Button -->
                    <button onclick="window.dispatchEvent(new CustomEvent('open-spotlight'))"
                            @click="$dispatch('open-spotlight')" 
                            type="button" 
                            class="lg:hidden p-1.5 text-gray-700 hover:text-[#650506] transition rounded-lg hover:bg-stone-50 cursor-pointer" 
                            title="Pencarian">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                </div>

                <!-- Center: Luxury Brand Logo (Always Centered on Mobile & Desktop) -->
                <div class="flex items-center justify-center shrink-0 mx-auto px-1 sm:px-2">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 sm:gap-2.5 lg:gap-3 group">
                        <img src="{{ asset('images/logo.png') }}" alt="Aroma Palace" class="w-9 h-9 sm:w-11 sm:h-11 lg:w-13 lg:h-13 object-contain rounded-full shadow-2xs group-hover:scale-105 transition-transform shrink-0">
                        <div class="flex flex-col text-left">
                            <span class="font-serif text-base sm:text-xl lg:text-2xl font-bold tracking-[0.14em] lg:tracking-[0.18em] text-[#4A070B] group-hover:text-[#650506] transition uppercase leading-none whitespace-nowrap">
                                Aroma Palace
                            </span>
                            <span class="text-[7px] sm:text-[8.5px] lg:text-[9.5px] uppercase tracking-[0.2em] lg:tracking-[0.25em] text-[#9B783E] font-semibold mt-0.5 sm:mt-1 whitespace-nowrap">
                                Dubai's Signature Perfumes
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Right: Utilities (Desktop: Account, Wishlist, Cart | Mobile: Cart Only) -->
                <div class="flex items-center justify-end flex-1 gap-2.5 sm:gap-4 lg:gap-7 shrink-0 lg:max-w-[260px]">
                    <!-- Account Icon + Label (Desktop Only - accessible via Bottom Nav on Mobile) -->
                    @auth
                        <a href="{{ route('account.index') }}" class="hidden lg:flex flex-col items-center gap-1 text-gray-700 hover:text-[#650506] transition group" title="{{ auth()->user()->name }}">
                            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-[11px] font-medium text-gray-600 group-hover:text-[#650506] leading-none">Account</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden lg:flex flex-col items-center gap-1 text-gray-700 hover:text-[#650506] transition group" title="Login / Register">
                            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="text-[11px] font-medium text-gray-600 group-hover:text-[#650506] leading-none">Account</span>
                        </a>
                    @endauth

                    <!-- Wishlist Icon + Label (Desktop Only - accessible via Bottom Nav on Mobile) -->
                    @php
                        $wishlistCount = auth()->check()
                            ? \App\Models\Wishlist::where('user_id', auth()->id())->count()
                            : 0;
                    @endphp
                    <a href="{{ route('wishlist.index') }}" class="hidden lg:flex flex-col items-center gap-1 text-gray-700 hover:text-[#650506] transition relative group" title="Wishlist">
                        <div class="relative">
                            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            <span id="wishlist-badge" class="{{ $wishlistCount > 0 ? '' : 'hidden' }} absolute -top-1.5 -right-2 w-4 h-4 bg-[#650506] text-white rounded-full text-[10px] font-bold flex items-center justify-center">
                                {{ $wishlistCount }}
                            </span>
                        </div>
                        <span class="text-[11px] font-medium text-gray-600 group-hover:text-[#650506] leading-none">Wishlist</span>
                    </a>

                    <!-- Cart Icon + Label (Both Desktop & Mobile) -->
                    @php
                        $cartCount = auth()->check() 
                            ? \App\Models\CartItem::whereHas('cart', fn($q) => $q->where('user_id', auth()->id()))->count() 
                            : 0;
                    @endphp
                    <a href="{{ route('cart.index') }}" class="flex flex-col items-center gap-0.5 lg:gap-1 p-1 lg:p-0 text-gray-700 hover:text-[#650506] transition relative group" title="Shopping Cart">
                        <div class="relative">
                            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <span id="cart-badge" class="{{ $cartCount > 0 ? '' : 'hidden' }} absolute -top-1.5 -right-2 w-4 h-4 bg-[#650506] text-white rounded-full text-[10px] font-bold flex items-center justify-center">
                                {{ $cartCount }}
                            </span>
                        </div>
                        <span class="hidden lg:block text-[11px] font-medium text-gray-600 group-hover:text-[#650506] leading-none">Cart</span>
                    </a>
                </div>
            </div>

            <!-- Lower Row: Navigation Menu Bar with Smooth Arrow Slide for Mobile -->
            <nav x-data="{
                    slidRight: false,
                    isAnimating: false,
                    shopOpen: false,
                    mobileShopLeft: 16,
                    toggleShop(e) {
                        this.shopOpen = !this.shopOpen;
                        if (this.shopOpen && this.$refs.shopBtn) {
                            const rect = this.$refs.shopBtn.getBoundingClientRect();
                            const navRect = this.$el.getBoundingClientRect();
                            this.mobileShopLeft = Math.max(12, Math.min(rect.left - navRect.left, window.innerWidth - 210));
                        }
                    },
                    slideToggle() {
                        const el = this.$refs.navTrack;
                        this.isAnimating = true;
                        this.slidRight = !this.slidRight;
                        this.shopOpen = false;
                        el.scrollTo({
                            left: this.slidRight ? (el.scrollWidth - el.clientWidth) : 0,
                            behavior: 'smooth'
                        });
                        setTimeout(() => { this.isAnimating = false; }, 400);
                    },
                    checkScroll() {
                        if (this.isAnimating) return;
                        const el = this.$refs.navTrack;
                        const maxScroll = el.scrollWidth - el.clientWidth;
                        if (maxScroll > 0) {
                            this.slidRight = el.scrollLeft > (maxScroll / 2);
                        }
                    }
                }"
                @click.outside="shopOpen = false"
                class="relative flex items-center border-t border-gray-100 py-2 sm:py-2.5 lg:py-3 px-1 sm:px-2 lg:px-0">

                <!-- Horizontal Nav Track (Scrollable without visible scrollbar on mobile, Centered & Overflow Visible on desktop) -->
                <div x-ref="navTrack" 
                     @scroll.passive="checkScroll()"
                     class="flex items-center overflow-x-auto lg:overflow-visible no-scrollbar lg:justify-center gap-4 sm:gap-6 lg:gap-8 xl:gap-10 text-[10.5px] sm:text-[11.5px] lg:text-[12px] uppercase tracking-[0.08em] sm:tracking-[0.12em] lg:tracking-[0.14em] font-semibold text-gray-700 whitespace-nowrap w-full pr-10 lg:pr-0 scroll-smooth"
                     style="-webkit-overflow-scrolling: touch;">
                    <!-- 1. Home -->
                    <a href="{{ route('home') }}" class="relative pb-1 transition shrink-0 {{ request()->routeIs('home') ? 'text-[#650506] font-bold after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-[#650506]' : 'hover:text-[#650506]' }}">
                        HOME
                    </a>

                    <!-- 2. Shop (All Product, Category, Brands) Simple Dropdown -->
                    <div class="relative group shrink-0" 
                         @mouseenter="shopOpen = true" 
                         @mouseleave="shopOpen = false">
                        <button x-ref="shopBtn"
                                type="button"
                                @click="toggleShop($event)"
                                class="relative pb-1 flex items-center gap-1 sm:gap-1.5 transition cursor-pointer {{ (request()->routeIs('products*') && !request()->get('sort') && !request()->has('discount')) ? 'text-[#650506] font-bold after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-[#650506]' : 'hover:text-[#650506]' }}">
                            <span>SHOP</span>
                            <svg class="w-3 h-3 text-gray-400 group-hover:text-[#650506] transition-transform duration-200" :class="shopOpen ? 'rotate-180 text-[#650506]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <!-- Desktop Dropdown Menu (Pops down right under button, zero clipping via lg:overflow-visible) -->
                        <div x-show="shopOpen" 
                             x-cloak 
                             x-transition:enter="transition ease-out duration-150" 
                             x-transition:enter-start="opacity-0 translate-y-1 scale-95" 
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100" 
                             x-transition:leave="transition ease-in duration-100" 
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
                             x-transition:leave-end="opacity-0 translate-y-1 scale-95" 
                             class="hidden lg:block absolute left-0 top-full pt-2 w-48 z-50">
                            <div class="bg-white rounded-xl shadow-xl border border-gray-100 p-2 text-xs normal-case tracking-normal space-y-0.5">
                                <a href="{{ route('products.index') }}" 
                                   class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-stone-50 hover:text-[#650506] text-gray-800 font-medium transition group">
                                    <span>All Product</span>
                                    <span class="text-gray-400 group-hover:text-[#650506] transition-transform group-hover:translate-x-0.5">&rarr;</span>
                                </a>
                                <a href="{{ route('home') }}#categories" 
                                   class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-stone-50 hover:text-[#650506] text-gray-800 font-medium transition group">
                                    <span>Category</span>
                                    <span class="text-gray-400 group-hover:text-[#650506] transition-transform group-hover:translate-x-0.5">&rarr;</span>
                                </a>
                                <a href="{{ route('home') }}#brands" 
                                   class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-stone-50 hover:text-[#650506] text-gray-800 font-medium transition group">
                                    <span>Brands</span>
                                    <span class="text-gray-400 group-hover:text-[#650506] transition-transform group-hover:translate-x-0.5">&rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Best Sellers -->
                    <a href="{{ route('products.index', ['sort' => 'popular']) }}" class="relative pb-1 transition shrink-0 {{ request()->get('sort') === 'popular' ? 'text-[#650506] font-bold after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-[#650506]' : 'hover:text-[#650506]' }}">
                        BEST SELLERS
                    </a>

                    <!-- 4. Special Offers -->
                    <a href="{{ route('products.index', ['discount' => 1]) }}" class="relative pb-1 transition shrink-0 {{ request()->has('discount') ? 'text-[#650506] font-bold after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-[#650506]' : 'hover:text-[#650506]' }}">
                        SPECIAL OFFERS
                    </a>

                    <!-- 5. Store Locator -->
                    <a href="{{ route('stores.index') }}" class="relative pb-1 transition shrink-0 {{ request()->routeIs('stores*') ? 'text-[#650506] font-bold after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-[#650506]' : 'hover:text-[#650506]' }}">
                        STORE LOCATOR
                    </a>

                    <!-- 6. About Us -->
                    <a href="{{ route('home') }}#about-us" class="relative pb-1 transition shrink-0 hover:text-[#650506]">
                        ABOUT US
                    </a>

                    <!-- 7. Contact -->
                    <a href="https://wa.me/6281188888888" target="_blank" class="relative pb-1 transition shrink-0 hover:text-[#650506]">
                        CONTACT
                    </a>
                </div>

                <!-- Mobile Dropdown Menu (Positioned outside scroll track so it is never clipped by overflow-x on mobile) -->
                <div x-show="shopOpen" 
                     x-cloak 
                     x-transition:enter="transition ease-out duration-150" 
                     x-transition:enter-start="opacity-0 -translate-y-2 scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100" 
                     x-transition:leave="transition ease-in duration-100" 
                     x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
                     x-transition:leave-end="opacity-0 -translate-y-2 scale-95" 
                     :style="{ left: mobileShopLeft + 'px' }"
                     class="lg:hidden absolute top-full pt-1.5 w-48 z-50">
                    <div class="bg-white rounded-xl shadow-xl border border-gray-100 p-2 text-xs normal-case tracking-normal space-y-0.5">
                        <a href="{{ route('products.index') }}" 
                           @click="shopOpen = false"
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-stone-50 hover:text-[#650506] text-gray-800 font-medium transition group">
                            <span>All Product</span>
                            <span class="text-gray-400 group-hover:text-[#650506] transition-transform group-hover:translate-x-0.5">&rarr;</span>
                        </a>
                        <a href="{{ route('home') }}#categories" 
                           @click="shopOpen = false"
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-stone-50 hover:text-[#650506] text-gray-800 font-medium transition group">
                            <span>Category</span>
                            <span class="text-gray-400 group-hover:text-[#650506] transition-transform group-hover:translate-x-0.5">&rarr;</span>
                        </a>
                        <a href="{{ route('home') }}#brands" 
                           @click="shopOpen = false"
                           class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-stone-50 hover:text-[#650506] text-gray-800 font-medium transition group">
                            <span>Brands</span>
                            <span class="text-gray-400 group-hover:text-[#650506] transition-transform group-hover:translate-x-0.5">&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Mobile Arrow Slide Toggle Button (Right Edge) -->
                <div class="lg:hidden absolute right-0 top-0 bottom-0 flex items-center pr-1.5 pl-6 bg-gradient-to-l from-white via-white/95 to-transparent pointer-events-none">
                    <button @click="slideToggle()" 
                            type="button" 
                            class="pointer-events-auto p-1.5 text-gray-500 hover:text-[#650506] transition-colors cursor-pointer flex items-center justify-center focus:outline-none" 
                            :title="slidRight ? 'Kembali ke awal' : 'Lihat menu lainnya'"
                            aria-label="Slide Navigation">
                        <span class="sr-only" aria-label="Open Navigation Menu">Open Navigation Menu</span>
                        <span class="sr-only" aria-label="Close Navigation Menu">Close Navigation Menu</span>
                        <!-- Single smoothly rotating arrow: 0deg when pointing right, 180deg when pointing left -->
                        <svg class="w-4 h-4 transition-transform duration-300 ease-in-out transform" 
                             :class="slidRight ? 'rotate-180 text-[#650506]' : 'text-gray-500 hover:text-[#650506]'" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </nav>
        </div>
    </header>



    <!-- Main Content -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Lumora 5-Column Clean Light Footer -->
    @unless(request()->routeIs('cart*') || request()->is('cart*'))
    <footer class="bg-white text-gray-600 mt-6 sm:mt-10 lg:mt-14 border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 sm:pt-12 lg:pt-14 pb-24 sm:pb-24 lg:pb-12">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-12 gap-x-5 gap-y-7 sm:gap-8 lg:gap-8 pb-8 sm:pb-12 border-b border-gray-200">
                <!-- Col 1: Brand Info & Socials (Full width on mobile/tablet, 3 cols on desktop) -->
                <div class="col-span-2 sm:col-span-3 lg:col-span-3 space-y-3 sm:space-y-4 pr-0 lg:pr-6 pb-4 sm:pb-0 border-b sm:border-b-0 border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('images/logo.png') }}" alt="Aroma Palace" class="w-8 h-8 sm:w-9 sm:h-9 object-contain rounded-full shadow-2xs">
                        <span class="text-sm sm:text-base font-extrabold tracking-[0.14em] text-[#4A070B] uppercase">AROMA PALACE</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed max-w-sm">
                        Luxury fragrances and artisanal lifestyle essentials. Quality products, exceptional service direct from Dubai.
                    </p>
                    <div class="flex items-center gap-2 pt-1">
                        <!-- Facebook -->
                        <a href="https://facebook.com" target="_blank" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white border border-gray-200 text-gray-600 hover:text-black hover:border-gray-400 flex items-center justify-center transition" title="Facebook">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.556 5 15.964 5H18V0h-3.808C10.595 0 9 1.582 9 4.615V8z"/></svg>
                        </a>
                        <!-- Instagram -->
                        <a href="https://instagram.com" target="_blank" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white border border-gray-200 text-gray-600 hover:text-black hover:border-gray-400 flex items-center justify-center transition" title="Instagram">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <!-- Twitter / X -->
                        <a href="https://twitter.com" target="_blank" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white border border-gray-200 text-gray-600 hover:text-black hover:border-gray-400 flex items-center justify-center transition" title="Twitter">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <!-- Pinterest -->
                        <a href="https://pinterest.com" target="_blank" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white border border-gray-200 text-gray-600 hover:text-black hover:border-gray-400 flex items-center justify-center transition" title="Pinterest">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.372 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738.098.119.112.224.083.345-.09.375-.291 1.199-.334 1.357-.057.235-.188.285-.434.171-1.62-.754-2.634-3.123-2.634-5.028 0-4.094 2.974-7.854 8.577-7.854 4.504 0 8.004 3.209 8.004 7.5 0 4.475-2.822 8.077-6.738 8.077-1.316 0-2.554-.684-2.977-1.492l-.811 3.09c-.293 1.121-1.085 2.525-1.616 3.391C9.697 23.85 10.825 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Shop (1 col on mobile, 1 on tablet, 2 cols on desktop) -->
                <div class="col-span-1 sm:col-span-1 lg:col-span-2 space-y-2.5 sm:space-y-3">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-gray-900">Shop</h5>
                    <ul class="space-y-2 text-xs text-gray-600">
                        <li><a href="{{ route('products.index') }}" class="hover:text-black transition">All Products</a></li>
                        <li><a href="{{ route('products.index', ['sort' => 'popular']) }}" class="hover:text-black transition">Best Sellers</a></li>
                        <li><a href="{{ route('products.index', ['sort' => 'newest']) }}" class="hover:text-black transition">New Arrivals</a></li>
                        <li><a href="{{ route('products.index') }}" class="hover:text-black transition">Categories</a></li>
                        <li><a href="{{ route('products.index', ['discount' => 1]) }}" class="hover:text-black transition">Sale</a></li>
                    </ul>
                </div>

                <!-- Col 3: Customer Service (1 col on mobile, 1 on tablet, 2 cols on desktop) -->
                <div class="col-span-1 sm:col-span-1 lg:col-span-2 space-y-2.5 sm:space-y-3">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-gray-900">Customer Service</h5>
                    <ul class="space-y-2 text-xs text-gray-600">
                        <li><a href="https://wa.me/6281188888888" target="_blank" class="hover:text-black transition">Help Center</a></li>
                        <li><a href="{{ route('account.orders') }}" class="hover:text-black transition">Track Order</a></li>
                        <li><a href="#" class="hover:text-black transition">Returns &amp; Refunds</a></li>
                        <li><a href="#" class="hover:text-black transition">Shipping Info</a></li>
                        <li><a href="#" class="hover:text-black transition">FAQs</a></li>
                    </ul>
                </div>

                <!-- Col 4: Company (1 col on mobile, 1 on tablet, 2 cols on desktop) -->
                <div class="col-span-1 sm:col-span-1 lg:col-span-2 space-y-2.5 sm:space-y-3">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-gray-900">Company</h5>
                    <ul class="space-y-2 text-xs text-gray-600">
                        <li><a href="{{ route('home') }}" class="hover:text-black transition">About Us</a></li>
                        <li><a href="{{ route('articles.index') }}" class="hover:text-black transition">Our Story</a></li>
                        <li><a href="#" class="hover:text-black transition">Careers</a></li>
                        <li><a href="https://wa.me/6281188888888" target="_blank" class="hover:text-black transition">Contact Us</a></li>
                        <li><a href="{{ route('articles.index') }}" class="hover:text-black transition">Blog</a></li>
                    </ul>
                </div>

                <!-- Col 4b (Mobile Only): Quick WhatsApp Consultation to complete 2x2 grid -->
                <div class="col-span-1 sm:hidden space-y-2.5">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-gray-900">Konsultasi</h5>
                    <p class="text-[11px] text-gray-500 leading-relaxed">
                        Butuh saran aroma wewangian Dubai?
                    </p>
                    <a href="https://wa.me/6281188888888" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#650506] hover:underline pt-0.5">
                        <span>Chat WhatsApp</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <!-- Col 5: Download App (Full width on mobile/tablet, 3 cols on desktop) -->
                <div class="col-span-2 sm:col-span-3 lg:col-span-3 space-y-3 sm:space-y-3.5 pt-4 sm:pt-6 lg:pt-0 border-t sm:border-t-0 border-gray-100">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-gray-900">Download App</h5>
                    <p class="text-[11px] sm:text-xs text-gray-500 leading-relaxed">
                        Unduh aplikasi kami untuk akses eksklusif katalog parfum Dubai.
                    </p>
                    <div class="flex flex-row flex-wrap sm:flex-row lg:flex-col xl:flex-row gap-2 sm:gap-2.5 pt-0.5">
                        <!-- Google Play Store -->
                        <a href="https://play.google.com" target="_blank" class="flex-1 sm:flex-initial min-w-[130px] inline-flex items-center gap-2 sm:gap-2.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-lg bg-white border border-gray-200/90 text-gray-900 hover:border-gray-400 hover:bg-gray-50 transition shadow-2xs shrink-0 group">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3.6 2.4C3.2 2.8 3 3.4 3 4.2V19.8C3 20.6 3.2 21.2 3.6 21.6L3.7 21.7L13.6 12L3.7 2.3L3.6 2.4Z" fill="#00D2FF"/>
                                <path d="M16.9 15.3L13.6 12L3.6 21.7C4.1 22.2 4.9 22.3 5.7 21.8L16.9 15.3Z" fill="#00F076"/>
                                <path d="M16.9 8.7L5.7 2.2C4.9 1.7 4.1 1.8 3.6 2.3L13.6 12L16.9 8.7Z" fill="#FF3A44"/>
                                <path d="M16.9 15.3L20.4 13.3C21.4 12.7 21.4 11.3 20.4 10.7L16.9 8.7L13.6 12L16.9 15.3Z" fill="#FFC800"/>
                            </svg>
                            <div class="text-left leading-tight">
                                <span class="text-[8px] sm:text-[9px] uppercase tracking-wider text-gray-500 block font-medium">GET IT ON</span>
                                <span class="text-[11px] sm:text-xs font-bold text-gray-900 tracking-wide block">Google Play</span>
                            </div>
                        </a>

                        <!-- Apple App Store -->
                        <a href="https://www.apple.com/app-store/" target="_blank" class="flex-1 sm:flex-initial min-w-[130px] inline-flex items-center gap-2 sm:gap-2.5 px-3 py-1.5 sm:px-3.5 sm:py-2 rounded-lg bg-white border border-gray-200/90 text-gray-900 hover:border-gray-400 hover:bg-gray-50 transition shadow-2xs shrink-0 group">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 fill-current text-gray-900 shrink-0" viewBox="0 0 24 24">
                                <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.84c.62-.75 1.04-1.8 0.93-2.84-.9.04-1.99.6-2.63 1.35-.57.65-1.07 1.71-.94 2.73 1 .08 2.02-.49 2.64-1.24z"/>
                            </svg>
                            <div class="text-left leading-tight">
                                <span class="text-[8px] sm:text-[9px] uppercase tracking-wider text-gray-500 block font-medium">Download on the</span>
                                <span class="text-[11px] sm:text-xs font-bold text-gray-900 tracking-wide block">App Store</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Row: Copyright -->
            <div class="pt-6 sm:pt-8 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-4 text-[11px] sm:text-xs text-gray-500 text-center sm:text-left">
                <div>
                    &copy; {{ date('Y') }} Aroma Palace. All Rights Reserved.
                </div>
                <div class="flex items-center gap-3 sm:gap-6 text-gray-400">
                    <a href="#" class="hover:text-gray-900 transition">Privacy Policy</a>
                    <span>&bull;</span>
                    <a href="#" class="hover:text-gray-900 transition">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
    @endunless

    <!-- Wishlist Toast Notification -->
    <div id="wishlist-toast" class="fixed top-4 left-1/2 -translate-x-1/2 sm:top-6 sm:right-6 sm:left-auto sm:translate-x-0 z-50 transform -translate-y-12 opacity-0 pointer-events-none transition-all duration-300 ease-out bg-[#18181B]/95 backdrop-blur-md text-white py-2 px-4 rounded-full shadow-xl border border-white/10 flex items-center gap-2.5 text-xs font-medium w-fit max-w-[92vw] sm:max-w-md">
        <span id="wishlist-toast-icon" class="text-rose-400 shrink-0">
            <svg class="w-4 h-4 fill-rose-500 text-rose-500" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
        </span>
        <span id="wishlist-toast-msg" class="truncate">Produk ditambahkan ke wishlist</span>
    </div>

    <!-- Cart Toast Notification -->
    <div id="cart-toast" class="fixed top-4 left-1/2 -translate-x-1/2 sm:top-6 sm:right-6 sm:left-auto sm:translate-x-0 z-50 transform -translate-y-12 opacity-0 pointer-events-none transition-all duration-300 ease-out bg-[#18181B]/95 backdrop-blur-md text-white py-2 px-4 rounded-full shadow-xl border border-white/10 flex items-center gap-2.5 text-xs font-medium w-fit max-w-[92vw] sm:max-w-md">
        <span class="text-[#F4B942] shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </span>
        <span id="cart-toast-msg" class="truncate">Produk ditambahkan ke keranjang</span>
        <a id="cart-toast-link" href="{{ route('cart.index') }}" class="ml-1 underline font-semibold text-[#F4B942] hover:text-amber-300 shrink-0">Lihat &rarr;</a>
    </div>

    <!-- Promo Code Toast Notification -->
    <div id="promo-toast" class="fixed top-4 left-1/2 -translate-x-1/2 sm:top-6 sm:right-6 sm:left-auto sm:translate-x-0 z-50 transform -translate-y-12 opacity-0 pointer-events-none transition-all duration-300 ease-out bg-[#18181B]/95 backdrop-blur-md text-white py-2 px-4 rounded-full shadow-xl border border-white/10 flex items-center gap-2.5 text-xs font-medium w-fit max-w-[92vw] sm:max-w-md">
        <span class="text-emerald-400 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        </span>
        <span id="promo-toast-msg" class="truncate">Kode promo berhasil disalin!</span>
    </div>

    <!-- Session Flash Toast Notification (Simple Toast like Cart & Wishlist) -->
    @if(session('success'))
    <div id="flash-toast-success" class="fixed top-4 left-1/2 -translate-x-1/2 sm:top-6 sm:right-6 sm:left-auto sm:translate-x-0 z-50 transform -translate-y-12 opacity-0 pointer-events-none transition-all duration-300 ease-out bg-[#18181B]/95 backdrop-blur-md text-white py-2 px-4 rounded-full shadow-xl border border-white/10 flex items-center gap-2.5 text-xs font-medium w-fit max-w-[92vw] sm:max-w-md">
        <span class="text-emerald-400 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
        </span>
        <span>{{ session('success') }}</span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('flash-toast-success');
            if (toast) {
                setTimeout(() => {
                    toast.classList.remove('-translate-y-12', 'opacity-0', 'pointer-events-none');
                    toast.classList.add('translate-y-0', 'opacity-100');
                }, 100);

                setTimeout(() => {
                    toast.classList.remove('translate-y-0', 'opacity-100');
                    toast.classList.add('-translate-y-12', 'opacity-0', 'pointer-events-none');
                    setTimeout(() => toast.remove(), 400);
                }, 3500);
            }
        });
    </script>
    @endif

    @if(session('error'))
    <div id="flash-toast-error" class="fixed top-4 left-1/2 -translate-x-1/2 sm:top-6 sm:right-6 sm:left-auto sm:translate-x-0 z-50 transform -translate-y-12 opacity-0 pointer-events-none transition-all duration-300 ease-out bg-[#18181B]/95 backdrop-blur-md text-white py-2 px-4 rounded-full shadow-xl border border-white/10 flex items-center gap-2.5 text-xs font-medium w-fit max-w-[92vw] sm:max-w-md">
        <span class="text-rose-400 shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </span>
        <span>{{ session('error') }}</span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('flash-toast-error');
            if (toast) {
                setTimeout(() => {
                    toast.classList.remove('-translate-y-12', 'opacity-0', 'pointer-events-none');
                    toast.classList.add('translate-y-0', 'opacity-100');
                }, 100);

                setTimeout(() => {
                    toast.classList.remove('translate-y-0', 'opacity-100');
                    toast.classList.add('-translate-y-12', 'opacity-0', 'pointer-events-none');
                    setTimeout(() => toast.remove(), 400);
                }, 4000);
            }
        });
    </script>
    @endif

    <script>
        window.openSpotlight = function() {
            window.dispatchEvent(new CustomEvent('open-spotlight'));
        };

        let wishlistToastTimeout = null;
        function showWishlistToast(message, isAdded = true) {
            const toast = document.getElementById('wishlist-toast');
            const msg = document.getElementById('wishlist-toast-msg');
            const icon = document.getElementById('wishlist-toast-icon');
            if (!toast || !msg) return;

            msg.textContent = message;
            if (isAdded) {
                icon.innerHTML = `<svg class="w-4 h-4 fill-rose-500 text-rose-500" viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>`;
            } else {
                icon.innerHTML = `<svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;
            }

            toast.classList.remove('-translate-y-12', 'opacity-0', 'pointer-events-none');
            toast.classList.add('translate-y-0', 'opacity-100');

            if (wishlistToastTimeout) clearTimeout(wishlistToastTimeout);
            wishlistToastTimeout = setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('-translate-y-12', 'opacity-0', 'pointer-events-none');
            }, 3000);
        }

        async function toggleWishlist(productId, btnElement) {
            @guest
                window.location.href = "{{ route('login') }}";
                return;
            @endguest

            try {
                if (btnElement) {
                    btnElement.disabled = true;
                    btnElement.classList.add('scale-95');
                }

                const response = await fetch("{{ route('wishlist.toggle') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId })
                });

                if (response.status === 401) {
                    window.location.href = "{{ route('login') }}";
                    return;
                }

                const data = await response.json();

                if (data.success) {
                    // Update Wishlist badge in navbar & mobile bottom nav
                    ['wishlist-badge', 'mobile-wishlist-badge'].forEach(id => {
                        const badge = document.getElementById(id);
                        if (badge) {
                            badge.textContent = data.count;
                            if (data.count > 0) {
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                    });

                    // Update all buttons targeting this product id
                    const targetButtons = document.querySelectorAll(`[data-product-id="${productId}"]`);
                    const buttonsToUpdate = targetButtons.length > 0 ? targetButtons : (btnElement ? [btnElement] : []);

                    buttonsToUpdate.forEach(btn => {
                        const svg = btn.querySelector('svg');
                        if (data.is_wishlisted) {
                            btn.classList.add('text-rose-500', 'opacity-100');
                            btn.classList.remove('text-gray-500', 'text-gray-600', 'opacity-0');
                            // Remove responsive/hover opacity overrides so icon stays visible always
                            btn.classList.remove('sm:opacity-0', 'group-hover:opacity-100', 'focus:opacity-100');
                            if (btn.classList.contains('border-gray-300')) {
                                btn.classList.remove('border-gray-300');
                                btn.classList.add('border-rose-200', 'bg-rose-50');
                            }
                            if (svg) {
                                svg.classList.add('fill-rose-500', 'text-rose-500');
                                svg.setAttribute('fill', 'currentColor');
                            }
                            btn.setAttribute('title', 'Hapus dari Wishlist');
                        } else {
                            btn.classList.remove('text-rose-500', 'opacity-100');
                            if (btn.classList.contains('border-rose-200')) {
                                btn.classList.remove('border-rose-200', 'bg-rose-50');
                                btn.classList.add('border-gray-300');
                            }
                            if (!btn.closest('.group')) {
                                btn.classList.add('text-gray-600');
                            } else {
                                // Restore hover-only visibility behaviour
                                btn.classList.add('text-gray-500', 'opacity-0', 'group-hover:opacity-100', 'sm:opacity-0', 'focus:opacity-100');
                            }
                            if (svg) {
                                svg.classList.remove('fill-rose-500', 'text-rose-500');
                                svg.setAttribute('fill', 'none');
                            }
                            btn.setAttribute('title', 'Tambah ke Wishlist');
                        }
                    });

                    showWishlistToast(data.message, data.is_wishlisted);

                    // --- Wishlist page: auto-remove card when un-wishlisted ---
                    const isWishlistPage = document.querySelector('[data-wishlist-page]') !== null;
                    if (isWishlistPage && !data.is_wishlisted) {
                        // Find the card wrapper closest to any button for this product
                        const card = btnElement
                            ? btnElement.closest('[data-wishlist-card]')
                            : document.querySelector(`[data-wishlist-card][data-product-id="${productId}"]`);

                        if (card) {
                            // Fade + slide out
                            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.9)';
                            setTimeout(() => {
                                card.remove();

                                // Update "Total Items" counter
                                const remaining = document.querySelectorAll('[data-wishlist-card]').length;
                                const countEl = document.getElementById('wishlist-total-count');
                                if (countEl) countEl.textContent = remaining;

                                const countWrapper = document.getElementById('wishlist-total-wrapper');
                                if (countWrapper) {
                                    if (remaining === 0) countWrapper.style.display = 'none';
                                }

                                // Show empty state if no cards left
                                if (remaining === 0) {
                                    const grid = document.getElementById('wishlist-grid');
                                    const footer = document.getElementById('wishlist-footer');
                                    if (grid) grid.style.display = 'none';
                                    if (footer) footer.style.display = 'none';

                                    const emptyState = document.getElementById('wishlist-empty-state');
                                    if (emptyState) emptyState.classList.remove('hidden');
                                }
                            }, 300);
                        }
                    }
                } else {
                    showWishlistToast(data.message || 'Terjadi kesalahan.', false);
                }
            } catch (error) {
                console.error('Wishlist toggle error:', error);
                showWishlistToast('Gagal memproses wishlist.', false);
            } finally {
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.classList.remove('scale-95');
                }
            }
        }

        // ── Cart Toast ───────────────────────────────────────────────
        let cartToastTimeout = null;
        function showCartToast(message) {
            const toast = document.getElementById('cart-toast');
            const msg   = document.getElementById('cart-toast-msg');
            if (!toast) return;
            if (msg) msg.textContent = message;

            toast.classList.remove('-translate-y-12', 'opacity-0', 'pointer-events-none');
            toast.classList.add('translate-y-0', 'opacity-100');

            if (cartToastTimeout) clearTimeout(cartToastTimeout);
            cartToastTimeout = setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('-translate-y-12', 'opacity-0', 'pointer-events-none');
            }, 3500);
        }

        async function addToCart(form, btnElement) {
            try {
                if (btnElement) {
                    btnElement.disabled = true;
                    btnElement.classList.add('opacity-70');
                }

                const formData = new FormData(form);
                const body = {};
                formData.forEach((v, k) => { body[k] = v; });

                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(body),
                });

                const data = await response.json();

                if (data.success) {
                    // Update cart badge with jumlah produk (total_items) on navbar & mobile bottom nav
                    const totalProducts = data.data?.total_items ?? (data.data?.items?.length ?? 0);
                    ['cart-badge', 'mobile-cart-badge'].forEach(id => {
                        const badge = document.getElementById(id);
                        if (badge) {
                            badge.textContent = totalProducts;
                            if (totalProducts > 0) {
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                    });
                    showCartToast(data.message || 'Produk ditambahkan ke keranjang');
                } else {
                    showCartToast(data.message || 'Gagal menambahkan ke keranjang');
                }
            } catch (error) {
                console.error('Add to cart error:', error);
                showCartToast('Gagal menambahkan ke keranjang');
            } finally {
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.classList.remove('opacity-70');
                }
            }
        }

        // ── Promo Toast & Copy ─────────────────────────────────────────
        let promoToastTimeout = null;
        function showPromoToast(message) {
            const toast = document.getElementById('promo-toast');
            const msg   = document.getElementById('promo-toast-msg');
            if (!toast) return;
            if (msg) msg.textContent = message;

            toast.classList.remove('-translate-y-12', 'opacity-0', 'pointer-events-none');
            toast.classList.add('translate-y-0', 'opacity-100');

            if (promoToastTimeout) clearTimeout(promoToastTimeout);
            promoToastTimeout = setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('-translate-y-12', 'opacity-0', 'pointer-events-none');
            }, 3000);
        }

        function copyPromoCode(code) {
            if (!code) return;

            const fallbackCopy = (text) => {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.top = '-9999px';
                textArea.style.left = '-9999px';
                textArea.setAttribute('readonly', '');
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                } catch (err) {
                    console.error('Fallback copy failed:', err);
                }
                document.body.removeChild(textArea);
                showPromoToast('Kode promo "' + text + '" berhasil disalin!');
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(code).then(() => {
                    showPromoToast('Kode promo "' + code + '" berhasil disalin!');
                }).catch(() => {
                    fallbackCopy(code);
                });
            } else {
                fallbackCopy(code);
            }
        }
    </script>

    <!-- Mobile Bottom Navigation Bar (App Menubar) -->
    @unless(request()->routeIs('cart*') || request()->is('cart*'))
        @include('components.mobile-bottom-nav')
    @endunless

    <!-- macOS Spotlight Search Modal -->
    @include('components.spotlight-search')
</body>
</html>
