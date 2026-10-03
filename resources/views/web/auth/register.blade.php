@extends('layouts.app')

@section('title', 'Daftar Akun - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl border border-gray-200 p-8 shadow-xs">
            <div class="text-center mb-8">
                <div class="inline-flex p-3 rounded-full bg-red-50 mb-3">
                    <svg class="w-8 h-8 text-[#650506]" viewBox="0 0 24 24" fill="currentColor">
                        <rect x="10.75" y="1" width="2.5" height="7" rx="1.25"/>
                        <rect x="10.75" y="16" width="2.5" height="7" rx="1.25"/>
                        <rect x="1" y="10.75" width="7" height="2.5" rx="1.25"/>
                        <rect x="16" y="10.75" width="7" height="2.5" rx="1.25"/>
                        <rect x="3.86" y="5.63" width="2.5" height="7" rx="1.25" transform="rotate(-45 5.11 9.13)"/>
                        <rect x="14.47" y="16.24" width="2.5" height="7" rx="1.25" transform="rotate(-45 15.72 19.74)"/>
                        <rect x="16.24" y="5.63" width="7" height="2.5" rx="1.25" transform="rotate(-45 19.74 6.88)"/>
                        <rect x="5.63" y="16.24" width="7" height="2.5" rx="1.25" transform="rotate(-45 9.13 17.49)"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Daftar Akun Baru</h1>
                <p class="text-xs text-gray-500 mt-1">Bergabung dengan Aroma Palace untuk keuntungan eksklusif</p>
            </div>

            @if($errors->any())
                <div class="mb-5 bg-rose-50 border border-rose-200 text-rose-700 text-xs p-3.5 rounded-lg">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-[#650506] focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-[#650506] focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Nomor Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="0812xxxx"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-[#650506] focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Kata Sandi *</label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-[#650506] focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-2">Konfirmasi Kata Sandi *</label>
                    <input type="password" name="password_confirmation" required minlength="8"
                        class="w-full bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-[#650506] focus:bg-white transition">
                </div>

                <button type="submit" class="w-full bg-[#650506] hover:bg-[#4A070B] text-white font-medium py-3.5 px-4 rounded-lg shadow-sm transition">
                    Daftar Sekarang
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gray-100 text-center text-xs text-gray-500">
                Sudah memiliki akun? <a href="{{ route('login') }}" class="font-bold text-[#650506] hover:underline">Masuk</a>
            </div>
        </div>
    </div>
</div>
@endsection
