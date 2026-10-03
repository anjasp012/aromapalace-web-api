@extends('layouts.app')

@section('title', 'Buku Alamat - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6 pb-6 sm:pb-8">
    <!-- Unified Luxury Account Header & Tabs -->
    @include('web.account.header')

    @if($errors->any())
        <div class="p-3 sm:p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <span class="font-bold block">Harap periksa input Anda:</span>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
        <!-- Saved Addresses (2 Cols) -->
        <div class="lg:col-span-2 space-y-3 sm:space-y-4">
            <h3 class="font-bold text-gray-900 text-sm sm:text-base">Alamat Tersimpan ({{ $addresses->count() }})</h3>

            <div class="space-y-3 sm:space-y-4">
                @forelse($addresses as $address)
                    <div class="bg-white rounded-xl sm:rounded-2xl p-3.5 sm:p-5 border {{ $address->is_primary ? 'border-[#650506] shadow-xs ring-1 ring-[#650506]' : 'border-gray-200' }} shadow-2xs relative transition">
                        <div class="flex items-start justify-between gap-3 sm:gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                    <span class="font-bold text-gray-900 text-xs sm:text-sm">{{ $address->recipient_name }}</span>
                                    <span class="text-gray-400 text-xs">&bull; {{ $address->phone_number }}</span>
                                    <span class="text-[9px] sm:text-[10px] bg-gray-100 text-gray-700 px-1.5 sm:px-2 py-0.5 rounded font-semibold uppercase tracking-wider">
                                        {{ $address->label }}
                                    </span>
                                    @if($address->is_primary)
                                        <span class="text-[9px] sm:text-[10px] bg-[#650506] text-white px-1.5 sm:px-2 py-0.5 rounded font-semibold uppercase tracking-wider">
                                            Utama
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] sm:text-xs text-gray-600 mt-1.5 sm:mt-2 leading-relaxed">
                                    {{ $address->full_address }}<br>
                                    <span class="font-semibold text-gray-800">{{ $address->city }}</span>, {{ $address->postal_code }}
                                </p>
                                @if($address->notes)
                                    <p class="text-[10px] sm:text-[11px] text-gray-400 mt-1 italic">Catatan: {{ $address->notes }}</p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('account.addresses.destroy', $address->id) }}" onsubmit="return confirm('Yakin ingin menghapus alamat ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition" title="Hapus Alamat">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl sm:rounded-2xl p-6 sm:p-8 border border-gray-200 text-center">
                        <span class="text-2xl sm:text-3xl block mb-2">📍</span>
                        <p class="text-xs text-gray-400">Belum ada alamat tersimpan.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Add Address Form (1 Col) -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8 border border-gray-200 shadow-2xs space-y-3.5 sm:space-y-5">
            <h3 class="font-bold text-gray-900 text-sm sm:text-base">Tambah Alamat Baru</h3>

            <form method="POST" action="{{ route('account.addresses.store') }}" class="space-y-3 sm:space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Label Alamat</label>
                    <input type="text" name="label" value="{{ old('label', 'Rumah') }}" placeholder="cth. Rumah, Kantor, Kos"
                           class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Nama Penerima *</label>
                    <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" required placeholder="Nama Lengkap"
                           class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Nomor Telepon *</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" required placeholder="08123456789"
                           class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">
                </div>

                <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Kota / Wilayah *</label>
                        <input type="text" name="city" value="{{ old('city') }}" required placeholder="Jakarta Selatan"
                               class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Kode Pos</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code') }}" placeholder="12190"
                               class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Alamat Lengkap *</label>
                    <textarea name="full_address" rows="3" required placeholder="Nama jalan, gedung, nomor apartemen..."
                              class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">{{ old('full_address') }}</textarea>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Patokan / Catatan Pengiriman</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Di sebelah..."
                           class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">
                </div>

                <button type="submit" class="w-full py-2.5 sm:py-3 rounded-lg bg-[#650506] hover:bg-[#4A070B] text-white font-medium text-xs uppercase tracking-wider transition shadow-xs">
                    Simpan Alamat
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
