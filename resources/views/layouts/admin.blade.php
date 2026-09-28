<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Backoffice') - Aroma Palace</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <!-- Production Assets via Vite (Tailwind CSS v4) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FAF8F5] font-sans text-stone-800 antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar Navigation -->
        <aside class="w-64 bg-[#38050D] text-gold-100 flex flex-col justify-between shrink-0 shadow-2xl border-r border-gold-500/20">
            <div>
                <!-- Brand Header with Logo -->
                <div class="px-5 py-5 border-b border-gold-500/20 flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Aroma Palace Logo" class="w-11 h-11 rounded-full object-contain ring-2 ring-gold-400/60 shadow-[0_0_10px_rgba(195,154,43,0.3)]">
                    <div>
                        <h1 class="text-sm font-serif font-bold tracking-wider text-white">AROMA PALACE</h1>
                        <span class="text-[9px] uppercase tracking-widest text-gold-400 font-semibold block">Admin Portal</span>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="p-3.5 space-y-1.5 text-sm font-medium">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.dashboard*') ? 'bg-gold-500 text-burgundy-950 font-bold shadow-sm' : 'text-gold-200/75 hover:text-white hover:bg-[#520813]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>

                    <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.orders*') ? 'bg-gold-500 text-burgundy-950 font-bold shadow-sm' : 'text-gold-200/75 hover:text-white hover:bg-[#520813]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        Pesanan & Resi
                    </a>

                    <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.products*') ? 'bg-gold-500 text-burgundy-950 font-bold shadow-sm' : 'text-gold-200/75 hover:text-white hover:bg-[#520813]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        Katalog Parfum
                    </a>

                    <a href="{{ route('admin.promotions.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.promotions*') ? 'bg-gold-500 text-burgundy-950 font-bold shadow-sm' : 'text-gold-200/75 hover:text-white hover:bg-[#520813]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Voucher & Promo
                    </a>

                    <a href="{{ route('admin.stores.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.stores*') ? 'bg-gold-500 text-burgundy-950 font-bold shadow-sm' : 'text-gold-200/75 hover:text-white hover:bg-[#520813]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Gerai Toko
                    </a>

                    <a href="{{ route('admin.articles.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition {{ request()->routeIs('admin.articles*') ? 'bg-gold-500 text-burgundy-950 font-bold shadow-sm' : 'text-gold-200/75 hover:text-white hover:bg-[#520813]' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        Beauty Guide
                    </a>
                </nav>
            </div>

            <!-- Admin Profile & Logout -->
            <div class="p-4 border-t border-gold-500/20 bg-[#2b040a]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ auth()->user()->avatar ?? 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&w=150&q=80' }}" class="w-9 h-9 rounded-full object-cover ring-1 ring-gold-400" alt="Avatar">
                        <div>
                            <p class="text-xs font-bold text-white leading-tight">{{ auth()->user()->name }}</p>
                            <span class="text-[10px] text-gold-400 font-medium">Administrator</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" title="Logout" class="p-1.5 text-gold-300/60 hover:text-rose-400 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-y-auto">
            <!-- Top Navbar -->
            <header class="bg-white border-b border-stone-200/80 px-8 py-4 flex items-center justify-between sticky top-0 z-10 shadow-xs">
                <div class="flex items-center gap-4">
                    <h2 class="text-xl font-bold font-serif text-stone-900">@yield('title')</h2>
                </div>
                <div class="flex items-center gap-4">
                    <a href="{{ route('home') }}" target="_blank" class="text-xs font-semibold text-burgundy-900 bg-gold-100 border border-gold-300 px-3 py-1.5 rounded-full hover:bg-gold-200 transition flex items-center gap-1.5">
                        <span>Lihat Website Toko</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </header>

            <!-- Alerts -->
            <div class="px-8 pt-6">
                @if(session('success'))
                    <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg flex items-center gap-2 shadow-sm">
                        <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-lg flex items-center gap-2 shadow-sm">
                        <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
            </div>

            <!-- Page Body -->
            <div class="p-8">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>

