@extends('layouts.admin')

@section('title', 'Manajemen Voucher & Promo')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Kelola kupon diskon, kuota penggunaan, dan promosi eksklusif member.</p>
        <a href="{{ route('admin.promotions.create') }}" class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Buat Voucher Baru
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Kode & Judul</th>
                        <th class="px-6 py-4">Tipe Diskon</th>
                        <th class="px-6 py-4">Min. Belanja</th>
                        <th class="px-6 py-4">Kuota / Terpakai</th>
                        <th class="px-6 py-4">Periode Aktif</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($promotions as $promo)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="px-6 py-4">
                            <span class="font-extrabold font-mono text-amber-700 bg-amber-50 px-2 py-0.5 rounded text-xs inline-block mb-1">
                                {{ $promo->code }}
                            </span>
                            <h5 class="font-bold text-gray-900">{{ $promo->title }}</h5>
                            @if($promo->is_exclusive)
                                <span class="text-xs text-purple-600 font-semibold">★ Khusus Member Eksklusif</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($promo->discount_type === 'percentage')
                                <span class="font-bold text-emerald-600">{{ $promo->discount_value }}%</span>
                                @if($promo->max_discount)<span class="text-xs text-gray-400 block">(Maks Rp {{ number_format($promo->max_discount, 0, ',', '.') }})</span>@endif
                            @else
                                <span class="font-bold text-emerald-600">Rp {{ number_format($promo->discount_value, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            Rp {{ number_format($promo->min_purchase, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-gray-800">{{ $promo->used_count }}</span> / {{ $promo->quota }}
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500">
                            {{ $promo->start_date->format('d M') }} s/d {{ $promo->end_date->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $promo->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">
                                {{ $promo->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <form action="{{ route('admin.promotions.toggle', $promo->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs font-semibold text-stone-600 hover:underline">
                                    {{ $promo->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.promotions.destroy', $promo->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus voucher ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">Belum ada promo terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $promotions->links() }}
        </div>
    </div>
</div>
@endsection

