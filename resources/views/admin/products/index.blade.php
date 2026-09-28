@extends('layouts.admin')

@section('title', 'Katalog & Stok Produk')

@section('content')
<div class="space-y-6">
    <!-- Action Bar & Filter -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk..." 
                class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-600">

            <select name="category_id" class="bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-amber-600">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="bg-stone-800 hover:bg-stone-900 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition">
                Filter
            </button>
        </form>

        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Tambah Produk Baru
        </a>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Foto & Produk</th>
                        <th class="px-6 py-4">Brand / Kategori</th>
                        <th class="px-6 py-4">Harga Dasar</th>
                        <th class="px-6 py-4">Harga Diskon</th>
                        <th class="px-6 py-4">Stok</th>
                        <th class="px-6 py-4">Rating</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="px-6 py-4 flex items-center gap-4">
                            <img src="{{ $product->primary_image ?? 'https://via.placeholder.com/60' }}" class="w-12 h-12 rounded-xl object-cover border border-gray-200" alt="{{ $product->name }}">
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $product->name }}</h4>
                                <span class="text-xs text-gray-400">SKU: {{ $product->variants->first()?->sku ?? 'AP-PROD-' . $product->id }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-semibold text-gray-800 block">{{ $product->brand?->name ?? '-' }}</span>
                            <span class="text-xs text-gray-400">{{ $product->category?->name ?? '-' }}</span>
                        </td>
                        <td class="px-6 py-4 font-semibold text-gray-700">
                            Rp {{ number_format($product->base_price, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4">
                            @if($product->discount_price)
                                <span class="font-bold text-emerald-600">Rp {{ number_format($product->discount_price, 0, ',', '.') }}</span>
                                <span class="text-xs bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded ml-1 font-bold">-{{ $product->discount_percent }}%</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg {{ $product->stock <= 10 ? 'bg-rose-100 text-rose-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $product->stock }} botol
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="flex items-center gap-1 font-bold text-amber-500">
                                ★ {{ number_format($product->rating_avg, 1) }}
                                <span class="text-xs text-gray-400 font-normal">({{ $product->reviews_count }})</span>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.products.edit', $product->id) }}" class="text-amber-600 hover:text-amber-700 font-semibold text-xs">Edit</a>
                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-700 font-semibold text-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">Belum ada produk terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $products->links() }}
        </div>
    </div>
</div>
@endsection

