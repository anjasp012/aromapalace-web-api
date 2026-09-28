@extends('layouts.app')

@section('title', 'Store Locations - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8" x-data="storeMapApp()">
    <!-- Breadcrumb -->
    <nav class="text-xs text-gray-500 flex items-center gap-2">
        <a href="{{ route('home') }}" class="hover:text-[#650506] transition">Home</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Boutique Stores &amp; Pickup</span>
    </nav>

    <!-- Header Banner -->
    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-gray-200 pb-6">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Store Locations &amp; Pickup Points</h1>
            <p class="text-xs text-gray-500 mt-1">Kunjungi butik fisik kami untuk konsultasi piramida aroma atau nikmati layanan Click &amp; Collect gratis.</p>
        </div>

        <!-- Filter Store (Select Option) & Geolocation on the Right -->
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" 
                    id="geoBtn" 
                    @click="findNearestStores()" 
                    :disabled="isLocating"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-stone-100 hover:bg-stone-200 text-gray-800 text-xs font-semibold transition cursor-pointer border border-gray-200/80 shadow-2xs disabled:opacity-60">
                <span x-show="!isLocating">📍</span>
                <span x-show="isLocating" class="animate-spin text-xs">⏳</span>
                <span x-text="isLocating ? 'Mendeteksi Lokasi...' : 'Cari Terdekat'"></span>
            </button>

            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-gray-500">Pilih Kota:</label>
                <select x-model="selectedCity" 
                        @change="applyFilter(true)"
                        class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs font-semibold text-gray-800 focus:outline-none focus:border-[#650506] shadow-2xs cursor-pointer">
                    <option value="">Semua Kota</option>
                    @foreach($cities as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
                <button type="button" 
                        x-show="selectedCity" 
                        @click="resetFilter()" 
                        class="text-xs text-[#650506] hover:underline font-semibold ml-1 cursor-pointer">
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Integrated Split Layout: Store Cards List (Left) & Sticky PostGIS Map (Right) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <!-- Store Cards Column (7 Cols on LG) -->
        <div class="lg:col-span-7 space-y-5">
            <div class="flex items-center justify-between px-1">
                <span class="text-xs text-gray-500 font-medium">
                    Menemukan <strong class="text-gray-900 font-bold" x-text="visibleCount"></strong> butik di lokasi ini
                </span>
                <span class="text-[11px] text-[#650506] font-semibold flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Click &amp; Collect Tersedia</span>
                </span>
            </div>

            @foreach($allStores as $store)
                <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs hover:shadow-xl hover:border-[#650506]/40 hover:-translate-y-0.5 transition-all duration-300 p-5 sm:p-6 flex flex-col sm:flex-row gap-5 items-start justify-between group"
                     id="store-card-{{ $store->id }}"
                     x-show="matchesCity('{{ $store->city }}')"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     @mouseenter="highlightStoreMarker({{ $store->id }})">
                    
                    <!-- Thumbnail & Visual -->
                    <div class="w-full sm:w-44 h-40 sm:h-36 rounded-xl overflow-hidden bg-stone-100 shrink-0 relative">
                        <img src="{{ $store->image_url ?? 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?auto=format&fit=crop&w=600&q=80' }}"
                             alt="{{ $store->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                        @if($store->is_pickup_available)
                            <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 bg-white/95 backdrop-blur-sm text-[#650506] text-[10px] font-bold uppercase tracking-wider rounded-full shadow-2xs border border-white/60">
                                Pickup
                            </span>
                        @endif
                    </div>

                    <!-- Store Info -->
                    <div class="flex-1 min-w-0 space-y-2.5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-widest text-[#650506] block mb-0.5">{{ $store->city }}</span>
                                <h3 class="font-bold text-gray-900 text-base sm:text-lg leading-snug group-hover:text-[#650506] transition-colors">{{ $store->name }}</h3>
                            </div>
                            <!-- Live Dynamic Distance Badge -->
                            <span x-show="storeDistances[{{ $store->id }}]" 
                                  class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-stone-50 border border-stone-200/80 text-[#650506] text-xs font-semibold rounded-full shrink-0 shadow-2xs">
                                <svg class="w-3.5 h-3.5 text-[#650506]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span x-text="storeDistances[{{ $store->id }}] + ' km'"></span>
                            </span>
                        </div>

                        <p class="text-xs text-gray-500 leading-relaxed line-clamp-2">
                            {{ $store->address }}
                        </p>

                        <div class="flex flex-wrap items-center gap-y-1.5 gap-x-5 text-xs text-gray-600 pt-2 border-t border-gray-100">
                            <span class="flex items-center gap-1.5 font-medium">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ $store->operating_hours ?? ($store->open_time . ' - ' . $store->close_time) }}</span>
                            </span>
                            @if($store->phone)
                            <span class="flex items-center gap-1.5 font-medium">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span class="font-mono text-gray-700">{{ $store->phone }}</span>
                            </span>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="pt-2 flex items-center gap-2.5">
                            @if($store->latitude && $store->longitude)
                            <button type="button"
                                    @click="focusStoreMap({{ $store->latitude }}, {{ $store->longitude }}, {{ $store->id }})"
                                    class="py-2 px-3.5 rounded-lg border border-gray-200 bg-stone-50 hover:bg-white hover:border-[#650506] text-[#650506] text-xs font-semibold transition cursor-pointer shadow-2xs flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-[#650506]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                                <span>Tampilkan di Peta</span>
                            </button>
                            @endif
                            <a href="https://maps.google.com/?q={{ urlencode($store->name . ' ' . $store->address . ' ' . $store->city) }}"
                               target="_blank" rel="noopener noreferrer"
                               class="py-2 px-3.5 rounded-lg bg-[#650506] hover:bg-[#4A070B] text-white text-xs font-semibold transition shadow-2xs flex items-center gap-1.5 group/btn">
                                <span>Petunjuk Arah</span>
                                <svg class="w-3.5 h-3.5 group-hover/btn:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Empty State when no stores match client filter -->
            <div x-show="visibleCount === 0" class="py-16 text-center max-w-md mx-auto space-y-3">
                <div class="w-16 h-16 bg-[#F4F2EE] text-[#650506] rounded-full flex items-center justify-center mx-auto text-2xl">
                    <svg class="w-8 h-8 text-[#650506]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <h3 class="text-base font-bold text-gray-900">Butik Tidak Ditemukan</h3>
                <p class="text-xs text-gray-500 leading-relaxed">
                    Tidak ada butik atau titik penjemputan yang sesuai dengan filter kota saat ini.
                </p>
                <div class="pt-2">
                    <button type="button" 
                            @click="resetFilter()" 
                            class="inline-block px-5 py-2.5 rounded-lg bg-[#650506] hover:bg-[#4A070B] text-white text-xs font-semibold transition shadow-2xs cursor-pointer">
                        Lihat Semua Butik &rarr;
                    </button>
                </div>
            </div>
        </div>

        <!-- Sticky Interactive Map Column (5 Cols on LG) -->
        <div class="lg:col-span-5 sticky top-28 space-y-3">
            <div class="bg-white rounded-2xl border border-gray-200/90 shadow-2xs overflow-hidden">
                <!-- Map Canvas -->
                <div id="store-map" class="w-full h-[560px] z-10 bg-stone-100"></div>
            </div>
            <p class="text-[11px] text-gray-400 text-center">Klik pin pada peta atau tombol "Tampilkan di Peta" untuk memfokuskan lokasi butik.</p>
        </div>
    </div>
</div>

<!-- Discount Products (Full Bleed Royal Maroon Horizontal Slider - Identik dengan Home & Articles) -->
@if(!empty($discountProducts) && $discountProducts->count() > 0)
<section class="w-full bg-gradient-to-br from-[#4A070B] via-[#52090F] to-[#360407] py-14 sm:py-16 text-white my-12 sm:my-14 shadow-xs"
         x-data="{
            scroll(dir) {
                const el = this.$refs.slider;
                if (!el) return;
                const card = el.querySelector('[data-deal-card]');
                const cardWidth = card ? card.offsetWidth + 16 : 240;
                
                if (dir === 1 && (el.scrollLeft + el.clientWidth >= el.scrollWidth - 10)) {
                    el.scrollTo({ left: 0, behavior: 'smooth' });
                    return;
                }
                if (dir === -1 && el.scrollLeft <= 5) {
                    el.scrollTo({ left: el.scrollWidth, behavior: 'smooth' });
                    return;
                }
                
                el.scrollBy({ left: dir * cardWidth, behavior: 'smooth' });
            }
         }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-7">
            <div>
                <h2 class="text-xl sm:text-2xl lg:text-3xl font-extrabold text-white tracking-tight flex items-center gap-2">
                    <span>Produk dengan Diskon</span>
                    <span class="text-xs bg-[#D4AF37] text-[#4A070B] px-2.5 py-0.5 rounded-full font-bold uppercase tracking-wider">Sale</span>
                </h2>
                <p class="text-xs sm:text-sm text-stone-300 mt-1">Penawaran harga terbaik dengan potongan harga spesial terbatas.</p>
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
                    class="absolute -left-3 sm:-left-5 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full border border-white/20 bg-black/40 hover:bg-white text-white hover:text-[#650506] flex items-center justify-center transition-all shadow-lg backdrop-blur-xs cursor-pointer hover:scale-105 opacity-90 sm:opacity-0 group-hover/dealslider:opacity-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
            </button>

            <!-- Slider Track -->
            <div x-ref="slider" 
                 class="flex items-stretch gap-4 overflow-x-auto scroll-smooth pb-4 pt-1 snap-x snap-mandatory [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach($discountProducts as $product)
                    <div data-deal-card
                         class="w-[calc((100%-16px)/2.2)] sm:w-[calc((100%-2*16px)/3)] md:w-[calc((100%-3*16px)/4)] lg:w-[calc((100%-5*16px)/5.5)] shrink-0 snap-start hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                        <x-product-card :product="$product" :price-stacked="true" :is-dark="true" />
                    </div>
                @endforeach
            </div>

            <!-- Floating Right Arrow -->
            <button type="button"
                    @click="scroll(1)"
                    aria-label="Scroll right"
                    class="absolute -right-3 sm:-right-5 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full border border-white/20 bg-black/40 hover:bg-white text-white hover:text-[#650506] flex items-center justify-center transition-all shadow-lg backdrop-blur-xs cursor-pointer hover:scale-105 opacity-90 sm:opacity-0 group-hover/dealslider:opacity-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
</section>
@endif

<!-- Leaflet CSS & JS for Interactive Map -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
    /* Custom Luxury Maroon Map Marker */
    .custom-maroon-pin {
        background-color: #650506;
        width: 32px;
        height: 32px;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        border: 2px solid #FFFFFF;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    .custom-maroon-pin span {
        transform: rotate(45deg);
        font-size: 14px;
        line-height: 1;
    }
</style>

<script>
    let mapInstance = null;
    let storeMarkers = {};

    function storeMapApp() {
        return {
            selectedCity: '{{ $city ?? request('city', '') }}',
            highlightStoreId: {{ request('store') ? (int)request('store') : 'null' }},
            userLat: {{ request('lat') ? (float)request('lat') : 'null' }},
            userLng: {{ request('lng') ? (float)request('lng') : 'null' }},
            allStores: @json($allStores),
            storeDistances: {},
            isLocating: false,

            init() {
                // If a store was requested, automatically set city to match that store
                if (this.highlightStoreId) {
                    const targetStore = this.allStores.find(s => s.id == this.highlightStoreId);
                    if (targetStore) {
                        this.selectedCity = targetStore.city;
                    }
                }

                // Pre-calculate distances if lat/lng are already present in query params
                if (this.userLat && this.userLng) {
                    this.calculateAllDistances(this.userLat, this.userLng);
                }

                // Initialize Leaflet map
                this.$nextTick(() => {
                    const mapElement = document.getElementById('store-map');
                    if (!mapElement) return;

                    mapInstance = L.map('store-map', {
                        scrollWheelZoom: false
                    }).setView([-6.2088, 106.8456], 11);

                    const cartoKey = '{{ config('services.carto.api_key') }}';
                    L.tileLayer(`https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png?key=${cartoKey}`, {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                        subdomains: 'abcd',
                        maxZoom: 20
                    }).addTo(mapInstance);

                    // Create markers for all stores
                    this.allStores.forEach(store => {
                        if (!store.latitude || !store.longitude) return;

                        const lat = parseFloat(store.latitude);
                        const lng = parseFloat(store.longitude);

                        const customIcon = L.divIcon({
                            className: 'custom-pin-wrapper',
                            html: '<div class="custom-maroon-pin"><span>🛍️</span></div>',
                            iconSize: [32, 32],
                            iconAnchor: [16, 32],
                            popupAnchor: [0, -32]
                        });

                        const popupHtml = `
                            <div class="p-1 space-y-1.5 font-sans" style="min-width: 180px;">
                                <span class="text-[9px] font-bold uppercase tracking-wider text-[#650506] block">${store.city}</span>
                                <h4 class="font-bold text-xs text-gray-900 leading-tight">${store.name}</h4>
                                <p class="text-[11px] text-gray-500 leading-relaxed">${store.address}</p>
                                <div class="pt-1 border-t border-gray-100 flex items-center justify-between text-[10px]">
                                    <span class="text-gray-400">🕒 ${store.operating_hours || '10:00 - 22:00'}</span>
                                    <a href="https://maps.google.com/?q=${encodeURIComponent(store.name + ' ' + store.address)}" target="_blank" class="font-bold text-[#650506] underline">Petunjuk &rarr;</a>
                                </div>
                            </div>
                        `;

                        const marker = L.marker([lat, lng], { icon: customIcon })
                            .bindPopup(popupHtml);

                        storeMarkers[store.id] = marker;
                    });

                    // Sync visible markers according to current selectedCity
                    this.updateMapMarkers();

                    // If a store was requested, automatically focus map and scroll to card
                    if (this.highlightStoreId) {
                        const targetStore = this.allStores.find(s => s.id == this.highlightStoreId);
                        if (targetStore && targetStore.latitude && targetStore.longitude) {
                            setTimeout(() => {
                                focusStoreMap(parseFloat(targetStore.latitude), parseFloat(targetStore.longitude), targetStore.id);
                                const card = document.getElementById('store-card-' + targetStore.id);
                                if (card) {
                                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                }
                            }, 350);
                        }
                    }
                });

                // Listen to browser Back/Forward navigation without full reload
                window.addEventListener('popstate', () => {
                    const params = new URLSearchParams(window.location.search);
                    this.selectedCity = params.get('city') || '';
                    this.highlightStoreId = params.get('store') ? parseInt(params.get('store')) : null;
                    this.updateMapMarkers();
                    if (this.highlightStoreId) {
                        const targetStore = this.allStores.find(s => s.id == this.highlightStoreId);
                        if (targetStore) {
                            focusStoreMap(parseFloat(targetStore.latitude), parseFloat(targetStore.longitude), targetStore.id);
                        }
                    }
                });
            },

            matchesCity(storeCity) {
                if (!this.selectedCity) return true;
                return storeCity.toLowerCase() === this.selectedCity.toLowerCase();
            },

            get visibleCount() {
                return this.allStores.filter(s => this.matchesCity(s.city)).length;
            },

            applyFilter(updateUrl = true) {
                if (updateUrl) {
                    const url = new URL(window.location.href);
                    if (this.selectedCity) {
                        url.searchParams.set('city', this.selectedCity);
                    } else {
                        url.searchParams.delete('city');
                    }
                    window.history.pushState({}, '', url.toString());
                }

                this.updateMapMarkers();
            },

            resetFilter() {
                this.selectedCity = '';
                this.applyFilter(true);
            },

            updateMapMarkers() {
                if (!mapInstance) return;

                const bounds = [];
                this.allStores.forEach(store => {
                    const marker = storeMarkers[store.id];
                    if (!marker) return;

                    if (this.matchesCity(store.city)) {
                        if (!mapInstance.hasLayer(marker)) {
                            marker.addTo(mapInstance);
                        }
                        bounds.push([parseFloat(store.latitude), parseFloat(store.longitude)]);
                    } else {
                        if (mapInstance.hasLayer(marker)) {
                            mapInstance.removeLayer(marker);
                        }
                    }
                });

                if (bounds.length > 0) {
                    mapInstance.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
                }
            },

            calculateAllDistances(userLat, userLng) {
                this.allStores.forEach(store => {
                    if (store.latitude && store.longitude) {
                        const distKm = this.getHaversineKm(userLat, userLng, parseFloat(store.latitude), parseFloat(store.longitude));
                        this.storeDistances[store.id] = distKm.toFixed(1);
                    }
                });
            },

            getHaversineKm(lat1, lon1, lat2, lon2) {
                const R = 6371; // km
                const dLat = (lat2 - lat1) * Math.PI / 180;
                const dLon = (lon2 - lon1) * Math.PI / 180;
                const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                          Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                          Math.sin(dLon / 2) * Math.sin(dLon / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return R * c;
            },

            findNearestStores() {
                if (!navigator.geolocation) {
                    alert('Layanan lokasi tidak didukung oleh browser Anda.');
                    return;
                }

                this.isLocating = true;
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        this.isLocating = false;
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        this.userLat = lat;
                        this.userLng = lng;

                        this.calculateAllDistances(lat, lng);

                        // Update URL without page reload
                        const url = new URL(window.location.href);
                        url.searchParams.set('lat', lat.toFixed(5));
                        url.searchParams.set('lng', lng.toFixed(5));
                        window.history.pushState({}, '', url.toString());

                        // Find nearest store and focus map on it
                        let nearestStore = null;
                        let minDist = Infinity;
                        this.allStores.forEach(s => {
                            const d = parseFloat(this.storeDistances[s.id] || Infinity);
                            if (d < minDist) {
                                minDist = d;
                                nearestStore = s;
                            }
                        });

                        if (nearestStore) {
                            focusStoreMap(parseFloat(nearestStore.latitude), parseFloat(nearestStore.longitude), nearestStore.id);
                        }
                    },
                    (error) => {
                        this.isLocating = false;
                        alert('Tidak dapat mendeteksi lokasi GPS Anda: ' + error.message);
                    }
                );
            }
        };
    }

    function focusStoreMap(lat, lng, storeId) {
        if (!mapInstance) return;
        mapInstance.setView([lat, lng], 15, { animate: true });
        
        // Open popup
        if (storeMarkers[storeId]) {
            storeMarkers[storeId].openPopup();
        }

        // Set highlight on Alpine component
        const appEl = document.querySelector('[x-data*="storeMapApp"]');
        if (appEl && window.Alpine) {
            const data = Alpine.$data(appEl);
            if (data) {
                data.highlightStoreId = storeId;
            }
        }

        // On mobile: scroll to map container
        if (window.innerWidth < 1024) {
            const mapContainer = document.getElementById('store-map');
            if (mapContainer) {
                mapContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    }

    function highlightStoreMarker(storeId) {
        if (!mapInstance || !storeMarkers[storeId]) return;
        const marker = storeMarkers[storeId];
        marker.openPopup();
    }
</script>
@endsection
