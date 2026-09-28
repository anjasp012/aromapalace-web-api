<!-- macOS Spotlight Search Modal Component -->
<div x-data="spotlightSearch()"
     @open-spotlight.window="openSpotlight()"
     @keydown.window.escape="closeSpotlight()"
     id="spotlight-search-modal"
     x-cloak>

    <!-- Backdrop Overlay -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-start justify-center p-3 sm:p-6 pt-16 sm:pt-20"
         @click.self="closeSpotlight()"
         style="display: none;">

        <!-- Spotlight Window Container -->
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 -translate-y-4"
             @click.stop
             class="w-full max-w-2xl bg-white/95 backdrop-blur-xl border border-gray-200/90 rounded-2xl shadow-2xl overflow-hidden flex flex-col transition-all max-h-[85vh]">

            <!-- Search Header Bar (Spotlight Style) -->
            <form @submit.prevent="submitSearch()" class="relative flex items-center px-4 py-3.5 border-b border-gray-100 shrink-0">
                <!-- Search Icon -->
                <span class="text-[#650506] shrink-0 mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>

                <!-- Input -->
                <input x-ref="searchInput"
                       x-model="query"
                       @input="onInput()"
                       type="text"
                       placeholder="Cari parfum, kategori, atau brand..."
                       autocomplete="off"
                       class="w-full bg-transparent text-base sm:text-lg text-gray-900 placeholder-gray-400 focus:outline-none font-medium">

                <!-- Loading Spinner -->
                <div x-show="isLoading" class="shrink-0 mr-2" style="display: none;">
                    <svg class="animate-spin h-4 w-4 text-[#650506]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Clear Query Button -->
                <button x-show="query.length > 0" 
                        @click="resetQuery()" 
                        type="button" 
                        class="p-1 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 mr-2 transition cursor-pointer"
                        title="Hapus teks"
                        style="display: none;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <!-- ESC Key Badge -->
                <button type="button" 
                        @click="closeSpotlight()" 
                        class="hidden sm:inline-flex items-center px-2 py-0.5 text-[11px] font-mono font-medium text-gray-400 bg-gray-100 hover:bg-gray-200 rounded border border-gray-200/80 transition cursor-pointer">
                    esc
                </button>
            </form>

            <!-- Category Filter Tabs (Visible when active query and has results) -->
            <div x-show="query.trim().length > 0 && hasResults" 
                 class="px-4 py-2 bg-stone-50/70 border-b border-gray-100 flex items-center gap-1.5 overflow-x-auto text-xs shrink-0" 
                 style="display: none;">
                <button type="button" 
                        @click="activeTab = 'all'" 
                        :class="activeTab === 'all' ? 'bg-[#650506] text-white shadow-2xs font-bold' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200 font-medium'"
                        class="px-3 py-1 rounded-lg transition shrink-0 cursor-pointer">
                    Semua (<span x-text="totalCount"></span>)
                </button>
                <button x-show="results.products.length > 0" 
                        type="button" 
                        @click="activeTab = 'products'" 
                        :class="activeTab === 'products' ? 'bg-[#650506] text-white shadow-2xs font-bold' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200 font-medium'"
                        class="px-3 py-1 rounded-lg transition shrink-0 cursor-pointer">
                    Produk (<span x-text="results.products.length"></span>)
                </button>
                <button x-show="results.categories && results.categories.length > 0" 
                        type="button" 
                        @click="activeTab = 'categories'" 
                        :class="activeTab === 'categories' ? 'bg-[#650506] text-white shadow-2xs font-bold' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200 font-medium'"
                        class="px-3 py-1 rounded-lg transition shrink-0 cursor-pointer">
                    Kategori (<span x-text="results.categories.length"></span>)
                </button>
                <button x-show="results.brands && results.brands.length > 0" 
                        type="button" 
                        @click="activeTab = 'brands'" 
                        :class="activeTab === 'brands' ? 'bg-[#650506] text-white shadow-2xs font-bold' : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200 font-medium'"
                        class="px-3 py-1 rounded-lg transition shrink-0 cursor-pointer">
                    Brand (<span x-text="results.brands.length"></span>)
                </button>
            </div>

            <!-- Scrollable Content Body -->
            <div class="overflow-y-auto divide-y divide-gray-100 flex-1">

                <!-- 1. LIVE SEARCH RESULTS (When query is active) -->
                <div x-show="query.trim().length > 0" style="display: none;">
                    
                    <!-- Has Results Container -->
                    <div x-show="hasResults" class="p-3 space-y-4">
                        
                        <!-- SECTION A: PRODUK FRAGRANCE -->
                        <div x-show="(activeTab === 'all' || activeTab === 'products') && results.products.length > 0" class="space-y-1.5">
                            <div class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-[#650506]">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                    <span>Produk Fragrance</span>
                                </span>
                                <span x-text="results.products.length + ' produk'"></span>
                            </div>

                            <div class="space-y-1">
                                <template x-for="item in results.products" :key="'prod-' + item.id">
                                    <a :href="item.url || ('/products/' + item.slug)" 
                                       @click="saveToHistory(item.name)"
                                       class="flex items-center gap-3.5 p-2 rounded-xl hover:bg-stone-50 transition group cursor-pointer border border-transparent hover:border-gray-200/80">
                                        <div class="w-12 h-12 rounded-lg bg-stone-100 border border-gray-200/70 overflow-hidden shrink-0">
                                            <img :src="item.primary_image || 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=150&q=80'" 
                                                 :alt="item.name" 
                                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#650506]" x-text="item.brand?.name || 'Aroma Palace'"></span>
                                                <span x-show="item.category?.name" class="text-[10px] text-gray-400">·</span>
                                                <span x-show="item.category?.name" class="text-[10px] text-gray-400 truncate" x-text="item.category?.name"></span>
                                            </div>
                                            <h4 class="text-xs sm:text-sm font-semibold text-gray-900 truncate group-hover:text-[#650506] transition" x-text="item.name"></h4>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-xs font-bold font-mono text-gray-800" x-text="formatRupiah(item.final_price || item.base_price)"></span>
                                                <span x-show="item.discount_percent > 0" class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.2 rounded" x-text="'-' + item.discount_percent + '%'"></span>
                                            </div>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-300 group-hover:text-[#650506] group-hover:translate-x-0.5 transition-all shrink-0 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <!-- SECTION B: KATEGORI WEWANGIAN (CATEGORIES) -->
                        <div x-show="(activeTab === 'all' || activeTab === 'categories') && results.categories && results.categories.length > 0" class="space-y-1.5 pt-2 border-t border-gray-100">
                            <div class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-stone-700">
                                    <svg class="w-3.5 h-3.5 text-[#650506]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <span>Kategori Wewangian</span>
                                </span>
                                <span x-text="results.categories.length + ' kategori'"></span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                <template x-for="cat in results.categories" :key="'cat-' + cat.id">
                                    <a :href="cat.url || ('/products?category=' + encodeURIComponent(cat.slug))" 
                                       @click="saveToHistory(cat.name)"
                                       class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-stone-50 transition group cursor-pointer border border-gray-100 hover:border-gray-200/80 bg-white shadow-2xs">
                                        <div class="w-9 h-9 rounded-lg bg-[#650506]/10 text-[#650506] border border-[#650506]/15 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-xs sm:text-sm font-semibold text-gray-900 truncate group-hover:text-[#650506] transition" x-text="cat.name"></h4>
                                            <span class="text-[10px] text-gray-400" x-text="(cat.products_count !== undefined ? cat.products_count + ' produk' : 'Lihat Produk')"></span>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-300 group-hover:text-[#650506] group-hover:translate-x-0.5 transition-all shrink-0 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <!-- SECTION C: BRANDS / KOLEKSI -->
                        <div x-show="(activeTab === 'all' || activeTab === 'brands') && results.brands && results.brands.length > 0" class="space-y-1.5 pt-2 border-t border-gray-100">
                            <div class="px-2 py-1 text-[11px] font-bold uppercase tracking-wider text-gray-400 flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-stone-700">
                                    <svg class="w-3.5 h-3.5 text-[#650506]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <span>Brand / Koleksi</span>
                                </span>
                                <span x-text="results.brands.length + ' brand'"></span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                <template x-for="brand in results.brands" :key="'brand-' + brand.id">
                                    <a :href="brand.url || ('/products?brand=' + encodeURIComponent(brand.slug))" 
                                       @click="saveToHistory(brand.name)"
                                       class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-stone-50 transition group cursor-pointer border border-gray-100 hover:border-gray-200/80 bg-white shadow-2xs">
                                        <div class="w-9 h-9 rounded-lg bg-stone-100 border border-gray-200/70 overflow-hidden shrink-0 flex items-center justify-center font-bold text-xs text-[#650506]">
                                            <template x-if="brand.logo_url">
                                                <img :src="brand.logo_url" :alt="brand.name" class="w-full h-full object-contain p-1">
                                            </template>
                                            <template x-if="!brand.logo_url">
                                                <span x-text="brand.name ? brand.name.substring(0, 2).toUpperCase() : 'AP'"></span>
                                            </template>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-xs sm:text-sm font-semibold text-gray-900 truncate group-hover:text-[#650506] transition" x-text="brand.name"></h4>
                                            <span class="text-[10px] text-gray-400" x-text="(brand.products_count !== undefined ? brand.products_count + ' produk' : 'Lihat Brand')"></span>
                                        </div>
                                        <svg class="w-4 h-4 text-gray-300 group-hover:text-[#650506] group-hover:translate-x-0.5 transition-all shrink-0 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </template>
                            </div>
                        </div>

                        <!-- View All Results link -->
                        <div class="pt-2 pb-1 px-1">
                            <button type="button" 
                                    @click="submitSearch()"
                                    class="w-full text-center py-2.5 px-4 bg-stone-50 hover:bg-[#650506] hover:text-white text-xs font-bold text-gray-700 rounded-xl transition border border-gray-200/80 cursor-pointer flex items-center justify-center gap-2">
                                <span>Lihat seluruh katalog produk untuk &ldquo;<span x-text="query"></span>&rdquo;</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    </div>

                    <!-- Empty State (No Products, Categories, or Brands) -->
                    <div x-show="!isLoading && !hasResults" class="p-8 text-center space-y-2">
                        <div class="w-10 h-10 rounded-full bg-stone-100 text-gray-400 flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <p class="text-xs sm:text-sm font-semibold text-gray-800">Tidak ada produk, kategori, atau brand yang cocok dengan &ldquo;<span x-text="query"></span>&rdquo;</p>
                        <p class="text-xs text-gray-500">Coba kata kunci lain atau tekan tombol di bawah untuk melihat katalog lengkap.</p>
                        <div class="pt-2">
                            <button type="button" 
                                    @click="submitSearch()" 
                                    class="text-xs font-bold text-[#650506] hover:underline cursor-pointer">
                                Cari di Katalog Produk &rarr;
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2. DEFAULT STATE: HISTORY ONLY (When query is empty) -->
                <div x-show="query.trim().length === 0" class="p-4 space-y-4">
                    
                    <!-- Search History Section -->
                    <div x-show="history.length > 0">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Riwayat Pencarian</span>
                            <button type="button" 
                                    @click="clearAllHistory()" 
                                    class="text-[11px] font-semibold text-gray-400 hover:text-rose-600 transition cursor-pointer">
                                Hapus Semua
                            </button>
                        </div>

                        <div class="flex flex-col gap-1">
                            <template x-for="(term, idx) in history" :key="'hist-' + idx">
                                <div class="flex items-center justify-between px-3 py-2 bg-stone-50/80 hover:bg-stone-100 rounded-xl group transition cursor-pointer"
                                     @click="selectQuery(term)">
                                    <span class="text-xs text-gray-800 group-hover:text-[#650506] font-medium truncate min-w-0" x-text="term"></span>
                                    <button type="button" 
                                            @click.stop="removeFromHistory(idx)" 
                                            class="p-1 text-gray-300 hover:text-rose-500 rounded transition cursor-pointer shrink-0 ml-2"
                                            title="Hapus dari riwayat">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Empty History Minimal Prompt -->
                    <div x-show="history.length === 0" class="py-12 text-center text-gray-400 text-xs sm:text-sm">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <p class="font-medium text-gray-600">Ketik untuk mencari</p>
                        <p class="text-[11px] text-gray-400 mt-0.5">Cari produk wewangian, kategori, atau brand pilihan</p>
                    </div>

                </div>
            </div>

            <!-- Spotlight Footer Bar (macOS Command Palette Style) -->
            <div class="px-4 py-2.5 bg-stone-50/90 border-t border-gray-100 flex items-center justify-between text-[11px] text-gray-500 shrink-0">
                <div class="flex items-center gap-1.5 font-medium">
                    <span class="w-2 h-2 rounded-full bg-[#650506]"></span>
                    <span>Aroma Palace Spotlight</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 text-[10px] font-mono bg-white border border-gray-200 rounded shadow-2xs text-gray-600">↵</kbd>
                        <span>Cari</span>
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <kbd class="px-1.5 py-0.5 text-[10px] font-mono bg-white border border-gray-200 rounded shadow-2xs text-gray-600">ESC</kbd>
                        <span>Tutup</span>
                    </span>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function spotlightSearch() {
        return {
            isOpen: false,
            query: '',
            activeTab: 'all',
            history: [],
            results: {
                products: [],
                categories: [],
                brands: []
            },
            isLoading: false,
            searchTimeout: null,

            get hasResults() {
                return (this.results.products.length + (this.results.categories?.length || 0) + (this.results.brands?.length || 0)) > 0;
            },

            get totalCount() {
                return this.results.products.length + (this.results.categories?.length || 0) + (this.results.brands?.length || 0);
            },

            init() {
                this.loadHistory();
            },

            openSpotlight() {
                this.isOpen = true;
                this.loadHistory();
                this.$nextTick(() => {
                    const input = this.$refs.searchInput;
                    if (input) {
                        input.focus();
                        input.select();
                    }
                });
            },

            closeSpotlight() {
                this.isOpen = false;
            },

            resetQuery() {
                this.query = '';
                this.activeTab = 'all';
                this.results = { products: [], categories: [], brands: [] };
                this.$refs.searchInput.focus();
            },

            loadHistory() {
                try {
                    const saved = localStorage.getItem('aromapalace_search_history');
                    this.history = saved ? JSON.parse(saved) : [];
                } catch (e) {
                    this.history = [];
                }
            },

            saveToHistory(term) {
                if (!term) return;
                const clean = term.trim();
                if (!clean) return;
                let list = this.history.filter(h => h.toLowerCase() !== clean.toLowerCase());
                list.unshift(clean);
                if (list.length > 8) list = list.slice(0, 8);
                this.history = list;
                try {
                    localStorage.setItem('aromapalace_search_history', JSON.stringify(list));
                } catch (e) {}
            },

            removeFromHistory(index) {
                this.history.splice(index, 1);
                try {
                    localStorage.setItem('aromapalace_search_history', JSON.stringify(this.history));
                } catch (e) {}
            },

            clearAllHistory() {
                this.history = [];
                try {
                    localStorage.removeItem('aromapalace_search_history');
                } catch (e) {}
            },

            selectQuery(term) {
                this.query = term;
                this.onInput();
                this.$nextTick(() => {
                    if (this.$refs.searchInput) this.$refs.searchInput.focus();
                });
            },

            submitSearch() {
                const clean = this.query.trim();
                if (!clean) return;
                this.saveToHistory(clean);
                this.closeSpotlight();
                window.location.href = "{{ route('products.index') }}?search=" + encodeURIComponent(clean);
            },

            onInput() {
                if (this.searchTimeout) clearTimeout(this.searchTimeout);

                const q = this.query.trim();
                if (!q) {
                    this.results = { products: [], categories: [], brands: [] };
                    this.isLoading = false;
                    return;
                }

                this.isLoading = true;
                this.searchTimeout = setTimeout(async () => {
                    try {
                        const res = await fetch(`/api/v1/search/suggestions?q=${encodeURIComponent(q)}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (res.ok) {
                            const json = await res.json();
                            const data = json.data || {};
                            this.results = {
                                products: data.products || [],
                                categories: data.categories || [],
                                brands: data.brands || [],
                            };
                            this.activeTab = 'all';
                        } else {
                            this.results = { products: [], categories: [], brands: [] };
                        }
                    } catch (e) {
                        this.results = { products: [], categories: [], brands: [] };
                    } finally {
                        this.isLoading = false;
                    }
                }, 200);
            },

            formatRupiah(amount) {
                if (!amount && amount !== 0) return 'Rp 0';
                return 'Rp ' + Math.round(amount).toLocaleString('id-ID');
            }
        };
    }

    // Register with window and Alpine
    window.spotlightSearch = spotlightSearch;
    if (window.Alpine) {
        window.Alpine.data('spotlightSearch', spotlightSearch);
    } else {
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('spotlightSearch', spotlightSearch);
        });
    }

    window.openSpotlight = function() {
        window.dispatchEvent(new CustomEvent('open-spotlight'));
    };
</script>
