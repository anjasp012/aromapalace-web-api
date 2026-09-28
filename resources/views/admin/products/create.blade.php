@extends('layouts.admin')

@section('title', 'Tambah Produk Baru')

@section('content')
<div class="max-w-4xl bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
    <form method="POST" action="{{ route('admin.products.store') }}" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Nama Produk *</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Baccarat Rouge 540 Extrait"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Brand *</label>
                <select name="brand_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
                    <option value="">Pilih Brand</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Kategori *</label>
                <select name="category_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
                    <option value="">Pilih Kategori</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Harga Dasar (Rp) *</label>
                <input type="number" name="base_price" value="{{ old('base_price') }}" required min="0" placeholder="1500000"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Harga Diskon (Opsional)</label>
                <input type="number" name="discount_price" value="{{ old('discount_price') }}" min="0" placeholder="Kosongkan jika tidak ada diskon"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Total Stok Tersedia *</label>
                <input type="number" name="stock" value="{{ old('stock', 20) }}" required min="0"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">URL Foto Produk Utama *</label>
                <input type="url" name="image_url" value="{{ old('image_url') }}" required placeholder="https://..."
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Ringkasan Singkat</label>
                <input type="text" name="short_description" value="{{ old('short_description') }}" placeholder="Aroma floral woody yang mewah dan hangat..."
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Deskripsi Lengkap *</label>
                <textarea name="description" rows="4" required placeholder="Cerita wewangian, top notes, heart notes, base notes..."
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Cara Penggunaan</label>
                <textarea name="how_to_use" rows="2" placeholder="Semprotkan pada titik nadi dari jarak 15 cm..."
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">{{ old('how_to_use') }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Komposisi / Ingredients</label>
                <textarea name="ingredients" rows="2" placeholder="Alcohol Denat., Parfum, Aqua..."
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">{{ old('ingredients') }}</textarea>
            </div>
        </div>

        <!-- Variant Section -->
        <div class="border-t border-gray-100 pt-6">
            <h4 class="font-bold text-gray-900 mb-2">Varian Ukuran (Opsional)</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <input type="text" name="variant_name[]" placeholder="Ukuran (e.g. 50ml)" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm">
                </div>
                <div>
                    <input type="number" name="variant_price[]" placeholder="Harga varian" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm">
                </div>
                <div>
                    <input type="number" name="variant_stock[]" placeholder="Stok varian" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        <!-- Flags -->
        <div class="flex items-center gap-6 border-t border-gray-100 pt-6">
            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                <input type="checkbox" name="is_featured" value="1" class="rounded text-amber-600">
                <span>Tampilkan di Rekomendasi Utama (Featured)</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                <input type="checkbox" name="is_popular" value="1" class="rounded text-amber-600">
                <span>Tandai sebagai Populer / Best Seller</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">Batal</a>
            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm px-6 py-2.5 rounded-xl shadow-sm transition">
                Simpan Produk
            </button>
        </div>
    </form>
</div>
@endsection

