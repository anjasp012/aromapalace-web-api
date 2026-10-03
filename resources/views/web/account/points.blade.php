@extends('layouts.app')

@section('title', 'Riwayat Poin Loyalty - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6 pb-6 sm:pb-8">
    <!-- Unified Luxury Account Header & Tabs -->
    @include('web.account.header')

    <!-- Mini Stats Summary Strip -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4 text-xs bg-white rounded-xl sm:rounded-2xl p-4 sm:p-5 border border-gray-200 shadow-2xs">
        <div>
            <span class="text-gray-500 text-[10px] sm:text-[11px] block">Total Poin Diperoleh</span>
            <span class="text-emerald-700 font-bold font-mono text-sm sm:text-base mt-0.5 block">
                +{{ number_format($earnedPoints ?? 0) }} pts
            </span>
        </div>
        <div>
            <span class="text-gray-500 text-[10px] sm:text-[11px] block">Total Poin Ditukarkan</span>
            <span class="text-rose-700 font-bold font-mono text-sm sm:text-base mt-0.5 block">
                -{{ number_format($redeemedPoints ?? 0) }} pts
            </span>
        </div>
        <div class="col-span-2 sm:col-span-1">
            <span class="text-gray-500 text-[10px] sm:text-[11px] block">Total Transaksi Poin</span>
            <span class="text-[#704828] font-bold font-mono text-sm sm:text-base mt-0.5 block">
                {{ number_format($pointHistories->total() ?? count($pointHistories)) }} aktivitas
            </span>
        </div>
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

