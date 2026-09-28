@extends('layouts.admin')

@section('title', 'Manajemen Artikel & Tips Kecantikan')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <p class="text-sm text-gray-500">Kelola artikel edukasi, tips memilih parfum, dan tren wewangian.</p>
        <a href="{{ route('admin.articles.create') }}" class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl shadow-sm transition inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Tulis Artikel Baru
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4">Sampul & Judul Artikel</th>
                        <th class="px-6 py-4">Topik</th>
                        <th class="px-6 py-4">Estimasi Baca</th>
                        <th class="px-6 py-4">Status & Tren</th>
                        <th class="px-6 py-4">Tanggal Rilis</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($articles as $art)
                    <tr class="hover:bg-gray-50/80 transition">
                        <td class="px-6 py-4 flex items-center gap-4">
                            <img src="{{ $art->cover_image }}" class="w-16 h-12 rounded-xl object-cover border border-gray-200" alt="{{ $art->title }}">
                            <div>
                                <h5 class="font-bold text-gray-900 leading-snug">{{ $art->title }}</h5>
                                <span class="text-xs text-gray-400">Oleh {{ $art->author_name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-1 rounded-full">
                                {{ $art->topic?->name ?? 'Umum' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $art->reading_time_minutes }} menit
                        </td>
                        <td class="px-6 py-4">
                            @if($art->is_trending)
                                <span class="px-2 py-0.5 text-xs font-bold bg-purple-100 text-purple-700 rounded-md">🔥 Trending</span>
                            @else
                                <span class="text-xs text-gray-400">Standar</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500">
                            {{ $art->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <a href="{{ route('admin.articles.edit', $art->id) }}" class="text-xs font-bold text-amber-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.articles.destroy', $art->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus artikel ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold text-rose-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">Belum ada artikel yang diterbitkan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $articles->links() }}
        </div>
    </div>
</div>
@endsection

