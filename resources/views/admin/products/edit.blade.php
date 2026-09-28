@extends('layouts.admin')

@section('title', 'Edit Produk: ' . $product->name)

@section('content')
<div class="max-w-4xl bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
    <form method="POST" action="{{ route('admin.products.update', $product->id) }}" class="space-y-6">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Nama Produk *</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Brand *</label>
                <select name="brand_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ $product->brand_id == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Kategori *</label>
                <select name="category_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Harga Dasar (Rp) *</label>
                <input type="number" name="base_price" value="{{ old('base_price', $product->base_price) }}" required min="0"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Harga Diskon (Opsional)</label>
                <input type="number" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}" min="0"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Total Stok *</label>
                <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" required min="0"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">URL Foto Produk Utama</label>
                <input type="url" name="image_url" value="{{ old('image_url', $product->primary_image) }}"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Ringkasan Singkat</label>
                <input type="text" name="short_description" value="{{ old('short_description', $product->short_description) }}"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Deskripsi Lengkap *</label>
                <textarea name="description" rows="4" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">{{ old('description', $product->description) }}</textarea>
            </div>
        </div>

        <!-- Flags -->
        <div class="flex flex-wrap items-center gap-6 border-t border-gray-100 pt-6">
            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                <input type="checkbox" name="is_featured" value="1" {{ $product->is_featured ? 'checked' : '' }} class="rounded text-amber-600">
                <span>Rekomendasi Utama (Featured)</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                <input type="checkbox" name="is_popular" value="1" {{ $product->is_popular ? 'checked' : '' }} class="rounded text-amber-600">
                <span>Populer / Best Seller</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                <input type="checkbox" name="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} class="rounded text-amber-600">
                <span>Produk Aktif / Tampil di Toko</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
            <a href="{{ route('admin.products.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">Kembali</a>
            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm px-6 py-2.5 rounded-xl shadow-sm transition">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>
@endsection

