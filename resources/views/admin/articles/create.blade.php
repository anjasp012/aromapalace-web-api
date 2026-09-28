@extends('layouts.admin')

@section('title', 'Tulis Artikel Kecantikan Baru')

@section('content')
<div class="max-w-3xl bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
    <form method="POST" action="{{ route('admin.articles.store') }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Judul Artikel *</label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Cara Memilih Aroma Parfum Berdasarkan Musim"
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Topik Kategori *</label>
                <select name="topic_id" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
                    <option value="">Pilih Topik</option>
                    @foreach($topics as $top)
                        <option value="{{ $top->id }}">{{ $top->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Estimasi Waktu Baca (Menit) *</label>
                <input type="number" name="reading_time_minutes" value="{{ old('reading_time_minutes', 3) }}" required min="1"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">URL Foto Sampul (Cover Image) *</label>
            <input type="url" name="cover_image" value="{{ old('cover_image') }}" required placeholder="https://images.unsplash.com/..."
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Ringkasan Singkat (Summary) *</label>
            <textarea name="summary" rows="2" required placeholder="Ringkasan 1-2 kalimat pengantar artikel..."
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">{{ old('summary') }}</textarea>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Isi Konten Artikel *</label>
            <textarea name="content" rows="8" required placeholder="Tuliskan isi artikel selengkapnya di sini..."
                class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">{{ old('content') }}</textarea>
        </div>

        <div class="border-t border-gray-100 pt-4">
            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                <input type="checkbox" name="is_trending" value="1" class="rounded text-amber-600">
                <span>Tandai sebagai Beauty Trends (Tampil di tren utama)</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
            <a href="{{ route('admin.articles.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">Batal</a>
            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm px-6 py-2.5 rounded-xl shadow-sm transition">
                Terbitkan Artikel
            </button>
        </div>
    </form>
</div>
@endsection

