@extends('layouts.app')

@section('title', 'Pengiriman - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6"
     x-data="checkoutApp(@js($preview), @js($addresses), @js($stores))"
     x-cloak>

    <!-- Breadcrumb -->
    <nav class="text-[11px] sm:text-xs text-gray-500 flex items-center gap-1.5 sm:gap-2">
        <a href="{{ route('home') }}" class="hover:text-[#650506] transition">Beranda</a>
        <span>/</span>
        <a href="{{ route('cart.index') }}" class="hover:text-[#650506] transition">Keranjang</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Pengiriman</span>
    </nav>

    <!-- Page Header & Delivery Mode Selector -->
    <div class="flex flex-wrap items-center justify-between gap-2.5 sm:gap-4">
        <h1 class="text-lg sm:text-2xl font-bold text-gray-900 tracking-tight">Pengiriman</h1>

        <!-- Mode Toggle (Kirim ke Alamat vs Ambil di Butik) -->
        <div class="inline-flex bg-gray-100 p-0.5 rounded-lg text-xs font-semibold">
            <button type="button"
                    @click="fulfillmentType = 'home_delivery'; recalculate()"
                    class="px-3 py-1 rounded-md transition cursor-pointer select-none"
                    :class="fulfillmentType === 'home_delivery' ? 'bg-[#650506] text-white' : 'text-gray-600 hover:text-gray-900'">
                🚚 Kirim ke Alamat
            </button>
            <button type="button"
                    @click="fulfillmentType = 'store_pickup'; recalculate()"
                    class="px-3 py-1 rounded-md transition cursor-pointer select-none"
                    :class="fulfillmentType === 'store_pickup' ? 'bg-[#650506] text-white' : 'text-gray-600 hover:text-gray-900'">
                🏬 Ambil di Butik
            </button>
        </div>
    </div>

    <form method="POST" action="{{ route('checkout.process') }}" @submit="submitOrder($event)">
        @csrf
        <input type="hidden" name="fulfillment_type" :value="fulfillmentType">
        <input type="hidden" name="address_id" :value="fulfillmentType === 'home_delivery' ? selectedAddressId : ''">
        <input type="hidden" name="store_id" :value="fulfillmentType === 'store_pickup' ? selectedStoreId : ''">
        <input type="hidden" name="shipping_courier" :value="selectedCourier">
        <input type="hidden" name="shipping_service" :value="selectedService">
        <input type="hidden" name="payment_method" :value="selectedPaymentMethod">
        <input type="hidden" name="notes" :value="notes">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8 items-start">
            
            <!-- LEFT COLUMN (Cards: Alamat Pengiriman + Card Toko / Produk & Pengiriman) -->
            <div class="lg:col-span-2 space-y-4">
                
                <!-- CARD 1: Alamat Pengiriman (Home Delivery) -->
                <div x-show="fulfillmentType === 'home_delivery'" class="bg-white rounded-lg p-4 sm:p-6 border border-gray-200 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm sm:text-base font-bold text-gray-900">Alamat Pengiriman</h3>
                        <button type="button"
                                @click="openAddressModal = true"
                                class="inline-flex items-center gap-1 text-xs sm:text-sm font-semibold text-[#650506] hover:text-[#4A070B] transition cursor-pointer select-none group">
                            <span>Ubah Alamat</span>
                            <svg class="w-3.5 h-3.5 text-[#650506] group-hover:text-[#4A070B] group-hover:translate-x-0.5 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                    <template x-if="activeAddress">
                        <div class="text-xs space-y-1 pt-0.5">
                            <div class="flex items-center gap-1.5 font-bold text-gray-900">
                                <span class="text-gray-400">📍</span>
                                <span x-text="activeAddress.label || 'Rumah'"></span>
                                <template x-if="activeAddress.is_primary">
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold bg-blue-100 text-blue-700 rounded-sm leading-none ml-1">
                                        Utama
                                    </span>
                                </template>
                            </div>
                            <div class="font-bold text-gray-900">
                                <span x-text="activeAddress.recipient_name"></span>
                                <span class="text-gray-400 font-normal"> · </span>
                                <span x-text="activeAddress.phone_number"></span>
                            </div>
                            <div class="text-gray-600 leading-relaxed" x-text="activeAddress.full_address"></div>
                            <div class="text-gray-500" x-text="activeAddress.city + (activeAddress.postal_code ? ', ' + activeAddress.postal_code : '')"></div>
                        </div>
                    </template>

                    <template x-if="!activeAddress">
                        <div class="text-xs text-gray-500 py-2 flex items-center justify-between">
                            <span>Belum ada alamat pengiriman tersimpan.</span>
                            <button type="button" @click="openAddressModal = true" class="font-bold text-[#650506] hover:underline cursor-pointer">
                                + Tambah Alamat
                            </button>
                        </div>
                    </template>
                </div>

                <!-- CARD 1 (Alternative): Butik Pengambilan (Store Pickup) -->
                <div x-show="fulfillmentType === 'store_pickup'" class="bg-white rounded-lg p-4 sm:p-6 border border-gray-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm sm:text-base font-bold text-gray-900">Lokasi Butik Pengambilan</h3>
                        <span class="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">Bebas Ongkir</span>
                    </div>

                    <div class="space-y-2">
                        <template x-for="st in stores" :key="st.id">
                            <label class="border rounded-xl p-3 cursor-pointer transition flex items-start gap-3 select-none"
                                   :class="selectedStoreId == st.id ? 'border-[#650506] bg-rose-50/30' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="store_radio" :value="st.id" x-model="selectedStoreId" class="text-[#650506] focus:ring-[#650506] mt-1">
                                <div class="text-xs">
                                    <h4 class="font-bold text-gray-900 text-sm" x-text="st.name"></h4>
                                    <p class="text-gray-600 mt-0.5" x-text="st.address + ', ' + st.city"></p>
                                    <p class="text-[11px] text-gray-400 mt-0.5" x-text="'Jam Operasional: ' + (st.operating_hours || '10:00 - 22:00')"></p>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- CARD 2: Produk, Pilih Pengiriman & Tambah Catatan -->
                <div class="bg-white rounded-lg p-4 sm:p-6 border border-gray-200 space-y-4">
                    <!-- Items List -->
                    <div class="space-y-3.5 divide-y divide-gray-100">
                        <template x-for="item in items" :key="item.id || (item.product_id + '_' + (item.variant_id || '0'))">
                            <div class="pt-3 first:pt-0 flex items-start sm:items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl overflow-hidden shrink-0 border border-gray-100 bg-[#F4F2EE]">
                                        <img :src="item.product_image || 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=160&q=80'"
                                             :alt="item.product_name"
                                             class="w-full h-full object-cover">
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h5 class="font-bold text-gray-900 text-xs sm:text-sm line-clamp-1 sm:line-clamp-2" x-text="item.product_name"></h5>
                                        <p class="text-gray-400 text-[11px] mt-0.5" x-text="(item.variant_name ? 'Varian: ' + item.variant_name : 'Standar') + ' · 1 Produk (' + (item.weight || 250) + ' g)'"></p>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="font-bold font-mono text-xs sm:text-sm text-gray-900 block" x-text="$money(item.unit_price || item.price || 0) + ' × ' + item.quantity"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Pilih Pengiriman (If Home Delivery) -->
                    <div x-show="fulfillmentType === 'home_delivery'" class="border-t border-gray-100 pt-4 space-y-2">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm sm:text-base font-bold text-gray-900">Pilih Pengiriman</h3>
                            <span class="text-[11px] text-gray-400">Klik untuk ubah kurir</span>
                        </div>

                        <!-- Courier Select Bar (Opens Popup Modal on Click) -->
                        <button type="button"
                                @click="openShippingModal = true"
                                class="w-full border rounded-xl p-3 sm:p-3.5 flex items-center justify-between text-left transition bg-white cursor-pointer hover:border-gray-400 select-none border-gray-200">
                            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                <template x-if="selectedCourierObj">
                                    <div class="flex items-center gap-2 flex-wrap min-w-0">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-50 text-[#650506] border border-rose-200 shrink-0"
                                              x-text="selectedCourierObj.courier"></span>
                                        <span class="font-bold text-xs sm:text-sm text-gray-900 font-mono" x-text="$money(selectedCourierObj.cost)"></span>
                                        <span class="text-gray-300 text-xs">·</span>
                                        <span class="text-xs text-gray-700 font-medium truncate" x-text="selectedCourierObj.service_name || (selectedCourierObj.courier + ' ' + selectedCourierObj.service)"></span>
                                        <span class="text-gray-300 text-xs hidden sm:inline">|</span>
                                        <span class="text-[11px] text-gray-500" x-text="'Estimasi tiba ' + selectedCourierObj.etd"></span>
                                    </div>
                                </template>
                                <template x-if="!selectedCourierObj">
                                    <span class="text-xs text-gray-400">Pilih kurir & layanan pengiriman...</span>
                                </template>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Tambah Catatan (Tokopedia Style: Tambah Catatan 0/100) -->
                    <div class="border-t border-gray-100 pt-4 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="text-sm sm:text-base font-bold text-gray-900">Tambah Catatan</label>
                            <span class="text-[11px] text-gray-400 font-mono" x-text="notes.length + '/100'"></span>
                        </div>
                        <input type="text"
                               name="notes_input"
                               x-model="notes"
                               maxlength="100"
                               placeholder="Berikan catatan untuk pesanan ini..."
                               class="w-full text-xs px-3.5 py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506] focus:ring-1 focus:ring-[#650506] transition bg-white placeholder:text-gray-400">
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN (Kotak Kanan: Pilih Metode Pembayaran, Ringkasan Belanja, Tombol Beli dan Bayar) -->
            <div class="bg-white rounded-lg p-4 sm:p-6 border border-gray-200 space-y-4 h-fit lg:sticky lg:top-28">
                
                <!-- 1. Pilih Metode Pembayaran -->
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm sm:text-base font-bold text-gray-900">Pilih Metode Pembayaran</h3>
                        <button type="button"
                                @click="openPaymentModal = true"
                                class="inline-flex items-center gap-1 text-xs sm:text-sm font-semibold text-[#650506] hover:text-[#4A070B] transition cursor-pointer select-none group">
                            <span>Lihat Lainnya</span>
                            <svg class="w-3.5 h-3.5 text-[#650506] group-hover:text-[#4A070B] group-hover:translate-x-0.5 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Stacked Payment Method Cards (Preview Top 3 / Selected) -->
                    <div class="space-y-2">
                        <template x-for="pm in displayedPaymentMethods" :key="pm.code">
                            <label class="border rounded-xl px-3 py-2.5 cursor-pointer transition flex items-center justify-between gap-2.5 select-none"
                                   :class="selectedPaymentMethod === pm.code ? 'border-[#650506] bg-rose-50/30' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50/50 bg-white'">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <input type="radio" name="payment_method_radio" :value="pm.code" x-model="selectedPaymentMethod" class="sr-only">
                                    
                                    <!-- Bank / Provider Badge -->
                                    <template x-if="pm.code === 'bca_va'">
                                        <span class="px-1.5 py-0.5 rounded bg-blue-50 text-[#0060AF] font-black text-[11px] tracking-tight shrink-0 border border-blue-100">BCA</span>
                                    </template>
                                    <template x-if="pm.code === 'mandiri_va'">
                                        <span class="px-1.5 py-0.5 rounded bg-amber-50 text-[#003876] font-black text-[11px] tracking-tight shrink-0 border border-amber-100">MANDIRI</span>
                                    </template>
                                    <template x-if="pm.code === 'bni_va'">
                                        <span class="px-1.5 py-0.5 rounded bg-orange-50 text-[#E55300] font-black text-[11px] tracking-tight shrink-0 border border-orange-100">BNI</span>
                                    </template>
                                    <template x-if="pm.code === 'bri_va'">
                                        <span class="px-1.5 py-0.5 rounded bg-blue-50 text-[#00529C] font-black text-[11px] tracking-tight shrink-0 border border-blue-100">BRI</span>
                                    </template>
                                    <template x-if="pm.code === 'permata_va'">
                                        <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-[#008144] font-black text-[11px] tracking-tight shrink-0 border border-emerald-100">PERMATA</span>
                                    </template>
                                    <template x-if="pm.code === 'qris'">
                                        <span class="px-1.5 py-0.5 rounded bg-rose-50 text-[#650506] font-black text-[11px] tracking-tight shrink-0 border border-rose-100">QRIS</span>
                                    </template>
                                    <template x-if="pm.code === 'cod'">
                                        <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold text-[11px] shrink-0 border border-emerald-100">COD</span>
                                    </template>

                                    <!-- Method Name -->
                                    <div class="min-w-0">
                                        <span class="font-bold text-xs text-gray-900 block truncate" x-text="pm.name"></span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <span class="text-[11px] text-gray-500 font-medium">Bebas Biaya</span>
                                    
                                    <!-- Custom Radio Checkmark (Consistent #650506) -->
                                    <div class="w-4 h-4 rounded-full border flex items-center justify-center transition shrink-0"
                                         :class="selectedPaymentMethod === pm.code ? 'border-[#650506] bg-[#650506] text-white' : 'border-gray-300'">
                                        <svg x-show="selectedPaymentMethod === pm.code" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- 2. Ringkasan Belanja -->
                <div class="border-t border-gray-100 pt-3.5 space-y-2.5">
                    <h3 class="text-sm sm:text-base font-bold text-gray-900">Ringkasan Belanja</h3>

                    <div class="space-y-2 text-xs text-gray-600">
                        <div class="flex justify-between items-center">
                            <span>Subtotal (<span x-text="items.length"></span> barang)</span>
                            <span class="font-bold text-gray-900 font-mono text-xs sm:text-sm" x-text="$money(subtotal)"></span>
                        </div>

                        <div class="flex justify-between items-center">
                            <span>Total Ongkos Kirim</span>
                            <span class="font-bold text-gray-900 font-mono" x-text="fulfillmentType === 'home_delivery' ? $money(shippingCost) : 'Rp0'"></span>
                        </div>

                        <template x-if="discountAmount > 0">
                            <div class="flex justify-between items-center text-emerald-700 font-semibold">
                                <span>Diskon Promo</span>
                                <span class="font-mono" x-text="'- ' + $money(discountAmount)"></span>
                            </div>
                        </template>
                    </div>

                    <div class="border-t border-gray-200 pt-3 flex justify-between items-baseline">
                        <span class="text-xs sm:text-sm font-bold text-gray-900">Total Belanja</span>
                        <span class="text-xl sm:text-2xl font-extrabold text-[#650506] font-mono" x-text="$money(totalAmount)"></span>
                    </div>
                </div>

                <!-- CTA Button (Consistent Royal Maroon #650506 like Cart page) -->
                <div>
                    <button type="submit"
                            :disabled="submitting || (fulfillmentType === 'home_delivery' && !selectedAddressId)"
                            class="w-full bg-[#650506] hover:bg-[#4A070B] text-white font-medium py-3 sm:py-3.5 px-4 rounded-lg transition text-xs sm:text-sm flex items-center justify-center gap-2 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                        <span x-show="submitting" class="animate-spin text-xs">⏳</span>
                        <span x-text="submitting ? 'Menghubungkan ke Pembayaran...' : ('Beli (' + items.length + ') dan Bayar')"></span>
                    </button>
                </div>

                <!-- 5. Back Link -->
                <div class="pt-1 text-center text-xs">
                    <a href="{{ route('cart.index') }}" class="group font-semibold text-gray-500 hover:text-black transition-colors inline-flex items-center gap-1.5 select-none">
                        <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Kembali ke Keranjang</span>
                    </a>
                </div>
            </div>
        </div>
    </form>

    <!-- 1. Address Selection Modal (Popup Ubah Alamat) -->
    <div x-show="openAddressModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         @keydown.escape.window="openAddressModal = false"
         style="display: none;">
        
        <div x-show="openAddressModal"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             @click.outside="openAddressModal = false"
             class="bg-white rounded-xl max-w-lg w-full p-4 sm:p-6 shadow-2xl border border-gray-100 space-y-4 max-h-[90vh] flex flex-col">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-1 h-4 bg-[#650506] rounded-full shrink-0"></span>
                    <h3 class="font-bold text-sm sm:text-base text-gray-900">Pilih Alamat Pengiriman</h3>
                </div>
                <button type="button" @click="openAddressModal = false" class="text-gray-400 hover:text-gray-700 text-lg leading-none cursor-pointer">&times;</button>
            </div>

            <!-- Address List Body -->
            <div class="overflow-y-auto space-y-3 flex-1 pr-1 [-ms-overflow-style:none] [scrollbar-width:thin]">
                <template x-for="addr in addresses" :key="addr.id">
                    <div class="border rounded-xl p-3.5 transition cursor-pointer relative"
                         :class="selectedAddressId === addr.id ? 'border-[#650506] bg-rose-50/30' : 'border-gray-200 hover:border-gray-300'"
                         @click="selectedAddressId = addr.id; openAddressModal = false; recalculate()">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-xs sm:text-sm text-gray-900" x-text="addr.label || 'Alamat'"></span>
                                <template x-if="addr.is_primary">
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold bg-blue-100 text-blue-700 rounded-sm">
                                        Utama
                                    </span>
                                </template>
                            </div>
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0"
                                 :class="selectedAddressId === addr.id ? 'border-[#650506] bg-[#650506] text-white' : 'border-gray-300'">
                                <svg x-show="selectedAddressId === addr.id" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>

                        <p class="text-xs font-semibold text-gray-800" x-text="addr.recipient_name + ' (' + addr.phone_number + ')'"></p>
                        <p class="text-xs text-gray-600 mt-1 leading-relaxed" x-text="addr.full_address + ', ' + addr.city + (addr.postal_code ? ' ' + addr.postal_code : '')"></p>
                    </div>
                </template>

                <div class="pt-2">
                    <a href="{{ route('account.addresses') }}" class="w-full py-2.5 border-2 border-dashed border-gray-300 hover:border-[#650506] hover:bg-stone-50 text-xs font-semibold text-gray-700 hover:text-[#650506] rounded-xl transition flex items-center justify-center gap-1.5">
                        <span>+ Kelola / Tambah Alamat Baru</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Shipping Courier Selection Modal (Popup Pilih Pengiriman) -->
    <div x-show="openShippingModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         @keydown.escape.window="openShippingModal = false"
         style="display: none;">
        
        <div x-show="openShippingModal"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             @click.outside="openShippingModal = false"
             class="bg-white rounded-xl max-w-lg w-full p-4 sm:p-6 shadow-2xl border border-gray-100 space-y-4 max-h-[85vh] flex flex-col">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-1 h-4 bg-[#650506] rounded-full shrink-0"></span>
                    <h3 class="font-bold text-sm sm:text-base text-gray-900">Pilih Pengiriman</h3>
                </div>
                <button type="button" @click="openShippingModal = false" class="text-gray-400 hover:text-gray-700 text-lg leading-none cursor-pointer">&times;</button>
            </div>

            <!-- Loading State -->
            <div x-show="loadingRates" class="py-8 text-center text-xs text-gray-500 flex items-center justify-center gap-2">
                <span class="animate-spin text-sm">⏳</span>
                <span>Memperbarui tarif kurir KiriminAja...</span>
            </div>

            <!-- Courier Options Body -->
            <div x-show="!loadingRates" class="overflow-y-auto space-y-2.5 flex-1 pr-1 [-ms-overflow-style:none] [scrollbar-width:thin]">
                <template x-if="availableCouriers.length === 0">
                    <div class="text-center py-8 text-xs text-gray-400">
                        Belum ada opsi kurir tersedia untuk alamat tujuan ini.
                    </div>
                </template>

                <template x-for="(courier, idx) in availableCouriers" :key="idx">
                    <div @click="selectCourier(courier.courier, courier.service, courier.cost, courier.etd); openShippingModal = false;"
                         class="border rounded-xl p-3.5 transition cursor-pointer flex items-center justify-between gap-3 select-none"
                         :class="(selectedCourier === courier.courier && selectedService === courier.service)
                             ? 'border-[#650506] bg-rose-50/30'
                             : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50/50 bg-white'">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center shrink-0"
                                 :class="(selectedCourier === courier.courier && selectedService === courier.service) ? 'border-[#650506] bg-[#650506] text-white' : 'border-gray-300'">
                                <svg x-show="selectedCourier === courier.courier && selectedService === courier.service" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-50 text-[#650506] border border-rose-200 shrink-0" x-text="courier.courier"></span>
                                    <span class="font-bold text-xs sm:text-sm text-gray-900" x-text="courier.service_name || (courier.courier + ' ' + courier.service)"></span>
                                </div>
                                <span class="text-[11px] text-gray-500 block mt-0.5" x-text="'Estimasi tiba ' + courier.etd"></span>
                            </div>
                        </div>
                        <span class="font-mono font-bold text-xs sm:text-sm text-gray-900 shrink-0" x-text="$money(courier.cost)"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- 3. Payment Method Selection Modal (Popup Pilih Metode Pembayaran) -->
    <div x-show="openPaymentModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
         @keydown.escape.window="openPaymentModal = false"
         style="display: none;">
        
        <div x-show="openPaymentModal"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-2"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-2"
             @click.outside="openPaymentModal = false"
             class="bg-white rounded-xl max-w-lg w-full p-4 sm:p-6 shadow-2xl border border-gray-100 space-y-4 max-h-[85vh] flex flex-col">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-1 h-4 bg-[#650506] rounded-full shrink-0"></span>
                    <h3 class="font-bold text-sm sm:text-base text-gray-900">Pilih Metode Pembayaran</h3>
                </div>
                <button type="button" @click="openPaymentModal = false" class="text-gray-400 hover:text-gray-700 text-lg leading-none cursor-pointer">&times;</button>
            </div>

            <!-- Payment Methods List Body -->
            <div class="overflow-y-auto space-y-2.5 flex-1 pr-1 [-ms-overflow-style:none] [scrollbar-width:thin]">
                <template x-for="pm in paymentMethods" :key="pm.code">
                    <div @click="selectedPaymentMethod = pm.code; openPaymentModal = false;"
                         class="border rounded-xl px-3.5 py-3 cursor-pointer transition flex items-center justify-between gap-3 select-none"
                         :class="selectedPaymentMethod === pm.code ? 'border-[#650506] bg-rose-50/20 ring-1 ring-[#650506]/30' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50/50 bg-white'">
                        <div class="flex items-center gap-3 min-w-0">
                            <!-- Bank / Provider Badge -->
                            <template x-if="pm.code === 'bca_va'">
                                <span class="px-2 py-1 rounded bg-blue-50 text-[#0060AF] font-black text-xs tracking-tight shrink-0 border border-blue-100">BCA</span>
                            </template>
                            <template x-if="pm.code === 'mandiri_va'">
                                <span class="px-2 py-1 rounded bg-amber-50 text-[#003876] font-black text-xs tracking-tight shrink-0 border border-amber-100">MANDIRI</span>
                            </template>
                            <template x-if="pm.code === 'bni_va'">
                                <span class="px-2 py-1 rounded bg-orange-50 text-[#E55300] font-black text-xs tracking-tight shrink-0 border border-orange-100">BNI</span>
                            </template>
                            <template x-if="pm.code === 'bri_va'">
                                <span class="px-2 py-1 rounded bg-blue-50 text-[#00529C] font-black text-xs tracking-tight shrink-0 border border-blue-100">BRI</span>
                            </template>
                            <template x-if="pm.code === 'permata_va'">
                                <span class="px-2 py-1 rounded bg-emerald-50 text-[#008144] font-black text-xs tracking-tight shrink-0 border border-emerald-100">PERMATA</span>
                            </template>
                            <template x-if="pm.code === 'qris'">
                                <span class="px-2 py-1 rounded bg-rose-50 text-[#650506] font-black text-xs tracking-tight shrink-0 border border-rose-100">QRIS</span>
                            </template>
                            <template x-if="pm.code === 'cod'">
                                <span class="px-2 py-1 rounded bg-emerald-50 text-emerald-700 font-bold text-xs shrink-0 border border-emerald-100">COD</span>
                            </template>

                            <!-- Method Name -->
                            <div class="min-w-0">
                                <span class="font-bold text-xs sm:text-sm text-gray-900 block truncate" x-text="pm.name"></span>
                                <span class="text-[11px] text-gray-400" x-text="pm.provider ? 'Konfirmasi otomatis via ' + pm.provider : 'Bayar saat kurir tiba'"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5 shrink-0">
                            <span class="text-xs text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full">Bebas Biaya</span>
                            
                            <!-- Custom Radio Checkmark (Consistent #650506) -->
                            <div class="w-4 h-4 rounded-full border flex items-center justify-center transition shrink-0"
                                 :class="selectedPaymentMethod === pm.code ? 'border-[#650506] bg-[#650506] text-white' : 'border-gray-300'">
                                <svg x-show="selectedPaymentMethod === pm.code" class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function checkoutApp(initialPreview, initialAddresses, initialStores) {
    return {
        items: initialPreview?.items || [],
        addresses: initialAddresses || [],
        stores: initialStores || [],
        fulfillmentType: initialPreview?.fulfillment_type || 'home_delivery',
        selectedAddressId: initialPreview?.selected_address?.id || (initialAddresses[0]?.id || null),
        selectedStoreId: initialPreview?.selected_store?.id || (initialStores[0]?.id || null),
        availableCouriers: initialPreview?.available_couriers || [],
        selectedCourier: initialPreview?.shipping?.courier || 'JNE',
        selectedService: initialPreview?.shipping?.service || 'REG',
        shippingCost: initialPreview?.shipping_cost || 0,
        subtotal: initialPreview?.subtotal || 0,
        discountAmount: initialPreview?.discount_amount || 0,
        totalAmount: initialPreview?.total_amount || 0,
        paymentMethods: initialPreview?.available_payment_methods || [],
        selectedPaymentMethod: initialPreview?.available_payment_methods?.[0]?.code || 'qris',
        notes: '',
        openAddressModal: false,
        openShippingModal: false,
        openPaymentModal: false,
        loadingRates: false,
        submitting: false,

        get activeAddress() {
            if (!this.addresses || this.addresses.length === 0) return null;
            return this.addresses.find(a => a.id == this.selectedAddressId) || this.addresses[0];
        },

        get activeStore() {
            if (!this.stores || this.stores.length === 0) return null;
            return this.stores.find(s => s.id == this.selectedStoreId) || this.stores[0];
        },

        get selectedCourierObj() {
            if (!this.availableCouriers || this.availableCouriers.length === 0) return null;
            return this.availableCouriers.find(c => c.courier === this.selectedCourier && c.service === this.selectedService) || this.availableCouriers[0];
        },

        get displayedPaymentMethods() {
            // Display top 3, but always ensure selected method is among the displayed cards
            const top3 = this.paymentMethods.slice(0, 3);
            if (this.selectedPaymentMethod && !top3.some(p => p.code === this.selectedPaymentMethod)) {
                const selected = this.paymentMethods.find(p => p.code === this.selectedPaymentMethod);
                if (selected) {
                    return [selected, ...top3.slice(0, 2)];
                }
            }
            return top3;
        },

        selectCourier(courier, service, cost, etd) {
            this.selectedCourier = courier;
            this.selectedService = service;
            this.shippingCost = cost;
            this.recalculate();
        },

        async recalculate() {
            this.loadingRates = true;
            try {
                const res = await fetch('/checkout/calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        fulfillment_type: this.fulfillmentType,
                        address_id: this.selectedAddressId,
                        store_id: this.selectedStoreId,
                        shipping_courier: this.selectedCourier,
                        shipping_service: this.selectedService,
                    })
                });

                const json = await res.json();
                if (json.success && json.data) {
                    const data = json.data;
                    this.subtotal = data.subtotal;
                    this.shippingCost = data.shipping_cost;
                    this.discountAmount = data.discount_amount;
                    this.totalAmount = data.total_amount;
                    if (data.available_couriers && data.available_couriers.length > 0) {
                        this.availableCouriers = data.available_couriers;
                    }
                }
            } catch (e) {
                console.error('Recalculate error:', e);
            } finally {
                this.loadingRates = false;
            }
        },

        submitOrder(event) {
            this.submitting = true;
        }
    };
}
</script>
@endsection
