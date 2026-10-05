<!-- Luxury Membership Hero Banner (Harmonized across all Account Pages) -->
<div class="relative overflow-hidden rounded-xl sm:rounded-2xl bg-gradient-to-br from-[#4A070B] via-[#650506] to-[#2B0304] text-white p-4 sm:p-6 lg:p-8 shadow-xl border border-[#650506]/40">
    <!-- Ambient Decorative Glows -->
    <div class="absolute -right-16 -top-16 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-[#800708]/30 rounded-full blur-2xl pointer-events-none"></div>

    <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4 sm:gap-6">
        <!-- User Info & Avatar -->
        <div class="flex items-center gap-3 sm:gap-5">
            @if(!empty($user->avatar))
                <img src="{{ $user->avatar }}" class="w-14 h-14 sm:w-18 sm:h-18 lg:w-20 lg:h-20 rounded-xl sm:rounded-2xl object-cover ring-2 sm:ring-4 ring-white/10 shadow-lg shrink-0" alt="{{ $user->name }}">
            @else
                <div class="w-14 h-14 sm:w-18 sm:h-18 lg:w-20 lg:h-20 rounded-xl sm:rounded-2xl bg-white/10 border border-white/20 backdrop-blur-sm flex items-center justify-center text-white font-serif font-bold text-xl sm:text-2xl lg:text-3xl shadow-inner ring-2 sm:ring-4 ring-white/10 shrink-0">
                    {{ strtoupper(substr($user->name ?? 'User', 0, 1)) }}
                </div>
            @endif

            <div class="space-y-0.5 sm:space-y-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-[10px] sm:text-xs uppercase tracking-widest text-amber-200/90 font-semibold font-mono">
                        ID: #AP-{{ str_pad($user->id ?? 1, 5, '0', STR_PAD_LEFT) }}
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider border shadow-xs bg-amber-400/20 text-amber-200 border-amber-400/30">
                        ★ Member Aroma Palace
                    </span>
                </div>

                <h2 class="text-base sm:text-xl lg:text-2xl font-serif font-bold text-white tracking-tight truncate">{{ $user->name ?? 'Customer' }}</h2>
                <p class="text-[11px] sm:text-xs text-white/70 flex flex-wrap items-center gap-1 sm:gap-2">
                    <span class="truncate max-w-[180px] sm:max-w-none">{{ $user->email ?? '' }}</span>
                    <span>&bull;</span>
                    <span>Bergabung {{ $membershipStatus['joined_at'] ?? 'Aktif' }}</span>
                </p>
            </div>
        </div>

        <!-- Points & Reward Action Card -->
        <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-3 sm:p-5 flex items-center justify-between sm:justify-end gap-3 sm:gap-6 shadow-sm shrink-0">
            <div>
                <span class="text-[10px] sm:text-[11px] font-bold text-amber-200 uppercase tracking-wider block">Poin Keanggotaan</span>
                <div class="flex items-baseline gap-1 mt-0.5">
                    <span class="text-xl sm:text-3xl font-extrabold font-mono tracking-tight text-white">{{ number_format($membershipStatus['points'] ?? 0) }}</span>
                    <span class="text-[11px] sm:text-xs font-semibold text-white/70">poin</span>
                </div>
            </div>
            <div class="shrink-0">
                <a href="{{ route('account.rewards') }}" class="inline-flex items-center gap-1 sm:gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg bg-white text-[#650506] hover:bg-amber-100 font-bold text-[11px] sm:text-xs shadow-sm transition">
                    <span>Tukar Poin</span>
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </div>

    <!-- Loyalty Points & Membership Strip -->
    <div class="relative z-10 mt-4 sm:mt-6 pt-3.5 sm:pt-5 border-t border-white/15 flex flex-wrap items-center justify-between gap-2.5 sm:gap-3 text-[11px] sm:text-xs">
        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 py-0.5 rounded-md bg-emerald-500/20 text-emerald-300 font-semibold text-[10px] sm:text-[11px] border border-emerald-500/30">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Keanggotaan Aktif
            </span>
            <span class="text-white/40">&bull;</span>
            <span class="text-white/90">Kumpulkan <strong class="text-amber-200">1 Poin</strong> per <strong class="text-white">Rp 10.000</strong></span>
        </div>
        <a href="{{ route('account.rewards') }}" class="text-amber-200 hover:text-white underline font-semibold transition inline-flex items-center gap-1 text-[11px] sm:text-xs">
            <span>Katalog Tukar Poin &rarr;</span>
        </a>
    </div>
</div>

<!-- Harmonized Navigation Tabs Bar (Clean, without stray logout button) -->
<div class="border-b border-gray-200 pb-1.5 sm:pb-2">
    <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto min-w-0 flex-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        <!-- 1. Ringkasan Akun -->
        <a href="{{ route('account.index') }}" 
           class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl {{ request()->routeIs('account.index') ? 'bg-[#650506] text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold' }} text-[11px] sm:text-xs transition shrink-0">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 {{ request()->routeIs('account.index') ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span>Ringkasan Akun</span>
        </a>

        <!-- 2. Alamat -->
        <a href="{{ route('account.addresses') }}" 
           class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl {{ request()->routeIs('account.addresses') ? 'bg-[#650506] text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold' }} text-[11px] sm:text-xs transition shrink-0">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 {{ request()->routeIs('account.addresses') ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Alamat</span>
        </a>

        <!-- 3. Riwayat Pesanan -->
        <a href="{{ route('account.orders') }}" 
           class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl {{ request()->routeIs('account.orders*') ? 'bg-[#650506] text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold' }} text-[11px] sm:text-xs transition shrink-0">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 {{ request()->routeIs('account.orders*') ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            <span>Riwayat Pesanan</span>
        </a>

        <!-- 4. Tukar Poin -->
        <a href="{{ route('account.rewards') }}" 
           class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl {{ request()->routeIs('account.rewards') ? 'bg-[#650506] text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold' }} text-[11px] sm:text-xs transition shrink-0">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 {{ request()->routeIs('account.rewards') ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Tukar Poin</span>
        </a>

        <!-- 5. Riwayat Poin -->
        <a href="{{ route('account.points') }}" 
           class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl {{ request()->routeIs('account.points') ? 'bg-[#650506] text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold' }} text-[11px] sm:text-xs transition shrink-0">
            <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 {{ request()->routeIs('account.points') ? 'text-white' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>Riwayat Poin</span>
        </a>
    </div>
</div>
