@extends('layouts.app')

@section('title', 'Tukar Poin - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6 pb-6 sm:pb-8">
    <!-- Unified Luxury Account Header & Tabs -->
    @include('web.account.header')

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
</div>
@endsection
