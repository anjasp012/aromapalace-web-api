@extends('layouts.app')

@section('title', 'Loyalty Points & Rewards - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6 pb-6 sm:pb-8">
    <!-- Breadcrumb & Header -->
    <div class="border-b border-gray-100 pb-3 sm:pb-5">
        <nav class="flex items-center gap-2 text-[11px] sm:text-xs text-gray-500 mb-1.5">
            <a href="{{ route('home') }}" class="hover:text-[#650506] transition">Beranda</a>
            <span>/</span>
            <a href="{{ route('account.index') }}" class="hover:text-[#650506] transition">Akun Saya</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Loyalty Rewards</span>
        </nav>
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-serif font-bold text-gray-900 tracking-tight">Loyalty Points & Rewards</h1>
        <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Kumpulkan poin dari setiap transaksi dan tukarkan dengan voucher diskon serta keuntungan eksklusif.</p>
    </div>

    <!-- Points & Membership Status Card -->
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
                <h2 class="text-lg sm:text-xl lg:text-2xl font-serif font-bold text-white tracking-tight">Saldo Poin Loyalty Anda</h2>
                <p class="text-[11px] sm:text-xs text-white/75">
                    Dapatkan <strong class="text-amber-200">1 Poin</strong> untuk setiap pembelanjaan <strong class="text-white">Rp 10.000</strong>. Poin dapat ditukarkan kapan saja.
                </p>
            </div>

            <!-- Points Display -->
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl sm:rounded-2xl px-4 sm:px-6 py-2.5 sm:py-4 text-center md:text-right shadow-sm shrink-0 min-w-[150px] sm:min-w-[200px]">
                <span class="text-[10px] sm:text-[11px] font-bold text-amber-200 uppercase tracking-wider block">Total Poin Aktif</span>
                <span class="text-2xl sm:text-3xl lg:text-4xl font-extrabold font-mono tracking-tight text-white block mt-0.5">
                    {{ number_format($membershipStatus['points']) }}
                </span>
                <span class="text-[9px] sm:text-[10px] text-white/60 block mt-0.5 font-medium">1 Poin = Potongan Nilai Belanja</span>
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
               class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl bg-[#650506] text-white font-bold text-[11px] sm:text-xs shadow-xs shrink-0">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Loyalty Rewards</span>
                <span class="px-1.5 py-0.5 rounded-full bg-white/20 text-white text-[9px] sm:text-[10px] font-mono font-bold">
                    {{ number_format($membershipStatus['points']) }} pts
                </span>
            </a>

            <a href="{{ route('account.addresses') }}" 
               class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold text-[11px] sm:text-xs transition shrink-0">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Buku Alamat</span>
            </a>

            <a href="{{ route('account.points') }}" 
               class="inline-flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-lg sm:rounded-xl text-gray-600 hover:text-gray-900 hover:bg-gray-100 font-semibold text-[11px] sm:text-xs transition shrink-0">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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

    <!-- Membership Benefits Highlights -->
    <div class="bg-white rounded-xl sm:rounded-2xl p-3.5 sm:p-6 lg:p-7 border border-gray-200/90 shadow-2xs space-y-3 sm:space-y-4">
        <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 sm:pb-3">
            <div>
                <h3 class="font-serif font-bold text-gray-900 text-sm sm:text-base">Keuntungan Keanggotaan Aroma Palace Club</h3>
                <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Nikmati berbagai keuntungan istimewa yang otomatis berlaku bagi member terdaftar.</p>
            </div>
            <span class="px-2 sm:px-2.5 py-0.5 sm:py-1 bg-[#650506]/10 text-[#650506] text-[10px] sm:text-xs font-bold rounded-lg shrink-0">
                Member Resmi
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-4 pt-1">
            @foreach($membershipStatus['benefits'] ?? $membershipStatus['my_benefits'] as $idx => $benefit)
                <div class="flex items-start gap-2.5 sm:gap-3 p-2.5 sm:p-3.5 rounded-lg sm:rounded-xl bg-stone-50/70 border border-gray-100 text-[11px] sm:text-xs">
                    <div class="w-5 h-5 sm:w-6 sm:h-6 rounded-md sm:rounded-lg bg-[#650506] text-white flex items-center justify-center text-[10px] sm:text-xs font-bold shrink-0 mt-0.5">
                        ✓
                    </div>
                    <span class="font-medium text-gray-800 leading-relaxed">{{ $benefit }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Available Rewards Section -->
    <div class="space-y-3 sm:space-y-4">
        <div>
            <h3 class="font-serif font-bold text-gray-900 text-lg sm:text-xl">Katalog Penukaran Reward & Voucher</h3>
            <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Pilih reward atau voucher belanja yang ingin Anda tukarkan menggunakan saldo loyalty points.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-6">
            @forelse($rewards as $reward)
                @php
                    $canRedeem = $membershipStatus['points'] >= $reward->points_required;
                @endphp
                <div class="bg-white rounded-xl sm:rounded-2xl p-3.5 sm:p-5 border border-gray-200/90 shadow-2xs hover:shadow-md transition flex flex-col justify-between group">
                    <div>
                        <div class="relative h-36 sm:h-44 rounded-lg sm:rounded-xl overflow-hidden mb-3 sm:mb-4 bg-stone-100 flex items-center justify-center border border-gray-100">
                            <img src="{{ $reward->image_url ?? 'https://images.unsplash.com/photo-1547887537-6158d64c35b3?auto=format&fit=crop&w=600&q=80' }}"
                                 alt="{{ $reward->title }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <span class="absolute top-2.5 right-2.5 sm:top-3 sm:right-3 px-2 sm:px-2.5 py-0.5 sm:py-1 bg-[#650506] text-white font-bold font-mono text-[10px] sm:text-xs rounded-lg shadow-sm">
                                {{ number_format($reward->points_required) }} Poin
                            </span>
                        </div>

                        <h4 class="font-bold text-xs sm:text-sm text-gray-900">{{ $reward->title }}</h4>
                        <p class="text-[11px] sm:text-xs text-gray-500 mt-1 leading-relaxed">{{ $reward->description }}</p>

                        @if($reward->discount_amount)
                            <div class="mt-2.5 sm:mt-3 inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-0.5 sm:py-1 bg-emerald-50 text-emerald-800 text-[10px] sm:text-xs font-bold rounded-lg border border-emerald-100">
                                <span>Nilai Voucher:</span>
                                <span class="font-mono">Rp {{ number_format($reward->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="pt-3.5 sm:pt-5 border-t border-gray-100 mt-3.5 sm:mt-5 flex items-center justify-between gap-3">
                        <div>
                            <span class="text-[9px] sm:text-[10px] text-gray-400 uppercase tracking-wider block font-semibold">Biaya Poin:</span>
                            <span class="text-xs sm:text-sm font-extrabold text-[#650506] font-mono">{{ number_format($reward->points_required) }} pts</span>
                        </div>

                        <form method="POST" action="{{ route('account.rewards.redeem', $reward->id) }}" onsubmit="return confirm('Tukarkan {{ number_format($reward->points_required) }} poin untuk reward ini?');">
                            @csrf
                            <button type="submit"
                                    @if(!$canRedeem) disabled @endif
                                    class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg sm:rounded-xl text-[11px] sm:text-xs font-bold transition shadow-xs {{ $canRedeem ? 'bg-[#650506] hover:bg-[#4A070B] text-white cursor-pointer' : 'bg-gray-100 text-gray-400 cursor-not-allowed' }}">
                                {{ $canRedeem ? 'Tukar Reward' : 'Poin Kurang' }}
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-3 text-center py-12 sm:py-16 bg-white rounded-xl sm:rounded-2xl border border-gray-200 p-6 sm:p-8 space-y-2">
                    <span class="text-2xl sm:text-3xl block">🎁</span>
                    <h4 class="font-bold text-sm text-gray-900">Belum Ada Reward Tersedia</h4>
                    <p class="text-xs text-gray-500">Nantikan voucher dan reward kejutan menarik lainnya segera.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Riwayat Transaksi Points (Point History) -->
    <div class="bg-white rounded-xl sm:rounded-2xl p-3.5 sm:p-6 lg:p-7 border border-gray-200/90 shadow-2xs space-y-3 sm:space-y-4">
        <div class="border-b border-gray-100 pb-2.5 sm:pb-3">
            <h3 class="font-serif font-bold text-gray-900 text-sm sm:text-base">Riwayat Poin Loyalty</h3>
            <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Catatan perolehan dan penukaran poin dari transaksi Anda.</p>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($pointHistories as $history)
                @php
                    $isPositive = $history->points > 0;
                @endphp
                <div class="py-2.5 sm:py-3.5 flex items-center justify-between gap-3 sm:gap-4 text-xs">
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg sm:rounded-xl {{ $isPositive ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} flex items-center justify-center font-bold text-xs sm:text-sm shrink-0">
                            {{ $isPositive ? '+' : '-' }}
                        </div>
                        <div>
                            <span class="font-bold text-gray-900 block text-xs">{{ $history->description ?? ($isPositive ? 'Poin Didapat' : 'Penukaran Reward') }}</span>
                            <span class="text-gray-400 text-[10px] sm:text-[11px]">{{ $history->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="font-mono font-bold text-xs sm:text-sm {{ $isPositive ? 'text-emerald-700' : 'text-rose-600' }}">
                            {{ $isPositive ? '+' : '' }}{{ number_format($history->points) }} pts
                        </span>
                        @if(isset($history->balance_after))
                            <span class="text-[9px] sm:text-[10px] text-gray-400 block font-mono">Sisa: {{ number_format($history->balance_after) }} pts</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center py-8 sm:py-10 space-y-1 text-gray-400">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-xs text-gray-500">Belum ada riwayat aktivitas poin.</p>
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
