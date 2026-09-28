@extends('layouts.app')

@section('title', 'Riwayat Poin Loyalty - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6 pb-6 sm:pb-8">
    <!-- Breadcrumb & Header -->
    <div class="border-b border-gray-100 pb-3 sm:pb-5">
        <nav class="flex items-center gap-2 text-[11px] sm:text-xs text-gray-500 mb-1.5">
            <a href="{{ route('home') }}" class="hover:text-[#650506] transition">Beranda</a>
            <span>/</span>
            <a href="{{ route('account.index') }}" class="hover:text-[#650506] transition">Akun Saya</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Riwayat Poin</span>
        </nav>
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-serif font-bold text-gray-900 tracking-tight">Riwayat Poin Loyalty</h1>
        <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Catatan lengkap perolehan poin dari transaksi dan penukaran reward membership Anda.</p>
    </div>

    <!-- Points Summary Card (Royal Maroon Hero) -->
    <div class="relative overflow-hidden rounded-xl sm:rounded-2xl bg-gradient-to-br from-[#4A070B] via-[#650506] to-[#2B0304] text-white p-4 sm:p-6 lg:p-8 shadow-xl border border-[#650506]/40">
        <!-- Ambient Glow -->
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4 sm:gap-6">
            <div class="space-y-1 sm:space-y-1.5">
                <div class="flex items-center gap-2">
                    <span class="px-2 sm:px-2.5 py-0.5 rounded-full text-[9px] sm:text-[10px] font-extrabold uppercase tracking-wider bg-amber-400/20 text-amber-200 border border-amber-400/30">
                        ★ Member Aroma Palace Club
                    </span>
                    <span class="inline-flex items-center gap-1 text-[10px] sm:text-[11px] text-emerald-300 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Keanggotaan Aktif
                    </span>
                </div>
                <h2 class="text-lg sm:text-xl lg:text-2xl font-serif font-bold text-white tracking-tight">Saldo Poin Tersedia</h2>
                <p class="text-[11px] sm:text-xs text-white/75">
                    Dapatkan <strong class="text-amber-200">1 Poin</strong> tiap transaksi <strong class="text-white">Rp 10.000</strong>. Poin tidak memiliki batas kedaluwarsa.
                </p>
            </div>

            <!-- Points Stat Pill & Action -->
            <div class="flex items-center gap-3 sm:gap-4 flex-wrap sm:flex-nowrap">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl sm:rounded-2xl px-4 sm:px-6 py-2.5 sm:py-4 text-center md:text-right shadow-sm shrink-0 min-w-[140px] sm:min-w-[170px]">
                    <span class="text-[10px] sm:text-[11px] font-bold text-amber-200 uppercase tracking-wider block">Total Saldo Poin</span>
                    <span class="text-2xl sm:text-3xl lg:text-4xl font-extrabold font-mono tracking-tight text-white block mt-0.5">
                        {{ number_format($membershipStatus['points'] ?? 0) }}
                    </span>
                    <span class="text-[9px] sm:text-[10px] text-white/60 block mt-0.5 font-medium">Poin Aktif</span>
                </div>

                <a href="{{ route('account.rewards') }}" 
                   class="inline-flex items-center gap-1.5 sm:gap-2 px-3.5 sm:px-5 py-2 sm:py-3 rounded-lg sm:rounded-xl bg-white text-[#650506] hover:bg-amber-100 font-bold text-[11px] sm:text-xs shadow-sm transition shrink-0">
                    <span>Tukar Reward &rarr;</span>
                </a>
            </div>
        </div>

        <!-- Mini Stats Summary Strip -->
        <div class="relative z-10 mt-4 sm:mt-6 pt-3.5 sm:pt-5 border-t border-white/15 grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4 text-xs">
            <div>
                <span class="text-white/60 text-[10px] sm:text-[11px] block">Total Poin Diperoleh</span>
                <span class="text-emerald-300 font-bold font-mono text-xs sm:text-sm lg:text-base mt-0.5 block">
                    +{{ number_format($earnedPoints ?? 0) }} pts
                </span>
            </div>
            <div>
                <span class="text-white/60 text-[10px] sm:text-[11px] block">Total Poin Ditukarkan</span>
                <span class="text-rose-300 font-bold font-mono text-xs sm:text-sm lg:text-base mt-0.5 block">
                    -{{ number_format($redeemedPoints ?? 0) }} pts
                </span>
            </div>
            <div class="col-span-2 sm:col-span-1">
                <span class="text-white/60 text-[10px] sm:text-[11px] block">Total Transaksi Poin</span>
                <span class="text-amber-200 font-bold font-mono text-xs sm:text-sm lg:text-base mt-0.5 block">
                    {{ number_format($pointHistories->total() ?? count($pointHistories)) }} aktivitas
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs Bar with Integrated Logout -->
    <div class="flex items-center justify-between gap-2 border-b border-gray-200 pb-2">
        <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto min-w-0 flex-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <a href="{{ route('account.index') }}" 
               class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold text-[11px] sm:text-xs transition shrink-0">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Ringkasan Akun</span>
            </a>

            <a href="{{ route('account.orders') }}" 
               class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold text-[11px] sm:text-xs transition shrink-0">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                <span>Riwayat Pesanan</span>
            </a>

            <a href="{{ route('account.rewards') }}" 
               class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold text-[11px] sm:text-xs transition shrink-0">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Loyalty Rewards</span>
            </a>

            <a href="{{ route('account.addresses') }}" 
               class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold text-[11px] sm:text-xs transition shrink-0">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Buku Alamat</span>
            </a>

            <a href="{{ route('account.points') }}" 
               class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl bg-[#650506] text-white font-bold text-[11px] sm:text-xs shadow-xs shrink-0">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Riwayat Poin</span>
            </a>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-lg sm:rounded-xl border border-gray-200 hover:border-rose-200 hover:bg-rose-50 text-gray-500 hover:text-rose-600 text-[11px] sm:text-xs font-semibold transition cursor-pointer shrink-0" title="Keluar dari akun">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400 group-hover:text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>

    <!-- Riwayat Transaksi Points List -->
    <div class="bg-white rounded-xl sm:rounded-2xl p-3.5 sm:p-6 lg:p-7 border border-gray-200/90 shadow-2xs space-y-3 sm:space-y-4">
        <div class="border-b border-gray-100 pb-2.5 sm:pb-3 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h3 class="font-serif font-bold text-gray-900 text-sm sm:text-base">Riwayat Aktivitas Poin</h3>
                <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Daftar lengkap perolehan poin dari pesanan dan penggunaan reward.</p>
            </div>
            <span class="text-[11px] sm:text-xs text-gray-400 font-mono">
                Total: {{ $pointHistories->total() ?? count($pointHistories) }} catatan
            </span>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($pointHistories as $history)
                @php
                    $isPositive = $history->points > 0;
                @endphp
                <div class="py-3 sm:py-4 flex items-center justify-between gap-3 sm:gap-4 text-xs transition hover:bg-stone-50/50 px-1.5 sm:px-2 rounded-xl">
                    <div class="flex items-center gap-2.5 sm:gap-3.5 min-w-0">
                        <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl {{ $isPositive ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-rose-50 text-rose-600 border border-rose-100' }} flex items-center justify-center font-bold text-xs sm:text-sm shrink-0 shadow-2xs">
                            {{ $isPositive ? '+' : '-' }}
                        </div>
                        <div class="min-w-0">
                            <span class="font-bold text-gray-900 block text-xs sm:text-sm truncate">{{ $history->description ?? ($isPositive ? 'Poin Didapat dari Pesanan' : 'Penukaran Reward') }}</span>
                            <div class="flex items-center gap-1.5 sm:gap-2 mt-0.5 text-[10px] sm:text-[11px] text-gray-400">
                                <span>{{ $history->created_at->format('d M Y, H:i') }} WIB</span>
                                @if($history->type)
                                    <span>&bull;</span>
                                    <span class="uppercase tracking-wider font-semibold text-[9px] sm:text-[10px] {{ $isPositive ? 'text-emerald-700' : 'text-rose-600' }}">
                                        {{ $history->type }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="font-mono font-bold text-xs sm:text-sm lg:text-base {{ $isPositive ? 'text-emerald-700' : 'text-rose-600' }}">
                            {{ $isPositive ? '+' : '' }}{{ number_format($history->points) }} pts
                        </span>
                        @if(isset($history->balance_after))
                            <span class="text-[9px] sm:text-[10px] text-gray-400 block font-mono mt-0.5">Saldo: {{ number_format($history->balance_after) }} pts</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-12 sm:py-16 space-y-2 text-gray-400">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-stone-100 text-gray-400 mx-auto flex items-center justify-center">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h4 class="font-bold text-sm text-gray-800">Belum Ada Riwayat Poin</h4>
                    <p class="text-xs text-gray-500 max-w-sm mx-auto">
                        Kumpulkan poin pertama Anda dengan berbelanja wewangian eksklusif di Aroma Palace. Setiap Rp 10.000 bernilai 1 Poin.
                    </p>
                    <a href="{{ route('products.index') }}" class="inline-block mt-3 px-4 py-2 rounded-xl bg-[#650506] text-white text-xs font-bold hover:bg-[#4A070B] transition">
                        Mulai Belanja &rarr;
                    </a>
                </div>
            @endforelse
        </div>

        @if(method_exists($pointHistories, 'hasPages') && $pointHistories->hasPages())
            <div class="pt-3 sm:pt-4 border-t border-gray-100">
                {{ $pointHistories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

