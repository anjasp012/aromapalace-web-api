@extends('layouts.admin')

@section('title', 'Buat Voucher / Promo Baru')

@section('content')
<div class="max-w-3xl bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
    <form method="POST" action="{{ route('admin.promotions.store') }}" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Kode Voucher *</label>
                <input type="text" name="code" value="{{ old('code') }}" required placeholder="Contoh: AROMA15"
                    class="w-full uppercase font-mono bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Judul Promosi *</label>
                <input type="text" name="title" value="{{ old('title') }}" required placeholder="Diskon Spesial 15%"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Tipe Diskon *</label>
                <select name="discount_type" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
                    <option value="percentage">Persentase (%)</option>
                    <option value="fixed">Nominal Tetap (Rp)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Nilai Diskon (% atau Rp) *</label>
                <input type="number" name="discount_value" value="{{ old('discount_value') }}" required min="1" placeholder="Contoh: 15 atau 50000"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Minimal Belanja (Rp) *</label>
                <input type="number" name="min_purchase" value="{{ old('min_purchase', 0) }}" required min="0"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Maksimal Potongan Diskon (Rp)</label>
                <input type="number" name="max_discount" value="{{ old('max_discount') }}" min="0" placeholder="Kosongkan jika tanpa batas"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Total Kuota Pemakaian *</label>
                <input type="number" name="quota" value="{{ old('quota', 100) }}" required min="1"
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Tanggal Mulai Berlaku *</label>
                <input type="datetime-local" name="start_date" value="{{ old('start_date', now()->format('Y-m-d\TH:i')) }}" required
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Tanggal Berakhir *</label>
                <input type="datetime-local" name="end_date" value="{{ old('end_date', now()->addDays(30)->format('Y-m-d\TH:i')) }}" required
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Syarat & Ketentuan (T&C)</label>
                <textarea name="terms_conditions" rows="3" placeholder="Satu kali pemakaian per user..."
                    class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-amber-600">{{ old('terms_conditions') }}</textarea>
            </div>
        </div>

        <div class="border-t border-gray-100 pt-6">
            <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-gray-700">
                <input type="checkbox" name="is_exclusive" value="1" class="rounded text-amber-600">
                <span>Voucher Eksklusif Member (Hanya tampil untuk member loyalitas)</span>
            </label>
        </div>

        <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
            <a href="{{ route('admin.promotions.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">Batal</a>
            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-semibold text-sm px-6 py-2.5 rounded-xl shadow-sm transition">
                Terbitkan Voucher
            </button>
        </div>
    </form>
</div>
@endsection

