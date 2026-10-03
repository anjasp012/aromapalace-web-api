@extends('layouts.app')

@section('title', 'Artikel - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-4 sm:pt-8 pb-4 sm:pb-6 space-y-4 sm:space-y-6" x-data="articleCatalogApp()">
    <!-- Breadcrumb -->
    <nav id="articles-breadcrumb" class="text-[11px] sm:text-xs text-gray-500 flex items-center gap-1.5 sm:gap-2">
        <a href="{{ route('home') }}" class="hover:text-[#650506] transition">Beranda</a>
        <span>/</span>
        <span class="text-gray-900 font-medium">Artikel</span>
    </nav>

    <!-- Main Dynamic Articles Container -->
    <div id="articles-catalog-inner" 
         class="space-y-4 sm:space-y-6 transition-opacity duration-150"
         :class="isLoading ? 'opacity-50 pointer-events-none' : ''">

        <!-- Header Banner -->
        <div class="flex flex-wrap items-end justify-between gap-2.5 sm:gap-4 border-b border-gray-200/80 pb-3 sm:pb-5">
            <div>
                <h1 class="text-xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">Artikel</h1>
            </div>
        </div>

        <!-- Main Articles Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 lg:gap-6">
            @forelse($articles as $art)
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
                            <div class="absolute inset-x-1.5 top-1.5 sm:inset-x-3 sm:top-3 flex items-center justify-between pointer-events-none">
                                @if($art->topic)
                                    <span class="px-1.5 py-0.5 sm:px-2.5 sm:py-1 bg-white/95 backdrop-blur-sm text-[#650506] font-bold text-[8px] sm:text-[10px] uppercase tracking-wider rounded-full shadow-2xs border border-white/60 truncate max-w-[85px] sm:max-w-none">
                                        {{ $art->topic->name }}
                                    </span>
                                @else
                                    <span></span>
                                @endif

                                <span class="px-1.5 py-0.5 sm:px-2 sm:py-1 bg-black/60 backdrop-blur-sm text-white font-medium text-[8px] sm:text-[10px] rounded-full flex items-center gap-1 shadow-2xs shrink-0">
                                    <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>{{ $art->reading_time_minutes ?? 3 }} mnt</span>
                                </span>
                            </div>
                        </div>

                        <!-- Article Content -->
                        <div class="p-2.5 sm:p-4 lg:p-5 space-y-1 sm:space-y-2">
                            <!-- Meta Line (Date) -->
                            <div class="flex items-center gap-1 sm:gap-1.5 text-[9px] sm:text-[11px] font-medium text-gray-400">
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
                    <div class="px-2.5 sm:px-4 lg:px-5 pb-2.5 sm:pb-4 pt-2 sm:pt-3 border-t border-gray-100 flex items-center justify-between gap-1.5 sm:gap-2 mt-auto">
                        <!-- Author with Initial Avatar -->
                        <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                            <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-[#650506]/10 text-[#650506] flex items-center justify-center font-bold text-[8px] sm:text-[10px] shrink-0">
                                {{ strtoupper(substr($art->author_name ?? 'A', 0, 1)) }}
                            </div>
                            <span class="text-[9px] sm:text-xs text-gray-600 font-medium truncate">
                                {{ $art->author_name ?? 'Aroma Palace' }}
                            </span>
                        </div>

                        <!-- Read Link -->
                        <a href="{{ route('articles.show', $art->slug) }}" 
                           class="inline-flex items-center gap-0.5 sm:gap-1 text-[9px] sm:text-xs font-semibold text-[#650506] hover:text-[#4A070B] shrink-0 transition-colors">
                            <span>Baca</span>
                            <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full py-12 sm:py-16 text-center max-w-md mx-auto space-y-3">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-[#F4F2EE] text-[#650506] rounded-full flex items-center justify-center mx-auto text-xl sm:text-2xl">
                        <svg class="w-6 h-6 sm:w-8 sm:h-8 text-[#650506]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </div>
                    <h3 class="text-sm sm:text-base font-bold text-gray-900">Belum Ada Artikel</h3>
                    <p class="text-[11px] sm:text-xs text-gray-500 leading-relaxed">
                        Belum ada artikel atau cerita untuk topik ini. Silakan periksa kembali nanti.
                    </p>
                    <div class="pt-1 sm:pt-2">
                        <a href="{{ route('articles.index') }}" 
                           @click.prevent="navigate($el.href)" 
                           class="bg-[#650506] hover:bg-[#4A070B] text-white font-semibold text-[11px] sm:text-xs px-4 sm:px-5 py-2 sm:py-2.5 rounded-lg shadow-2xs transition inline-block cursor-pointer">
                            Lihat Semua Artikel &rarr;
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination with Zero-Reload Delegation -->
        @if($articles->hasPages())
        <div class="pt-4 sm:pt-6 border-t border-gray-200" @click="handlePaginationClick($event)">
            {{ $articles->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Discount Products (Full Bleed Royal Maroon Horizontal Slider - Identik dengan Home & Articles Show) -->
@if(!empty($discountProducts) && $discountProducts->count() > 0)
<section class="w-full bg-gradient-to-br from-[#4A070B] via-[#52090F] to-[#360407] py-8 sm:py-14 text-white my-6 sm:my-12 shadow-xs"
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
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h2 class="text-lg sm:text-2xl font-normal text-white tracking-tight">Produk dengan <span class="font-bold">Diskon</span></h2>
            
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
                         class="w-[calc((100%-12px)/2.2)] sm:w-[calc((100%-2*16px)/3)] md:w-[calc((100%-3*16px)/4)] lg:w-[calc((100%-5*16px)/5.5)] shrink-0 snap-start hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
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
@endif

<script>
    function articleCatalogApp() {
        return {
            isLoading: false,

            init() {
                window.addEventListener('popstate', () => {
                    this.loadPage(window.location.href, false);
                });
            },

            filterTopic(slug) {
                const url = new URL('{{ route('articles.index') }}', window.location.origin);
                if (slug) {
                    url.searchParams.set('topic', slug);
                }
                this.loadPage(url.toString(), true);
            },

            navigate(url) {
                if (!url) return;
                this.loadPage(url, true);
            },

            handlePaginationClick(event) {
                const link = event.target.closest('a');
                if (link && link.href) {
                    event.preventDefault();
                    this.navigate(link.href);
                    const target = document.getElementById('articles-catalog-inner');
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

                    // Swap articles container
                    const newCatalog = doc.getElementById('articles-catalog-inner');
                    const target = document.getElementById('articles-catalog-inner');
                    if (newCatalog && target) {
                        target.innerHTML = newCatalog.innerHTML;
                        if (window.Alpine) {
                            window.Alpine.initTree(target);
                        }
                    }

                    // Swap breadcrumb
                    const newBreadcrumb = doc.getElementById('articles-breadcrumb');
                    const breadcrumbTarget = document.getElementById('articles-breadcrumb');
                    if (newBreadcrumb && breadcrumbTarget) {
                        breadcrumbTarget.innerHTML = newBreadcrumb.innerHTML;
                        if (window.Alpine) {
                            window.Alpine.initTree(breadcrumbTarget);
                        }
                    }

                    // Push URL
                    if (pushState) {
                        window.history.pushState({}, '', url);
                    }

                    // Document title
                    if (doc.title) {
                        document.title = doc.title;
                    }
                } catch (err) {
                    console.error('Failed to load articles dynamically:', err);
                    window.location.href = url;
                } finally {
                    this.isLoading = false;
                }
            }
        };
    }
</script>
@endsection
