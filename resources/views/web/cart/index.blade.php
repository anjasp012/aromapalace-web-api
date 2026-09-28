@extends('layouts.app')

@section('title', 'Shopping Bag - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6"
     :class="items.length > 0 ? 'pb-20 sm:pb-24 lg:pb-8' : ''"
     x-data="cartApp(@js($summary))"
     x-cloak>

    <!-- Breadcrumb -->
    <nav class="text-[11px] sm:text-xs text-gray-500 flex items-center gap-1.5 sm:gap-2">
        <a href="{{ route('home') }}" class="hover:text-[#650506] transition">Home</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Shopping Bag</span>
    </nav>

    <!-- Page Header -->
    <div class="flex flex-wrap items-center justify-between gap-2.5 sm:gap-4 border-b border-gray-200/80 pb-3 sm:pb-5">
        <div>
            <span class="text-[10px] sm:text-xs uppercase tracking-widest text-[#650506] font-semibold block mb-0.5 sm:mb-1">Your Order</span>
            <h1 class="text-xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Shopping Bag</h1>
        </div>
        <div class="text-[11px] sm:text-xs text-gray-500 font-medium" x-show="items.length > 0">
            Total Items: <span class="font-bold text-gray-900" x-text="items.length"></span>
        </div>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.show"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
         class="fixed top-4 left-1/2 -translate-x-1/2 sm:top-6 sm:right-6 sm:left-auto sm:translate-x-0 z-50 w-fit max-w-[92vw] sm:max-w-md py-2 px-4 rounded-full shadow-xl flex items-center gap-2.5 border border-white/10 text-xs font-semibold bg-[#18181B]/95 text-white backdrop-blur-md">
        <span class="text-sm shrink-0" x-text="toast.type === 'success' ? '✓' : 'ℹ️'"></span>
        <span x-text="toast.message" class="truncate"></span>
        <button type="button" @click="toast.show = false" class="ml-1 text-gray-400 hover:text-white shrink-0 leading-none">&times;</button>
    </div>

    <!-- Custom Delete Confirmation Modal -->
    <div x-show="deleteModal.show"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         @keydown.escape.window="closeDeleteModal()">
        
        <div x-show="deleteModal.show"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             @click.outside="closeDeleteModal()"
             class="bg-white rounded-xl sm:rounded-2xl max-w-sm w-full p-4 sm:p-6 text-center shadow-2xl border border-gray-100 space-y-3 sm:space-y-4">
            
            <!-- Warning Icon with Royal Theme -->
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-[#FFF5F5] text-[#650506] border border-rose-100 flex items-center justify-center mx-auto">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[#650506]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>

            <div class="space-y-1">
                <h3 class="text-sm sm:text-base font-bold text-gray-900">Hapus Produk?</h3>
                <p class="text-[11px] sm:text-xs text-gray-500 leading-relaxed">
                    Produk <span class="font-semibold text-gray-800" x-text="deleteModal.productName ? '“' + deleteModal.productName + '”' : 'ini'"></span> akan dihapus dari keranjang belanja Anda.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-2.5 sm:gap-3 pt-1 sm:pt-2">
                <button type="button"
                        @click="closeDeleteModal()"
                        :disabled="loading"
                        class="w-full py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition cursor-pointer">
                    Batal
                </button>
                <button type="button"
                        @click="confirmRemoveItem()"
                        :disabled="loading"
                        class="w-full py-2 sm:py-2.5 px-3 sm:px-4 rounded-lg bg-[#650506] hover:bg-[#4A070B] text-white text-xs font-semibold shadow-2xs transition flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50">
                    <span x-show="loading" class="animate-spin text-xs">⏳</span>
                    <span>Hapus</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Active Cart View (Items > 0) -->
    <template x-if="items.length > 0">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8 items-start">
            <!-- Items List (2 Cols) -->
            <div class="lg:col-span-2 bg-white rounded-xl p-3.5 sm:p-6 lg:p-7 border border-gray-200 shadow-2xs space-y-4 sm:space-y-6">
                <div class="divide-y divide-gray-100">
                    <template x-for="item in items" :key="item.id">
                        <div class="py-3.5 sm:py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 transition duration-300"
                             :class="{ 'opacity-50 pointer-events-none': updatingId === item.id }">
                            
                            <!-- Product Image & Details -->
                            <div class="flex items-start sm:items-center gap-2.5 sm:gap-3.5 flex-1 min-w-0">
                                <!-- Thumbnail: full-bleed, no bg -->
                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-lg overflow-hidden shrink-0 border border-gray-100 bg-[#F4F2EE]">
                                    <a :href="'/products/' + item.product_slug" class="block w-full h-full">
                                        <img :src="item.product_image || 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=160&q=80'"
                                             :alt="item.product_name"
                                             class="w-full h-full object-cover">
                                    </a>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <span class="text-[9px] sm:text-[10px] uppercase tracking-wider text-gray-400 font-semibold block truncate" x-text="item.brand_name"></span>
                                    <h4 class="font-bold text-gray-900 text-xs sm:text-sm line-clamp-1 sm:line-clamp-2">
                                        <a :href="'/products/' + item.product_slug" class="hover:text-[#650506] transition" x-text="item.product_name"></a>
                                    </h4>
                                    <template x-if="item.variant_name">
                                        <span class="text-[10px] sm:text-[11px] text-gray-700 bg-gray-100 border border-gray-200 px-1.5 sm:px-2 py-0.5 rounded-md font-medium inline-block mt-0.5"
                                              x-text="'Variant: ' + item.variant_name"></span>
                                    </template>
                                    <span class="text-[11px] sm:text-xs text-gray-500 block mt-0.5 sm:mt-1 font-mono" x-text="$money(item.unit_price) + ' / item'"></span>
                                </div>

                                <!-- X Delete Button (Mobile: Far Right of Header) -->
                                <button type="button"
                                        @click="openDeleteModal(item)"
                                        :disabled="loading"
                                        title="Remove from cart"
                                        class="sm:hidden w-7 h-7 rounded-full text-gray-400 hover:text-rose-500 hover:bg-rose-50 flex items-center justify-center transition cursor-pointer shrink-0 -mr-1 -mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Stepper & Subtotal -->
                            <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-5 pt-2 sm:pt-0 border-t border-gray-100/60 sm:border-0 shrink-0">
                                <!-- Interactive Quantity Stepper -->
                                <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden bg-white shadow-2xs">
                                    <button type="button"
                                            @click="updateQuantity(item.id, item.quantity - 1)"
                                            :disabled="item.quantity <= 1 || loading"
                                            class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-gray-700 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition font-bold text-xs sm:text-sm">
                                        -
                                    </button>
                                    <span class="w-8 sm:w-10 text-center text-xs font-semibold text-gray-900 font-mono" x-text="item.quantity"></span>
                                    <button type="button"
                                            @click="updateQuantity(item.id, item.quantity + 1)"
                                            :disabled="item.quantity >= item.available_stock || loading"
                                            class="w-7 h-7 sm:w-8 sm:h-8 flex items-center justify-center text-gray-700 hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition font-bold text-xs sm:text-sm">
                                        +
                                    </button>
                                </div>

                                <!-- Subtotal -->
                                <div class="text-right min-w-[85px] sm:min-w-[100px]">
                                    <span class="font-bold text-gray-900 text-xs sm:text-sm block font-mono" x-text="$money(item.subtotal)"></span>
                                </div>

                                <!-- X Delete Button (Desktop: Far Right of Row) -->
                                <button type="button"
                                        @click="openDeleteModal(item)"
                                        :disabled="loading"
                                        title="Remove from cart"
                                        class="hidden sm:flex w-7 h-7 rounded-full text-gray-400 hover:text-rose-500 hover:bg-rose-50 items-center justify-center transition cursor-pointer shrink-0 ml-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Mobile Cross-Navigation (lg:hidden) -->
                <div class="lg:hidden pt-3 border-t border-gray-100 flex items-center justify-between text-xs px-1 text-gray-500">
                    <a href="{{ route('products.index') }}" class="font-medium hover:text-black transition">
                        &larr; Continue Shopping
                    </a>
                    <a href="{{ route('wishlist.index') }}" class="font-semibold text-[#650506] hover:text-[#4A070B] transition flex items-center gap-1">
                        <span>View Wishlist</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- Order Summary Card (Desktop Only: lg:block) -->
            <div class="hidden lg:block bg-white rounded-xl p-6 lg:p-7 border border-gray-200 shadow-2xs space-y-5 h-fit lg:sticky lg:top-28">
                <h3 class="text-base sm:text-lg font-bold text-gray-900 border-b border-gray-100 pb-2.5 sm:pb-3">Order Summary</h3>

                <div class="space-y-2.5 sm:space-y-3 text-xs text-gray-600">
                    <div class="flex justify-between items-center">
                        <span>Subtotal (<span x-text="items.length"></span> items)</span>
                        <span class="font-bold text-gray-900 text-xs sm:text-sm font-mono" x-text="$money(subtotal)"></span>
                    </div>

                    <template x-if="discountAmount > 0">
                        <div class="flex justify-between items-center text-emerald-700 font-semibold">
                            <span>Discount</span>
                            <span class="font-mono text-xs sm:text-sm" x-text="'- ' + $money(discountAmount)"></span>
                        </div>
                    </template>

                    <div class="border-t border-gray-200 pt-3 flex justify-between items-baseline">
                        <div>
                            <span class="text-xs sm:text-sm font-bold text-gray-900 block">Total</span>
                            <span class="text-[10px] sm:text-[11px] text-gray-400">Shipping calculated at checkout</span>
                        </div>
                        <span class="text-xl sm:text-2xl font-extrabold text-[#650506]" x-text="$money(totalAmount)"></span>
                    </div>
                </div>

                <!-- Promo Code -->
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="text"
                               x-model="promoInput"
                               @keydown.enter.prevent="applyPromo()"
                               placeholder="Enter promo code"
                               class="uppercase font-mono bg-gray-50 border border-gray-300 rounded-lg px-2.5 sm:px-3 py-2 text-xs tracking-wider flex-1 focus:outline-none focus:border-[#650506] focus:bg-white">
                        <button type="button"
                                @click="applyPromo()"
                                :disabled="promoLoading || !promoInput.trim()"
                                class="bg-[#650506] hover:bg-[#4A070B] text-white font-medium text-xs px-3 sm:px-4 py-2 rounded-lg transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1.5 cursor-pointer shrink-0">
                            <span x-show="promoLoading" class="animate-spin text-xs">⏳</span>
                            <span>Apply</span>
                        </button>
                    </div>

                    <!-- Active Promo Banner -->
                    <template x-if="appliedPromo">
                        <div class="p-2.5 sm:p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs flex items-center justify-between">
                            <div class="flex items-center gap-1.5 sm:gap-2 text-emerald-800">
                                <span>🏷️</span>
                                <span class="font-bold uppercase font-mono tracking-wider text-[11px] sm:text-xs" x-text="appliedPromo.code"></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold font-mono text-emerald-700 text-xs sm:text-sm" x-text="'- ' + $money(discountAmount)"></span>
                                <button type="button" @click="removePromo()" :disabled="promoLoading"
                                        class="text-emerald-600 hover:text-rose-600 transition cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <a href="{{ route('checkout.index') }}"
                   class="w-full bg-[#650506] hover:bg-[#4A070B] text-white font-medium py-3 sm:py-3.5 px-4 sm:px-6 rounded-lg shadow-sm transition flex items-center justify-center gap-2 text-xs sm:text-sm">
                    <span>Proceed to Checkout</span>
                    <span>&rarr;</span>
                </a>

                <div class="pt-2.5 sm:pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                    <a href="{{ route('products.index') }}" class="font-semibold text-gray-500 hover:text-black transition">
                        &larr; Continue Shopping
                    </a>
                    <a href="{{ route('wishlist.index') }}" class="font-semibold text-[#650506] hover:text-[#4A070B] transition flex items-center gap-1">
                        <span>View Wishlist</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>
    </template>

    <!-- Mobile Sticky Order Summary & Promo Bar (Nempel di bawah pengganti Menubar) -->
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 border-t border-gray-200/80 px-3.5 sm:px-4 pt-2.5 pb-2"
         style="padding-bottom: max(env(safe-area-inset-bottom), 8px);"
         x-show="items.length > 0"
         x-data="{ showBreakdown: false }">

        <div class="max-w-md mx-auto space-y-2">
            <!-- 1. Sticky Enter Promo Code Row (Di atas Order Breakdown) -->
            <div>
                <!-- If No Promo Applied: Input & Apply -->
                <template x-if="!appliedPromo">
                    <div class="flex items-center gap-1.5 bg-gray-50 border border-gray-200/90 rounded-lg p-1 transition focus-within:border-[#650506] focus-within:bg-white">
                        <div class="flex items-center gap-1 pl-1.5 text-gray-400">
                            <span class="text-xs">🏷️</span>
                        </div>
                        <input type="text"
                               x-model="promoInput"
                               @keydown.enter.prevent="applyPromo()"
                               placeholder="Enter promo code"
                               class="uppercase font-mono bg-transparent text-xs tracking-wider flex-1 py-1 px-1 focus:outline-none placeholder:text-gray-400">
                        <button type="button"
                                @click="applyPromo()"
                                :disabled="promoLoading || !promoInput.trim()"
                                class="bg-[#650506] hover:bg-[#4A070B] text-white font-medium text-xs px-3 py-1.5 rounded-md transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1 cursor-pointer shrink-0">
                            <span x-show="promoLoading" class="animate-spin text-[10px]">⏳</span>
                            <span>Apply</span>
                        </button>
                    </div>
                </template>

                <!-- If Promo Applied: Applied Banner with Discount & Remove -->
                <template x-if="appliedPromo">
                    <div class="px-2.5 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-emerald-800 min-w-0">
                            <span class="text-xs">🏷️</span>
                            <span class="font-bold uppercase font-mono tracking-wider text-[11px] truncate" x-text="appliedPromo.code"></span>
                            <span class="text-[10px] text-emerald-600 font-semibold">(Applied)</span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="font-bold font-mono text-emerald-700 text-xs" x-text="'- ' + $money(discountAmount)"></span>
                            <button type="button" @click="removePromo()" :disabled="promoLoading"
                                    title="Remove promo code"
                                    class="text-emerald-600 hover:text-rose-600 transition cursor-pointer p-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- 2. Expandable Order Breakdown Panel (Di bawah Promo Code, di atas Total & Checkout) -->
            <div x-show="showBreakdown"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="bg-gray-50/90 rounded-xl p-3 border border-gray-200/90 space-y-2">
                
                <div class="flex items-center justify-between border-b border-gray-200/60 pb-1.5">
                    <span class="text-[11px] font-bold text-gray-800 uppercase tracking-wider">Order Breakdown</span>
                    <button type="button" @click="showBreakdown = false" class="text-gray-400 hover:text-gray-700 text-xs font-semibold p-0.5">Tutup</button>
                </div>

                <div class="space-y-1.5 text-xs text-gray-600">
                    <div class="flex justify-between items-center">
                        <span>Subtotal (<span x-text="items.length"></span> items)</span>
                        <span class="font-bold text-gray-900 font-mono" x-text="$money(subtotal)"></span>
                    </div>
                    <template x-if="discountAmount > 0">
                        <div class="flex justify-between items-center text-emerald-700 font-semibold">
                            <span>Discount</span>
                            <span class="font-mono text-xs" x-text="'- ' + $money(discountAmount)"></span>
                        </div>
                    </template>
                    <div class="flex justify-between items-center text-gray-400 text-[11px]">
                        <span>Shipping</span>
                        <span>Calculated at checkout</span>
                    </div>
                </div>
            </div>

            <!-- Sticky Total & Checkout Row -->
            <div class="flex items-center justify-between gap-3 pt-0.5">
                <!-- Left: Total Price & Breakdown Toggle -->
                <div class="flex flex-col min-w-0">
                    <button type="button"
                            @click="showBreakdown = !showBreakdown"
                            class="flex items-center gap-1 text-[10px] text-gray-500 hover:text-[#650506] font-medium leading-none text-left cursor-pointer">
                        <span>Total Amount</span>
                        <svg class="w-3 h-3 text-gray-400 transition-transform duration-200"
                             :class="{ 'rotate-180': showBreakdown }"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
                        </svg>
                        <template x-if="discountAmount > 0">
                            <span class="text-[9px] text-emerald-700 bg-emerald-50 px-1 py-0.2 rounded font-semibold font-mono"
                                  x-text="'Saved ' + $money(discountAmount)"></span>
                        </template>
                    </button>
                    <span class="text-base sm:text-lg font-extrabold text-[#650506] font-mono leading-tight mt-0.5 truncate"
                          x-text="$money(totalAmount)"></span>
                </div>

                <!-- Right: Proceed to Checkout Button -->
                <a href="{{ route('checkout.index') }}"
                   class="bg-[#650506] hover:bg-[#4A070B] active:scale-95 text-white font-semibold py-2.5 px-4 sm:px-6 rounded-lg shadow-sm transition flex items-center justify-center gap-1.5 text-xs sm:text-sm shrink-0">
                    <span>Proceed to Checkout</span>
                    <span class="text-[11px] opacity-80" x-text="'(' + items.length + ')'"></span>
                    <span>&rarr;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Empty Cart State (Items == 0) -->
    <div x-show="items.length === 0"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="py-12 sm:py-16 text-center max-w-lg mx-auto space-y-3 sm:space-y-4">
        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-[#F4F2EE] text-[#650506] rounded-full flex items-center justify-center mx-auto">
            <svg class="w-7 h-7 sm:w-8 sm:h-8 text-[#650506]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
        </div>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900">Your bag is empty</h3>
        <p class="text-[11px] sm:text-xs text-gray-500 max-w-sm mx-auto leading-relaxed px-4">
            Looks like you haven't added anything to your cart yet. Explore our curated collections.
        </p>
        <div class="pt-2 sm:pt-3">
            <a href="{{ route('products.index') }}"
               class="bg-[#650506] hover:bg-[#4A070B] text-white font-semibold text-xs px-5 sm:px-6 py-2.5 sm:py-3 rounded-lg shadow-sm transition inline-block">
                Start Shopping &rarr;
            </a>
        </div>
    </div>
</div>

<!-- Alpine.js Cart Logic Component -->
<script>
function cartApp(initialSummary) {
    return {
        items: initialSummary?.items || [],
        subtotal: initialSummary?.subtotal || 0,
        discountAmount: initialSummary?.discount_amount || 0,
        totalAmount: initialSummary?.total_amount || 0,
        totalQuantity: initialSummary?.total_quantity || 0,
        appliedPromo: initialSummary?.applied_promo || null,
        promoInput: initialSummary?.applied_promo?.code || '',
        loading: false,
        updatingId: null,
        promoLoading: false,
        toast: {
            show: false,
            message: '',
            type: 'success'
        },
        deleteModal: {
            show: false,
            itemId: null,
            productName: ''
        },

        openDeleteModal(item) {
            this.deleteModal.itemId = item.id;
            this.deleteModal.productName = item.product_name;
            this.deleteModal.show = true;
        },

        closeDeleteModal() {
            if (this.loading) return;
            this.deleteModal.show = false;
            this.deleteModal.itemId = null;
            this.deleteModal.productName = '';
        },

        showToast(message, type = 'success') {
            this.toast.message = message;
            this.toast.type = type;
            this.toast.show = true;
            setTimeout(() => {
                this.toast.show = false;
            }, 3500);
        },

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        },

        syncSummary(summary) {
            this.items = summary.items || [];
            this.subtotal = summary.subtotal || 0;
            this.discountAmount = summary.discount_amount || 0;
            this.totalAmount = summary.total_amount || 0;
            this.totalQuantity = summary.total_quantity || 0;
            this.appliedPromo = summary.applied_promo || null;
            if (this.appliedPromo) {
                this.promoInput = this.appliedPromo.code;
            }

            // Sync cart badge in header & mobile bottom nav with jumlah produk
            const productCount = this.items.length;
            ['cart-badge', 'mobile-cart-badge'].forEach(id => {
                const badge = document.getElementById(id);
                if (badge) {
                    badge.textContent = productCount;
                    if (productCount > 0) {
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            });
        },

        async updateQuantity(itemId, newQty) {
            if (newQty < 1) return;
            this.loading = true;
            this.updatingId = itemId;

            try {
                const res = await fetch(`/cart/${itemId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                    body: JSON.stringify({ quantity: newQty })
                });

                const json = await res.json();
                if (json.success) {
                    this.syncSummary(json.data);
                    this.showToast(json.message || 'Cart updated', 'success');
                } else {
                    this.showToast(json.message || 'Failed to update quantity', 'error');
                }
            } catch (err) {
                this.showToast('Network error', 'error');
            } finally {
                this.loading = false;
                this.updatingId = null;
            }
        },

        async confirmRemoveItem() {
            const itemId = this.deleteModal.itemId;
            if (!itemId) return;

            this.loading = true;
            this.updatingId = itemId;

            try {
                const res = await fetch(`/cart/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    }
                });

                const json = await res.json();
                if (json.success) {
                    this.syncSummary(json.data);
                    this.deleteModal.show = false;
                    this.showToast(json.message || 'Produk dihapus dari keranjang', 'success');
                } else {
                    this.showToast(json.message || 'Gagal menghapus produk', 'error');
                }
            } catch (err) {
                this.showToast('Terjadi kesalahan jaringan', 'error');
            } finally {
                this.loading = false;
                this.updatingId = null;
                this.deleteModal.itemId = null;
                this.deleteModal.productName = '';
            }
        },

        async saveToWishlist(productId, itemId) {
            this.loading = true;
            this.updatingId = itemId;

            try {
                const res = await fetch('{{ route('wishlist.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                    body: JSON.stringify({ product_id: productId })
                });

                const json = await res.json();
                if (json.success) {
                    // Remove from cart quietly
                    const delRes = await fetch(`/cart/${itemId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken(),
                        }
                    });
                    const delJson = await delRes.json();
                    if (delJson.success) {
                        this.syncSummary(delJson.data);
                    }
                    this.showToast('Item saved to your Wishlist', 'success');
                } else {
                    this.showToast(json.message || 'Failed to save to Wishlist', 'error');
                }
            } catch (err) {
                this.showToast('Network error', 'error');
            } finally {
                this.loading = false;
                this.updatingId = null;
            }
        },

        async applyPromo() {
            if (!this.promoInput.trim()) return;
            this.promoLoading = true;

            try {
                const res = await fetch('/cart/promo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                    body: JSON.stringify({ promo_code: this.promoInput.trim() })
                });

                const json = await res.json();
                if (json.success) {
                    this.syncSummary(json.data);
                    this.showToast(json.message || 'Promo code applied!', 'success');
                } else {
                    this.showToast(json.message || 'Invalid promo code', 'error');
                }
            } catch (err) {
                this.showToast('Failed to apply promo code', 'error');
            } finally {
                this.promoLoading = false;
            }
        },

        async removePromo() {
            this.promoLoading = true;

            try {
                const res = await fetch('/cart/promo', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    }
                });

                const json = await res.json();
                if (json.success) {
                    this.syncSummary(json.data);
                    this.promoInput = '';
                    this.showToast(json.message || 'Promo code removed', 'success');
                } else {
                    this.showToast(json.message || 'Failed to remove promo code', 'error');
                }
            } catch (err) {
                this.showToast('Error processing request', 'error');
            } finally {
                this.promoLoading = false;
            }
        }
    };
}
</script>
@endsection
