@php
    $bannerImageUrl = 'https://images.unsplash.com/photo-1588405748880-12d1d2a59f75?auto=format&fit=crop&w=800&q=80';
    $promoTargetUrl = route('products.index', ['discount' => 1]);
@endphp

<!-- Simple Image Promotional Popup Modal -->
<div x-data="{
        isOpen: false,
        dontShowToday: false,
        init() {
            // Clean up old legacy keys that may have blocked earlier tests
            sessionStorage.removeItem('ap_promo_seen');
            localStorage.removeItem('ap_promo_dismissed_date');

            // Expose globally so user can reopen from header announcement or promo links
            window.openPromoModal = () => {
                this.isOpen = true;
                document.body.classList.add('overflow-hidden');
            };
            window.addEventListener('open-promo-modal', () => window.openPromoModal());

            // Check if user explicitly checked 'Jangan tampilkan lagi hari ini'
            const today = new Date().toDateString();
            const dismissedToday = localStorage.getItem('ap_promo_dismissed_today');

            if (dismissedToday === today) {
                return; // User explicitly asked not to show again today
            }

            // Show popup smoothly after page renders (600ms delay)
            setTimeout(() => {
                this.isOpen = true;
                document.body.classList.add('overflow-hidden');
            }, 600);
        },
        closeModal() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
            if (this.dontShowToday) {
                localStorage.setItem('ap_promo_dismissed_today', new Date().toDateString());
            }
        }
    }"
    @keydown.window.escape="if(isOpen) closeModal()"
    id="welcome-promo-modal"
    x-cloak>

    <!-- Backdrop Overlay -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/75 backdrop-blur-md flex items-center justify-center p-4 sm:p-6"
         @click.self="closeModal()"
         style="display: none;">

        <!-- Modal Wrapper (Centered, Simple Image Flyer) -->
        <div x-show="isOpen"
             x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-400"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             @click.stop
             class="relative w-full max-w-[340px] sm:max-w-[400px] md:max-w-[440px] flex flex-col items-center">

            <!-- Floating Close Button (Top-Right) -->
            <button @click="closeModal()"
                    type="button"
                    class="absolute -top-3.5 -right-3.5 sm:-top-4 sm:-right-4 z-30 w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white text-gray-800 hover:text-black hover:bg-gray-100 shadow-xl border border-gray-200 flex items-center justify-center transition-all duration-200 hover:scale-110 focus:outline-none cursor-pointer"
                    title="Tutup (ESC)"
                    aria-label="Tutup Iklan">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <!-- Clickable Promotional Banner Image with Ad Typography Overlay -->
            <a href="{{ $promoTargetUrl }}" 
               @click="closeModal()"
               title="Lihat Promo Spesial Aroma Palace"
               class="block w-full relative overflow-hidden rounded-2xl sm:rounded-3xl shadow-[0_25px_60px_-15px_rgba(0,0,0,0.6)] border border-white/25 group transition-transform duration-300 hover:scale-[1.015]">
                
                <!-- Main Promo Poster Image -->
                <div class="relative w-full aspect-[3/4] sm:aspect-[4/5] bg-stone-900 overflow-hidden">
                    <img src="https://images.unsplash.com/photo-1588405748880-12d1d2a59f75?auto=format&fit=crop&w=800&q=80" 
                         alt="Iklan Promo Aroma Palace" 
                         class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700 ease-out select-none">

                    <!-- Dark Gradient Overlays for Maximum Contrast & Readability -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/95 via-black/40 to-black/60 pointer-events-none"></div>

                    <!-- Top Ad Header (Badge & Brand) -->
                    <div class="absolute top-0 inset-x-0 p-3.5 sm:p-5 flex items-center justify-between pointer-events-none">
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-black/60 backdrop-blur-md border border-white/15 text-white shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                            <span class="text-[9px] sm:text-[10px] font-extrabold tracking-widest uppercase text-amber-300">IKLAN PROMO</span>
                        </div>

                        <div class="flex items-center gap-1.5 bg-white/90 backdrop-blur-md px-2.5 py-1 rounded-full shadow-xs">
                            <img src="{{ asset('images/logo.png') }}" alt="Aroma Palace" class="w-3.5 h-3.5 object-contain rounded-full">
                            <span class="text-[9px] sm:text-[10px] font-black tracking-wider text-[#4A070B] uppercase">Aroma Palace</span>
                        </div>
                    </div>

                    <!-- Bottom Ad Typography & Call-To-Action ("Tulisan Iklan") -->
                    <div class="absolute bottom-0 inset-x-0 p-4 sm:p-6 text-center flex flex-col items-center justify-end pointer-events-none">
                        <!-- Sub-heading Tagline -->
                        <div class="text-[10px] sm:text-[11px] font-bold uppercase tracking-[0.2em] text-amber-300 drop-shadow-sm flex items-center gap-1.5 mb-1 sm:mb-1.5">
                            <span>✦</span>
                            <span>DUBAI LUXURY FRAGRANCE</span>
                            <span>✦</span>
                        </div>

                        <!-- Main Ad Title -->
                        <h3 class="text-xl sm:text-2xl md:text-3xl font-black text-white tracking-tight uppercase leading-none drop-shadow-md">
                            Special Summer Sale
                        </h3>

                        <!-- Promo Value Pill -->
                        <div class="mt-2 sm:mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gradient-to-r from-[#650506] via-[#8B1E21] to-[#4A070B] border border-amber-400/30 text-white shadow-md">
                            <span class="text-xs sm:text-sm font-black text-amber-300 tracking-wide">DISKON HINGGA 40%</span>
                            <span class="text-[9px] sm:text-[10px] text-stone-200 border-l border-white/20 pl-1.5">+ Gratis Ongkir</span>
                        </div>

                        <!-- Short Ad Caption -->
                        <p class="text-[11px] sm:text-xs text-stone-200 mt-2 line-clamp-2 max-w-[280px] sm:max-w-xs leading-relaxed drop-shadow-sm">
                            Koleksi parfum artisanal Dubai terlaris minggu ini dengan aroma mewah tahan lama.
                        </p>

                        <!-- Interactive Looking Voucher Ticket Tag -->
                        <div class="mt-2.5 sm:mt-3 px-3 py-1 rounded-lg bg-black/60 backdrop-blur-sm border border-dashed border-amber-300/40 text-stone-200 text-[10px] sm:text-[11px] font-mono flex items-center gap-1.5">
                            <span class="text-amber-300">🎟️</span>
                            <span>Gunakan Kode:</span>
                            <span class="text-amber-300 font-bold tracking-wider">AROMA15</span>
                        </div>

                        <!-- Action Button Overlay -->
                        <div class="mt-3 sm:mt-4 w-full">
                            <span class="inline-flex w-full items-center justify-center gap-2 py-2 sm:py-2.5 px-4 rounded-xl bg-white group-hover:bg-amber-50 text-[#650506] font-extrabold text-xs sm:text-sm tracking-wide shadow-xl transition-all duration-300 group-hover:scale-102">
                                <span>Klaim Diskon &amp; Belanja Sekarang</span>
                                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                            </span>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Subtle Bottom Controls (Jangan Tampilkan Lagi Hari Ini) -->
            <div class="mt-3 flex items-center justify-between w-full px-2 text-[11px] sm:text-xs text-white/80 select-none">
                <label class="flex items-center gap-1.5 cursor-pointer hover:text-white transition">
                    <input type="checkbox" 
                           x-model="dontShowToday" 
                           class="rounded border-white/40 bg-black/40 text-[#650506] focus:ring-[#650506]/30">
                    <span>Jangan tampilkan lagi hari ini</span>
                </label>

                <button type="button" 
                        @click="closeModal()" 
                        class="hover:text-white transition cursor-pointer underline underline-offset-2">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>
