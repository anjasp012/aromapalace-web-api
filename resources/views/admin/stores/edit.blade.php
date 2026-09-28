@extends('layouts.admin')

@section('title', 'Edit Gerai Toko: ' . $store->name)

@section('content')
<div class="max-w-2xl bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
    <form method="POST" action="{{ route('admin.stores.update', $store->id) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Nama Gerai Toko *</label>
            <input type="text" name="name" value="{{ old('name', $store->name) }}" required
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Kota *</label>
                <input type="text" name="city" value="{{ old('city', $store->city) }}" required
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">No. Telepon Toko</label>
                <input type="text" name="phone" value="{{ old('phone', $store->phone) }}"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Alamat Lengkap *</label>
            <textarea name="address" rows="2" required
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">{{ old('address', $store->address) }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Jam Operasional</label>
            <input type="text" name="operating_hours" value="{{ old('operating_hours', $store->operating_hours) }}"
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Latitude (GPS) *</label>
                <input type="number" step="any" name="latitude" value="{{ old('latitude', $store->latitude) }}" required
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600 font-mono">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Longitude (GPS) *</label>
                <input type="number" step="any" name="longitude" value="{{ old('longitude', $store->longitude) }}" required
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600 font-mono">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">URL Foto Gerai Toko</label>
            <input type="url" name="image_url" value="{{ old('image_url', $store->image_url) }}"
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
        </div>

        <div class="border-t border-gray-100 pt-4">
            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                <input type="checkbox" name="is_pickup_available" value="1" {{ $store->is_pickup_available ? 'checked' : '' }} class="rounded text-amber-600">
                <span>Tersedia fasilitas Store Pickup (Click & Collect) di gerai ini</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
            <a href="{{ route('admin.stores.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">Kembali</a>
            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm px-6 py-2.5 rounded-xl shadow-sm transition">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection

