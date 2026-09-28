@php
    $cartCount = $cartCount ?? (auth()->check() ? \App\Models\CartItem::whereHas('cart', fn($q) => $q->where('user_id', auth()->id()))->count() : 0);
    $wishlistCount = $wishlistCount ?? (auth()->check() ? \App\Models\Wishlist::where('user_id', auth()->id())->count() : 0);
@endphp
@unless(request()->routeIs('cart*') || request()->is('cart*'))
<!-- Mobile Bottom Navigation Bar (Native App Style Menubar) -->
<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-lg border-t border-gray-200/80 shadow-[0_-4px_25px_rgba(0,0,0,0.06)] px-1 sm:px-3 pt-1"
     style="padding-bottom: max(env(safe-area-inset-bottom), 6px);"
     aria-label="Navigasi Mobile">
    <div class="max-w-md mx-auto flex items-center justify-between h-14 sm:h-16" style="display: flex; width: 100%;">
        
        <!-- 1. Beranda (Home) -->
        <a href="{{ route('home') }}" 
           class="flex-1 flex flex-col items-center justify-center py-1 group transition-transform active:scale-90 {{ request()->routeIs('home') ? 'text-[#650506]' : 'text-gray-400 hover:text-gray-600' }}"
           style="flex: 1 1 0%; min-width: 0; text-align: center;">
            <div class="relative flex items-center justify-center">
                @if(request()->routeIs('home'))
                    <!-- Active Filled Icon -->
                    <svg class="w-5 h-5 text-[#650506] transition-transform" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/>
                    </svg>
                @else
                    <!-- Inactive Outline Icon -->
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                @endif
            </div>
            <span class="text-[10px] tracking-tight mt-1 truncate max-w-full block {{ request()->routeIs('home') ? 'font-bold text-[#650506]' : 'font-medium text-gray-500' }}">
                Beranda
            </span>
        </a>

        <!-- 2. Katalog / Shop -->
        <a href="{{ route('products.index') }}" 
           class="flex-1 flex flex-col items-center justify-center py-1 group transition-transform active:scale-90 {{ request()->routeIs('products*') ? 'text-[#650506]' : 'text-gray-400 hover:text-gray-600' }}"
           style="flex: 1 1 0%; min-width: 0; text-align: center;">
            <div class="relative flex items-center justify-center">
                @if(request()->routeIs('products*'))
                    <svg class="w-5 h-5 text-[#650506] transition-transform" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M4 4h4v4H4zm6 0h4v4h-4zm6 0h4v4h-4zM4 10h4v4H4zm6 0h4v4h-4zm6 0h4v4h-4zM4 16h4v4H4zm6 0h4v4h-4zm6 0h4v4h-4z"/>
                    </svg>
                @else
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                @endif
            </div>
            <span class="text-[10px] tracking-tight mt-1 truncate max-w-full block {{ request()->routeIs('products*') ? 'font-bold text-[#650506]' : 'font-medium text-gray-500' }}">
                Katalog
            </span>
        </a>

        <!-- 3. Wishlist (Wajib Login) -->
        <a href="@auth {{ route('wishlist.index') }} @else {{ route('login') }} @endauth" 
           class="flex-1 flex flex-col items-center justify-center py-1 group transition-transform active:scale-90 {{ request()->routeIs('wishlist*') ? 'text-[#650506]' : 'text-gray-400 hover:text-gray-600' }}"
           style="flex: 1 1 0%; min-width: 0; text-align: center;">
            <div class="relative flex items-center justify-center">
                @if(request()->routeIs('wishlist*'))
                    <svg class="w-5 h-5 text-[#650506] transition-transform" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                @else
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                @endif
                <!-- Wishlist Badge -->
                <span id="mobile-wishlist-badge" 
                      class="{{ $wishlistCount > 0 ? '' : 'hidden' }} absolute -top-1 -right-2 min-w-[16px] h-4 px-1 bg-[#650506] text-white rounded-full text-[9px] font-bold flex items-center justify-center leading-none shadow-2xs">
                    {{ $wishlistCount }}
                </span>
            </div>
            <span class="text-[10px] tracking-tight mt-1 truncate max-w-full block {{ request()->routeIs('wishlist*') ? 'font-bold text-[#650506]' : 'font-medium text-gray-500' }}">
                Wishlist
            </span>
        </a>


        <!-- 5. Akun / Profile / Masuk -->
        @auth
            <a href="{{ route('account.index') }}" 
               class="flex-1 flex flex-col items-center justify-center py-1 group transition-transform active:scale-90 {{ request()->routeIs('account*') ? 'text-[#650506]' : 'text-gray-400 hover:text-gray-600' }}"
               style="flex: 1 1 0%; min-width: 0; text-align: center;">
                <div class="relative flex items-center justify-center">
                    @if(request()->routeIs('account*'))
                        <svg class="w-5 h-5 text-[#650506] transition-transform" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @endif
                </div>
                <span class="text-[10px] tracking-tight mt-1 truncate max-w-full block {{ request()->routeIs('account*') ? 'font-bold text-[#650506]' : 'font-medium text-gray-500' }}">
                    Akun
                </span>
            </a>
        @else
            <a href="{{ route('login') }}" 
               class="flex-1 flex flex-col items-center justify-center py-1 group transition-transform active:scale-90 {{ request()->routeIs('login', 'register*') ? 'text-[#650506]' : 'text-gray-400 hover:text-gray-600' }}"
               style="flex: 1 1 0%; min-width: 0; text-align: center;">
                <div class="relative flex items-center justify-center">
                    @if(request()->routeIs('login', 'register*'))
                        <svg class="w-5 h-5 text-[#650506] transition-transform" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                        </svg>
                    @else
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    @endif
                </div>
                <span class="text-[10px] tracking-tight mt-1 truncate max-w-full block {{ request()->routeIs('login', 'register*') ? 'font-bold text-[#650506]' : 'font-medium text-gray-500' }}">
                    Masuk
                </span>
            </a>
        @endauth

    </div>
</nav>
@endunless

