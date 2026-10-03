@extends('layouts.app')

@section('title', $article->title . ' - Aroma Palace')
@section('meta_description', Str::limit(strip_tags($article->summary ?? $article->content), 155))
@section('meta_keywords', ($article->topic->name ?? 'Lifestyle') . ', tips, stories, aroma palace')
@section('canonical', route('articles.show', $article->slug))
@section('og_type', 'article')
@section('og_image', $article->cover_image)

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
          "name": "Artikel",
          "item": "{{ route('articles.index') }}"
        },
        {
          "@@type": "ListItem",
          "position": 3,
          "name": "{{ $article->title }}",
          "item": "{{ route('articles.show', $article->slug) }}"
        }
      ]
    },
    {
      "@@type": "BlogPosting",
      "headline": "{{ $article->title }}",
      "image": "{{ $article->cover_image }}",
      "datePublished": "{{ $article->published_at ? $article->published_at->toIso8601String() : $article->created_at->toIso8601String() }}",
      "dateModified": "{{ $article->updated_at->toIso8601String() }}",
      "author": {
        "@@type": "Person",
        "name": "{{ $article->author_name ?? 'Aroma Palace' }}"
      },
      "publisher": {
        "@@type": "Organization",
        "name": "Aroma Palace",
        "logo": "https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=400&q=80"
      },
      "description": "{{ addslashes(Str::limit(strip_tags($article->summary ?? $article->content), 200)) }}"
    }
  ]
}
</script>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-gray-500">
        <a href="{{ route('home') }}" class="hover:text-[#650506] transition">Beranda</a>
        <span>/</span>
        <a href="{{ route('articles.index') }}" class="hover:text-[#650506] transition">Artikel</a>
        <span>/</span>
        <span class="text-gray-900 font-medium truncate max-w-xs sm:max-w-md">{{ $article->title }}</span>
    </nav>

    <!-- Editorial Article Content Box -->
    <div class="max-w-4xl mx-auto space-y-4 sm:space-y-6">
        <!-- 1. Featured Cover Image (Thumbnail) -->
        @if($article->cover_image)
            <div class="rounded-xl overflow-hidden shadow-2xs border border-gray-200/80 bg-gray-100 aspect-[16/10] sm:aspect-auto sm:h-[400px]">
                <img src="{{ $article->cover_image }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
            </div>
        @endif

        <!-- 2 & 3. Title then Category, Tanggal, Read -->
        <header class="space-y-2.5 sm:space-y-3.5 text-left">
            <h1 class="text-xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 leading-tight tracking-tight">
                {{ $article->title }}
            </h1>

            <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-[10px] sm:text-xs text-gray-400">
                @if($article->topic)
                    <span class="px-2.5 py-0.5 sm:px-3 sm:py-1 bg-stone-100 text-gray-800 font-semibold uppercase tracking-wider rounded-md">
                        {{ $article->topic->name }}
                    </span>
                @endif
                <span>
                    {{ $article->published_at ? $article->published_at->format('M d, Y') : $article->created_at->format('M d, Y') }}
                </span>
                <span class="text-gray-300">&bull;</span>
                <span class="text-gray-500 font-medium">
                    {{ $article->reading_time_minutes ?? 4 }} menit baca
                </span>
            </div>
        </header>

        <!-- 4. Deskripsi: Lead / Excerpt -->
        @if($article->summary)
            <div class="p-3.5 sm:p-5 rounded-xl bg-stone-50 border border-gray-200/90 text-gray-700 text-xs sm:text-base leading-relaxed italic">
                "{{ $article->summary }}"
            </div>
        @endif

        <!-- Main Content Body -->
        <div class="prose prose-stone max-w-none text-gray-700 leading-relaxed space-y-3 sm:space-y-4 text-xs sm:text-base">
            {!! nl2br(e($article->content)) !!}
        </div>
    </div>

    <!-- More Stories from Journal -->
    @if(!empty($moreArticles) && count($moreArticles) > 0)
        <div class="pt-6 sm:pt-10 border-t border-gray-200 space-y-3 sm:space-y-5">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[10px] sm:text-xs uppercase tracking-wider text-gray-400 font-semibold">Artikel</span>
                    <h3 class="text-base sm:text-xl font-bold text-gray-900 mt-0.5">Cerita Pilihan Lainnya</h3>
                </div>
                <a href="{{ route('articles.index') }}" class="text-xs sm:text-sm font-semibold text-[#650506] hover:text-[#4A070B] inline-flex items-center gap-1 group shrink-0 transition-colors">
                    <span>Lihat Semua</span>
                    <span class="transition-transform duration-200 group-hover:translate-x-1">&rarr;</span>
                </a>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 lg:gap-6">
                @foreach($moreArticles as $moreArt)
                    <article class="bg-white rounded-xl sm:rounded-2xl overflow-hidden border border-gray-200/90 shadow-2xs hover:shadow-xl hover:border-[#650506]/30 hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group">
                        <div>
                            <!-- Thumbnail Box with 16:10 Aspect Ratio -->
                            <div class="relative aspect-[16/10] overflow-hidden bg-stone-100">
                                <a href="{{ route('articles.show', $moreArt->slug) }}" class="block w-full h-full">
                                    <img src="{{ $moreArt->cover_image ?? 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?auto=format&fit=crop&w=600&q=80' }}"
                                         alt="{{ $moreArt->title }}"
                                         loading="lazy"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                                </a>
                                
                                <!-- Badges Overlay -->
                                <div class="absolute inset-x-1.5 top-1.5 sm:inset-x-3 sm:top-3 flex items-center justify-between pointer-events-none">
                                    @if($moreArt->topic)
                                        <span class="px-1.5 py-0.5 sm:px-2.5 sm:py-1 bg-white/95 backdrop-blur-sm text-[#650506] font-bold text-[8px] sm:text-[10px] uppercase tracking-wider rounded-full shadow-2xs border border-white/60 truncate max-w-[85px] sm:max-w-none">
                                            {{ $moreArt->topic->name }}
                                        </span>
                                    @else
                                        <span></span>
                                    @endif

                                    <span class="px-1.5 py-0.5 sm:px-2 sm:py-1 bg-black/60 backdrop-blur-sm text-white font-medium text-[8px] sm:text-[10px] rounded-full flex items-center gap-1 shadow-2xs shrink-0">
                                        <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>{{ $moreArt->reading_time_minutes ?? 3 }} mnt</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Article Content -->
                            <div class="p-2.5 sm:p-4 lg:p-5 space-y-1 sm:space-y-2">
                                <!-- Meta Line (Date) -->
                                <div class="flex items-center gap-1 sm:gap-1.5 text-[9px] sm:text-[11px] font-medium text-gray-400">
                                    <svg class="w-2.5 h-2.5 sm:w-3.5 sm:h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>{{ $moreArt->published_at ? $moreArt->published_at->format('d M Y') : $moreArt->created_at->format('d M Y') }}</span>
                                </div>

                                <!-- Article Title -->
                                <h4 class="text-xs sm:text-base font-bold text-gray-900 group-hover:text-[#650506] transition-colors line-clamp-2 leading-snug tracking-tight">
                                    <a href="{{ route('articles.show', $moreArt->slug) }}">{{ $moreArt->title }}</a>
                                </h4>

                                <!-- Article Excerpt -->
                                <p class="text-[11px] sm:text-xs text-gray-500 line-clamp-2 leading-relaxed hidden sm:block">
                                    {{ $moreArt->summary }}
                                </p>
                            </div>
                        </div>

                        <!-- Article Card Footer -->
                        <div class="px-2.5 sm:px-4 lg:px-5 pb-2.5 sm:pb-4 pt-2 sm:pt-3 border-t border-gray-100 flex items-center justify-between gap-1.5 sm:gap-2 mt-auto">
                            <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                                <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-full bg-[#650506]/10 text-[#650506] flex items-center justify-center font-bold text-[8px] sm:text-[10px] shrink-0">
                                    {{ strtoupper(substr($moreArt->author_name ?? 'A', 0, 1)) }}
                                </div>
                                <span class="text-[9px] sm:text-xs text-gray-600 font-medium truncate">
                                    {{ $moreArt->author_name ?? 'Aroma Palace' }}
                                </span>
                            </div>

                            <a href="{{ route('articles.show', $moreArt->slug) }}" 
                               class="inline-flex items-center gap-0.5 sm:gap-1 text-[9px] sm:text-xs font-semibold text-[#650506] hover:text-[#4A070B] shrink-0 transition-colors">
                                <span>Baca</span>
                                <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    @endif

</div>

<!-- Discount Products (Full Bleed Royal Maroon Horizontal Slider - Identik dengan Home) -->
@if(!empty($discountProducts) && $discountProducts->count() > 0)
<section class="w-full bg-gradient-to-br from-[#4A070B] via-[#52090F] to-[#360407] py-8 sm:py-14 text-white my-6 sm:my-12 shadow-xs"
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

<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pb-4 sm:pb-6">
    <!-- Back to Articles Footer -->
    <div class="flex justify-between items-center gap-2">
        <a href="{{ route('articles.index') }}" class="inline-flex items-center gap-1 sm:gap-1.5 text-[11px] sm:text-xs font-semibold text-gray-600 hover:text-[#650506] transition shrink-0">
            &larr; Kembali ke Artikel
        </a>
        <a href="{{ route('products.index') }}" class="px-3.5 sm:px-5 py-2 sm:py-2.5 rounded-lg bg-[#650506] hover:bg-[#4A070B] text-white text-[11px] sm:text-xs font-semibold transition shadow-2xs shrink-0">
            Belanja Produk &rarr;
        </a>
    </div>
</div>
@endsection
