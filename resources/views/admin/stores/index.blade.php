@extends('layouts.admin')

@section('title', 'Gerai Toko & Store Pickup')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Kelola lokasi gerai fisik, fasilitas store pickup (click & collect), dan koordinat PostGIS GPS.</p>
        <a href="{{ route('admin.stores.create') }}" class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Tambah Toko Baru
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($stores as $store)
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                <img src="{{ $store->image_url ?? 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?auto=format&fit=crop&w=600&q=80' }}" class="w-full h-44 object-cover" alt="{{ $store->name }}">
                <div class="p-6">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded">{{ $store->city ?? 'Indonesia' }}</span>
                        @if($store->is_pickup_available)
                            <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded flex items-center gap-1">
                                ✓ Store Pickup
                            </span>
                        @endif
                    </div>
                    <h4 class="font-bold text-gray-900 text-base mb-1">{{ $store->name }}</h4>
                    <p class="text-xs text-gray-500 mb-3">{{ $store->address }}</p>
                    <div class="text-xs text-gray-600 space-y-1">
                        <p>🕒 Jam Buka: <span class="font-semibold">{{ $store->operating_hours }}</span></p>
                        <p>📞 Telp: <span class="font-semibold">{{ $store->phone ?? '-' }}</span></p>
                        <p class="font-mono text-stone-400">📍 {{ $store->latitude }}, {{ $store->longitude }}</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.stores.edit', $store->id) }}" class="text-xs font-bold text-amber-600 hover:underline">Edit</a>
                <form action="{{ route('admin.stores.destroy', $store->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus gerai toko ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-bold text-rose-600 hover:underline">Hapus</button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-3 py-12 text-center text-gray-400 bg-white rounded-2xl border border-gray-100">
            Belum ada data gerai toko.
        </div>
        @endforelse
    </div>
</div>
@endsection

