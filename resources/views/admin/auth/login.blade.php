<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - Aroma Palace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#1F0206] font-sans flex items-center justify-center min-h-screen p-4 text-stone-100">
    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="Aroma Palace Logo" class="w-20 h-20 rounded-full object-contain mx-auto mb-3.5 ring-2 ring-gold-400/60 shadow-[0_0_20px_rgba(195,154,43,0.35)]">
            <span class="text-xs uppercase tracking-widest text-gold-400 font-bold block">Portal Administrator</span>
            <h1 class="text-2xl font-serif font-bold tracking-wider text-white mt-1">AROMA PALACE</h1>
            <p class="text-xs text-stone-300 mt-1">Masuk ke backoffice manajemen toko, pesanan, & logistik</p>
        </div>

        <!-- Login Card -->
        <div class="bg-[#38050D] border border-gold-500/30 rounded-3xl p-8 shadow-2xl">
            @if($errors->any())
                <div class="mb-5 bg-rose-950/80 border border-rose-800 text-rose-200 text-xs p-4 rounded-xl">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 bg-rose-950/80 border border-rose-800 text-rose-200 text-xs p-4 rounded-xl">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="mb-5 bg-emerald-950/80 border border-emerald-800 text-emerald-200 text-xs p-4 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.submit') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gold-200 uppercase tracking-wider mb-2">Email Administrator</label>
                    <input type="email" name="email" value="{{ old('email', 'admin@aromapalace.com') }}" required autofocus
                        class="w-full bg-[#1F0206] border border-gold-500/30 rounded-xl px-4 py-3 text-white placeholder-stone-500 focus:outline-none focus:border-gold-400 focus:ring-1 focus:ring-gold-400 transition text-sm">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gold-200 uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="password" required value="password123"
                        class="w-full bg-[#1F0206] border border-gold-500/30 rounded-xl px-4 py-3 text-white placeholder-stone-500 focus:outline-none focus:border-gold-400 focus:ring-1 focus:ring-gold-400 transition text-sm">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center gap-2 cursor-pointer text-stone-300">
                        <input type="checkbox" name="remember" class="rounded bg-[#1F0206] border-stone-700 text-gold-500 focus:ring-gold-400">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-gold-500 to-gold-600 hover:from-gold-400 hover:to-gold-500 text-burgundy-950 font-bold py-3.5 px-4 rounded-xl shadow-lg transition text-xs uppercase tracking-wider">
                    Masuk ke Backoffice
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-gold-500/20 text-center text-xs text-stone-400">
                <a href="{{ route('home') }}" class="hover:text-gold-300 transition">&larr; Kembali ke Halaman Utama Toko</a>
            </div>
        </div>
    </div>
</body>
</html>
