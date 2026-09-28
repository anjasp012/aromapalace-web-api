@extends('layouts.app')

@section('title', 'Address Book - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8 space-y-4 sm:space-y-6 pb-6 sm:pb-8">
    <!-- Breadcrumb & Header -->
    <div>
        <div class="flex items-center gap-2 text-[11px] sm:text-xs text-gray-500 mb-1.5 sm:mb-2">
            <a href="{{ route('home') }}" class="hover:text-[#650506]">Home</a>
            <span>/</span>
            <a href="{{ route('account.index') }}" class="hover:text-[#650506]">Account</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">Address Book</span>
        </div>
        <h1 class="text-xl sm:text-2xl lg:text-3xl font-serif font-bold text-gray-900 tracking-tight">Address Book</h1>
        <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5 sm:mt-1">Manage your delivery addresses for faster checkout.</p>
    </div>

    <!-- Navigation Tabs with Integrated Logout -->
    <div class="flex items-center justify-between gap-2 border-b border-gray-200 pb-2.5 sm:pb-3">
        <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto min-w-0 flex-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden text-xs sm:text-sm font-medium">
            <a href="{{ route('account.index') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-black transition shrink-0">Account Overview</a>
            <a href="{{ route('account.orders') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-black transition shrink-0">Orders History</a>
            <a href="{{ route('account.rewards') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-black transition shrink-0">Rewards</a>
            <a href="{{ route('account.addresses') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg bg-[#650506] text-white font-semibold shadow-xs shrink-0">Address Book</a>
            <a href="{{ route('account.points') }}" class="px-3 sm:px-4 py-1.5 sm:py-2 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-black transition shrink-0">Riwayat Poin</a>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="shrink-0">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1 sm:gap-1.5 px-2.5 sm:px-3 py-1.5 sm:py-2 rounded-lg border border-gray-200 hover:border-rose-200 hover:bg-rose-50 text-gray-500 hover:text-rose-600 text-[11px] sm:text-xs font-semibold transition cursor-pointer shrink-0" title="Keluar dari akun">
                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-400 group-hover:text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>

    @if($errors->any())
        <div class="p-3 sm:p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
            <span class="font-bold block">Please check your input:</span>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
        <!-- Saved Addresses (2 Cols) -->
        <div class="lg:col-span-2 space-y-3 sm:space-y-4">
            <h3 class="font-bold text-gray-900 text-sm sm:text-base">Saved Addresses ({{ $addresses->count() }})</h3>

            <div class="space-y-3 sm:space-y-4">
                @forelse($addresses as $address)
                    <div class="bg-white rounded-xl sm:rounded-2xl p-3.5 sm:p-5 border {{ $address->is_primary ? 'border-[#650506] shadow-xs ring-1 ring-[#650506]' : 'border-gray-200' }} shadow-2xs relative transition">
                        <div class="flex items-start justify-between gap-3 sm:gap-4">
                            <div>
                                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                                    <span class="font-bold text-gray-900 text-xs sm:text-sm">{{ $address->recipient_name }}</span>
                                    <span class="text-gray-400 text-xs">&bull; {{ $address->phone_number }}</span>
                                    <span class="text-[9px] sm:text-[10px] bg-gray-100 text-gray-700 px-1.5 sm:px-2 py-0.5 rounded font-semibold uppercase tracking-wider">
                                        {{ $address->label }}
                                    </span>
                                    @if($address->is_primary)
                                        <span class="text-[9px] sm:text-[10px] bg-[#650506] text-white px-1.5 sm:px-2 py-0.5 rounded font-semibold uppercase tracking-wider">
                                            Default
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] sm:text-xs text-gray-600 mt-1.5 sm:mt-2 leading-relaxed">
                                    {{ $address->full_address }}<br>
                                    <span class="font-semibold text-gray-800">{{ $address->city }}</span>, {{ $address->postal_code }}
                                </p>
                                @if($address->notes)
                                    <p class="text-[10px] sm:text-[11px] text-gray-400 mt-1 italic">Note: {{ $address->notes }}</p>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('account.addresses.destroy', $address->id) }}" onsubmit="return confirm('Are you sure you want to delete this address?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-rose-600 p-1.5 rounded-lg hover:bg-rose-50 transition" title="Delete Address">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl sm:rounded-2xl p-6 sm:p-8 border border-gray-200 text-center">
                        <span class="text-2xl sm:text-3xl block mb-2">📍</span>
                        <p class="text-xs text-gray-400">No saved addresses yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Add Address Form (1 Col) -->
        <div class="bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 lg:p-8 border border-gray-200 shadow-2xs space-y-3.5 sm:space-y-5">
            <h3 class="font-bold text-gray-900 text-sm sm:text-base">Add New Address</h3>

            <form method="POST" action="{{ route('account.addresses.store') }}" class="space-y-3 sm:space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Address Label</label>
                    <input type="text" name="label" value="{{ old('label', 'Home') }}" placeholder="e.g. Home, Office, Apartment"
                           class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Recipient Name *</label>
                    <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" required placeholder="Full Name"
                           class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Phone Number *</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" required placeholder="08123456789"
                           class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">
                </div>

                <div class="grid grid-cols-2 gap-2.5 sm:gap-3">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">City / Region *</label>
                        <input type="text" name="city" value="{{ old('city') }}" required placeholder="Jakarta Selatan"
                               class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Postal Code</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code') }}" placeholder="12190"
                               class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Full Address *</label>
                    <textarea name="full_address" rows="3" required placeholder="Street name, building, apartment number..."
                              class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">{{ old('full_address') }}</textarea>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Landmark / Delivery Note</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Next to..."
                           class="w-full px-3 sm:px-3.5 py-2 sm:py-2.5 rounded-lg border border-gray-300 focus:outline-none focus:border-[#650506]">
                </div>

                <button type="submit" class="w-full py-2.5 sm:py-3 rounded-lg bg-[#650506] hover:bg-[#4A070B] text-white font-medium text-xs uppercase tracking-wider transition shadow-xs">
                    Save Address
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
