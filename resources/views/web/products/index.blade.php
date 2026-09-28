@extends('layouts.app')

@section('title', 'Shop - All Products')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6" x-data="productFilterApp()">
    <!-- Breadcrumb -->
    <nav id="product-breadcrumb" class="text-[11px] sm:text-xs text-gray-500 flex items-center gap-1.5 sm:gap-2">
        <a href="{{ route('home') }}" class="hover:text-[#650506] transition">Home</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Shop</span>
        @if(request('category'))
            <span>/</span>
            <span class="text-gray-900 font-medium capitalize">{{ str_replace('-', ' ', request('category')) }}</span>
        @elseif(request('brand'))
            <span>/</span>
            <span class="text-gray-900 font-medium capitalize">{{ str_replace('-', ' ', request('brand')) }}</span>
        @elseif(request('search'))
            <span>/</span>
            <span class="text-gray-900 font-medium">Pencarian &ldquo;{{ request('search') }}&rdquo;</span>
        @endif
    </nav>

    <!-- Main Dynamic Catalog Container -->
    <div id="product-catalog-inner" 
         class="space-y-4 sm:space-y-6 transition-opacity duration-150"
         :class="isLoading ? 'opacity-50 pointer-events-none' : ''">

        <!-- Page Header & Sorting -->
        <div class="flex flex-wrap items-center justify-between gap-2.5 sm:gap-4 border-b border-gray-200/80 pb-3 sm:pb-5">
            <div>
                <span class="text-[10px] sm:text-xs uppercase tracking-widest text-gray-500 font-semibold block mb-0.5 sm:mb-1">Curated Fragrances</span>
                <h1 class="text-xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">
                    @if(request('category'))
                        {{ optional($categories->firstWhere('slug', request('category')))->name ?? 'Category Products' }}
                    @elseif(request('brand'))
                        {{ optional($brands->firstWhere('slug', request('brand')))->name ?? 'Brand Collection' }}
                    @elseif(request('search'))
                        Hasil Pencarian: &ldquo;{{ request('search') }}&rdquo;
                    @elseif(request('discount'))
                        Special Offers &amp; Sale
                    @else
                        All Products
                    @endif
                </h1>
            </div>

            <!-- Sorting Selector (Zero-Reload with Alpine) -->
            <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-1.5 sm:gap-2">
                @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                @if(request('brand'))<input type="hidden" name="brand" value="{{ request('brand') }}">@endif
                @if(request('search'))<input type="hidden" name="search" value="{{ request('search') }}">@endif
                @if(request('discount'))<input type="hidden" name="discount" value="{{ request('discount') }}">@endif

                <label class="text-[11px] sm:text-xs font-semibold text-gray-500">Urutkan:</label>
                <select name="sort" 
                        @change="onSortChange($event)" 
                        class="bg-white border border-gray-200 rounded-lg px-2.5 sm:px-3 py-1.5 sm:py-2 text-[11px] sm:text-xs font-semibold text-gray-800 focus:outline-none focus:border-[#650506] shadow-2xs cursor-pointer">
                    <option value="popular" {{ $sort === 'popular' ? 'selected' : '' }}>Paling Populer</option>
                    <option value="price_low" {{ $sort === 'price_low' ? 'selected' : '' }}>Harga: Rendah ke Tinggi</option>
                    <option value="price_high" {{ $sort === 'price_high' ? 'selected' : '' }}>Harga: Tinggi ke Rendah</option>
                    <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Rating Tertinggi</option>
                    <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Terbaru</option>
                </select>
            </form>
        </div>

        <!-- Active Filter Chips (if any filter is applied) -->
        @if(request('category') || request('brand') || request('discount') || request('search'))
        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 pt-0.5 sm:pt-1">
            <span class="text-[11px] sm:text-xs text-gray-400 font-medium mr-1">Filter aktif:</span>
            @if(request('category'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-stone-100 text-gray-800 text-[11px] sm:text-xs rounded-full font-medium">
                    <span>Kategori: {{ optional($categories->firstWhere('slug', request('category')))->name ?? request('category') }}</span>
                    <a href="{{ route('products.index', array_merge(request()->query(), ['category' => null])) }}" 
                       @click.prevent="navigate($el.href)"
                       class="text-gray-400 hover:text-rose-600">&times;</a>
                </span>
            @endif
            @if(request('brand'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-stone-100 text-gray-800 text-[11px] sm:text-xs rounded-full font-medium">
                    <span>Brand: {{ optional($brands->firstWhere('slug', request('brand')))->name ?? request('brand') }}</span>
                    <a href="{{ route('products.index', array_merge(request()->query(), ['brand' => null])) }}" 
                       @click.prevent="navigate($el.href)"
                       class="text-gray-400 hover:text-rose-600">&times;</a>
                </span>
            @endif
            @if(request('discount'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-50 text-rose-700 text-[11px] sm:text-xs rounded-full font-medium border border-rose-100">
                    <span>Sedang Diskon</span>
                    <a href="{{ route('products.index', array_merge(request()->query(), ['discount' => null])) }}" 
                       @click.prevent="navigate($el.href)"
                       class="text-rose-400 hover:text-rose-700">&times;</a>
                </span>
            @endif
            @if(request('search'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-amber-900 text-[11px] sm:text-xs rounded-full font-medium border border-amber-200">
                    <span>Pencarian: "{{ request('search') }}"</span>
                    <a href="{{ route('products.index', array_merge(request()->query(), ['search' => null])) }}" 
                       @click.prevent="navigate($el.href)"
                       class="text-amber-500 hover:text-rose-600">&times;</a>
                </span>
            @endif
            <a href="{{ route('products.index') }}" 
               @click.prevent="navigate($el.href)"
               class="text-[11px] sm:text-xs font-semibold text-[#650506] hover:underline ml-1 sm:ml-2">Reset Semua</a>
        </div>
        @endif

        <!-- Mobile Filter Toggle Button -->
        <div class="lg:hidden">
            <button type="button" 
                    @click="mobileFilterOpen = !mobileFilterOpen"
                    class="w-full flex items-center justify-between px-3.5 py-2 bg-stone-50 hover:bg-stone-100 border border-gray-200 rounded-lg text-xs font-semibold text-gray-800 transition shadow-2xs">
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-[#650506]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter Kategori &amp; Brand</span>
                    @if(request('category') || request('brand') || request('discount'))
                        <span class="w-2 h-2 rounded-full bg-[#650506]"></span>
                    @endif
                </div>
                <svg class="w-3.5 h-3.5 text-gray-400 transition-transform duration-200" :class="mobileFilterOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
        </div>

        <!-- Filter & Product Grid Container -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-3.5 sm:gap-6 lg:gap-8">
            <!-- Sidebar Filters -->
            <aside class="lg:col-span-1 space-y-3 sm:space-y-5" :class="mobileFilterOpen ? 'block' : 'hidden lg:block'">
                <!-- Categories Filter -->
                <div class="bg-white p-3.5 sm:p-5 rounded-xl border border-gray-200 shadow-2xs">
                    <h4 class="font-bold text-[11px] sm:text-xs uppercase tracking-wider text-gray-900 mb-2 sm:mb-3">Kategori</h4>
                    <div class="space-y-0.5 sm:space-y-1 text-[11px] sm:text-xs font-medium">
                        <a href="{{ route('products.index', array_merge(request()->query(), ['category' => null])) }}" 
                           @click.prevent="navigate($el.href)"
                           class="block px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-lg transition {{ !request('category') ? 'bg-[#650506] text-white font-semibold shadow-2xs' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
                            Semua Kategori
                        </a>
                        @foreach($categories as $cat)
                        <a href="{{ route('products.index', array_merge(request()->query(), ['category' => $cat->slug])) }}" 
                           @click.prevent="navigate($el.href)"
                           class="flex items-center justify-between px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-lg transition {{ request('category') === $cat->slug ? 'bg-[#650506] text-white font-semibold shadow-2xs' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
                            <span>{{ $cat->name }}</span>
                            <span class="{{ request('category') === $cat->slug ? 'text-white/80' : 'text-gray-400' }} text-[10px] sm:text-[11px] font-mono">({{ $cat->products_count }})</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- Brands Filter -->
                <div class="bg-white p-3.5 sm:p-5 rounded-xl border border-gray-200 shadow-2xs">
                    <h4 class="font-bold text-[11px] sm:text-xs uppercase tracking-wider text-gray-900 mb-2 sm:mb-3">Merek / Brand</h4>
                    <div class="space-y-0.5 sm:space-y-1 text-[11px] sm:text-xs font-medium">
                        <a href="{{ route('products.index', array_merge(request()->query(), ['brand' => null])) }}" 
                           @click.prevent="navigate($el.href)"
                           class="block px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-lg transition {{ !request('brand') ? 'bg-[#650506] text-white font-semibold shadow-2xs' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
                            Semua Brand
                        </a>
                        @foreach($brands as $brand)
                        <a href="{{ route('products.index', array_merge(request()->query(), ['brand' => $brand->slug])) }}" 
                           @click.prevent="navigate($el.href)"
                           class="flex items-center justify-between px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-lg transition {{ request('brand') === $brand->slug ? 'bg-[#650506] text-white font-semibold shadow-2xs' : 'text-gray-600 hover:bg-stone-50 hover:text-gray-900' }}">
                            <span>{{ $brand->name }}</span>
                            <span class="{{ request('brand') === $brand->slug ? 'text-white/80' : 'text-gray-400' }} text-[10px] sm:text-[11px] font-mono">({{ $brand->products_count }})</span>
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- Offers Filter -->
                <div class="bg-white p-3.5 sm:p-5 rounded-xl border border-gray-200 shadow-2xs">
                    <h4 class="font-bold text-[11px] sm:text-xs uppercase tracking-wider text-gray-900 mb-2 sm:mb-3">Penawaran</h4>
                    <label class="flex items-center gap-2 text-[11px] sm:text-xs font-medium text-gray-700 cursor-pointer">
                        <input type="checkbox" 
                               @change="onDiscountToggle($event)" 
                               {{ request('discount') ? 'checked' : '' }} 
                               class="rounded border-gray-300 text-[#650506] focus:ring-[#650506] cursor-pointer">
                        <span class="font-semibold text-gray-800">Sedang Diskon (Sale)</span>
                    </label>
                </div>
            </aside>

            <!-- Products Grid -->
            <div class="lg:col-span-4 space-y-4 sm:space-y-6">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-4 lg:gap-5">
                    @forelse($products as $product)
                        <x-product-card :product="$product" />
                    @empty
                    <div class="col-span-full py-12 sm:py-16 text-center max-w-md mx-auto space-y-2.5 sm:space-y-3">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 bg-[#F4F2EE] text-[#650506] rounded-full flex items-center justify-center mx-auto text-xl sm:text-2xl">
                            <svg class="w-7 h-7 sm:w-8 sm:h-8 text-[#650506]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900">Produk Tidak Ditemukan</h3>
                        <p class="text-[11px] sm:text-xs text-gray-500 leading-relaxed">
                            Tidak ada produk yang cocok dengan kombinasi filter atau kata kunci pencarian Anda.
                        </p>
                        <div class="pt-1 sm:pt-2">
                            <a href="{{ route('products.index') }}" 
                               @click.prevent="navigate($el.href)" 
                               class="bg-[#650506] hover:bg-[#4A070B] text-white font-semibold text-xs px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg shadow-2xs transition inline-block">
                                Reset Filter &rarr;
                            </a>
                        </div>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination with Zero-Reload Delegation -->
                <div class="pt-4 sm:pt-6" @click="handlePaginationClick($event)">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function productFilterApp() {
        return {
            isLoading: false,
            mobileFilterOpen: false,

            init() {
                // Support browser Back and Forward history buttons
                window.addEventListener('popstate', () => {
                    this.loadPage(window.location.href, false);
                });
            },

            navigate(url) {
                if (!url) return;
                this.loadPage(url, true);
            },

            onSortChange(event) {
                const form = event.target.closest('form');
                if (!form) return;
                const formData = new FormData(form);
                const params = new URLSearchParams(formData);
                const url = form.action + '?' + params.toString();
                this.loadPage(url, true);
            },

            onDiscountToggle(event) {
                const isChecked = event.target.checked;
                const url = new URL(window.location.href);
                if (isChecked) {
                    url.searchParams.set('discount', '1');
                } else {
                    url.searchParams.delete('discount');
                }
                url.searchParams.delete('page'); // reset page on filter change
                this.loadPage(url.toString(), true);
            },

            handlePaginationClick(event) {
                const link = event.target.closest('a');
                if (link && link.href) {
                    event.preventDefault();
                    this.navigate(link.href);
                    // Scroll to top of products catalog smoothly
                    const target = document.getElementById('product-catalog-inner');
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            },

            async loadPage(url, pushState = true) {
                this.isLoading = true;
                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    // Swap main catalog inner contents
                    const newCatalog = doc.getElementById('product-catalog-inner');
                    const target = document.getElementById('product-catalog-inner');
                    if (newCatalog && target) {
                        target.innerHTML = newCatalog.innerHTML;
                        if (window.Alpine) {
                            window.Alpine.initTree(target);
                        }
                    }

                    // Swap breadcrumbs
                    const newBreadcrumb = doc.getElementById('product-breadcrumb');
                    const breadcrumbTarget = document.getElementById('product-breadcrumb');
                    if (newBreadcrumb && breadcrumbTarget) {
                        breadcrumbTarget.innerHTML = newBreadcrumb.innerHTML;
                        if (window.Alpine) {
                            window.Alpine.initTree(breadcrumbTarget);
                        }
                    }

                    // Update page URL without reload
                    if (pushState) {
                        window.history.pushState({}, '', url);
                    }

                    // Update document title if present
                    if (doc.title) {
                        document.title = doc.title;
                    }
                } catch (err) {
                    console.error('Failed to load page dynamically:', err);
                    // Fallback to traditional browser navigation
                    window.location.href = url;
                } finally {
                    this.isLoading = false;
                }
            }
        };
    }
</script>
@endsection
