@extends('layouts.app')

@section('title', 'Checkout - Aroma Palace')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
     x-data="checkoutApp(@js($preview), @js($addresses), @js($stores))"
     x-cloak>

    <div class="border-b border-gray-200 pb-6">
        <span class="text-xs uppercase tracking-widest text-gray-400 font-semibold block mb-1">Final Step</span>
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Checkout</h1>
        <p class="text-xs text-gray-500 mt-1">Safe and secure payments powered by <strong>Pakasir</strong> & delivery via <strong>KiriminAja</strong>.</p>
    </div>

    <form method="POST" action="{{ route('checkout.process') }}" @submit="submitOrder($event)">
        @csrf
        <input type="hidden" name="fulfillment_type" :value="fulfillmentType">
        <input type="hidden" name="address_id" :value="fulfillmentType === 'home_delivery' ? selectedAddressId : ''">
        <input type="hidden" name="store_id" :value="fulfillmentType === 'store_pickup' ? selectedStoreId : ''">
        <input type="hidden" name="shipping_courier" :value="selectedCourier">
        <input type="hidden" name="shipping_service" :value="selectedService">
        <input type="hidden" name="payment_method" :value="selectedPaymentMethod">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Fulfillment, Courier, Payment (2 Cols) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- 1. Fulfillment Type Selection -->
                <div class="bg-white rounded-xl p-6 sm:p-8 border border-gray-200 shadow-2xs space-y-4">
                    <h3 class="text-base font-bold text-gray-900">1. Delivery Method</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="border-2 rounded-lg p-4 cursor-pointer transition flex items-start gap-3"
                               :class="fulfillmentType === 'home_delivery' ? 'border-[#650506] bg-rose-50/20 ring-1 ring-[#650506]' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="fulfillment_option" value="home_delivery" x-model="fulfillmentType" @change="recalculate()" class="text-[#650506] focus:ring-[#650506] mt-1">
                            <div>
                                <h4 class="font-bold text-sm text-gray-900">Home Delivery (Courier)</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Express shipping via KiriminAja (JNE, SiCepat, J&T, Anteraja).</p>
                            </div>
                        </label>

                        <label class="border-2 rounded-lg p-4 cursor-pointer transition flex items-start gap-3"
                               :class="fulfillmentType === 'store_pickup' ? 'border-[#650506] bg-rose-50/20 ring-1 ring-[#650506]' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="fulfillment_option" value="store_pickup" x-model="fulfillmentType" @change="recalculate()" class="text-[#650506] focus:ring-[#650506] mt-1">
                            <div>
                                <h4 class="font-bold text-sm text-gray-900">Store Pickup</h4>
                                <p class="text-xs text-gray-500 mt-0.5">Free shipping. Collect at your nearest store location within 2 hours.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- 2A. Address Selection & KiriminAja Couriers (If Delivery) -->
                <div x-show="fulfillmentType === 'home_delivery'" class="space-y-6">
                    <!-- Address Selection -->
                    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-bold text-gray-900">2. Shipping Address</h3>
                            <a href="{{ route('account.addresses') }}" class="text-xs font-semibold text-gray-600 hover:text-black">+ Manage Addresses</a>
                        </div>

                        <template x-if="addresses.length > 0">
                            <div class="space-y-3">
                                <template x-for="addr in addresses" :key="addr.id">
                                    <label class="border rounded-xl p-4 cursor-pointer transition flex items-start gap-3 block"
                                           :class="selectedAddressId == addr.id ? 'border-black bg-gray-50 ring-1 ring-black' : 'border-gray-200 hover:border-gray-300'">
                                        <input type="radio" name="address_radio" :value="addr.id" x-model="selectedAddressId" @change="recalculate()" class="text-black focus:ring-black mt-1">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="font-bold text-sm text-gray-900" x-text="addr.recipient_name"></span>
                                                <span class="text-[10px] uppercase font-semibold px-2 py-0.5 rounded bg-gray-100 text-gray-700" x-text="addr.label"></span>
                                                <template x-if="addr.is_primary">
                                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-black text-white">Default</span>
                                                </template>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-0.5" x-text="addr.phone_number"></p>
                                            <p class="text-xs text-gray-600 mt-1" x-text="addr.full_address + ', ' + addr.city + ' ' + (addr.postal_code || '')"></p>
                                        </div>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <template x-if="addresses.length === 0">
                            <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-xs text-gray-700 flex items-center justify-between">
                                <span>You have not saved any delivery address.</span>
                                <a href="{{ route('account.addresses') }}" class="font-bold underline text-black">Add Address &rarr;</a>
                            </div>
                        </template>
                    </div>

                    <!-- Courier Selection Card -->
                    <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200 shadow-2xs space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-bold text-gray-900">3. Select Courier</h3>
                                <p class="text-xs text-gray-500">Live rates calculated via KiriminAja.</p>
                            </div>
                            <span class="text-[10px] bg-gray-100 text-gray-800 border border-gray-200 font-semibold px-2.5 py-1 rounded-full uppercase tracking-wider">
                                KiriminAja
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" x-show="!loadingRates">
                            <template x-for="(courier, idx) in availableCouriers" :key="idx">
                                <label class="border rounded-xl p-4 cursor-pointer transition flex items-start justify-between gap-3"
                                       :class="(selectedCourier === courier.courier && selectedService === courier.service) ? 'border-black bg-gray-50 ring-1 ring-black' : 'border-gray-200 hover:border-gray-300'">
                                    <div class="flex items-start gap-3">
                                        <input type="radio" name="courier_option"
                                               :checked="selectedCourier === courier.courier && selectedService === courier.service"
                                               @change="selectCourier(courier.courier, courier.service, courier.cost, courier.etd)"
                                               class="text-black focus:ring-black mt-1">
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-xs text-gray-900" x-text="courier.service_name || (courier.courier + ' ' + courier.service)"></span>
                                            </div>
                                            <span class="text-[11px] text-gray-400 block mt-0.5" x-text="'Estimated: ' + courier.etd"></span>
                                        </div>
                                    </div>
                                    <span class="font-mono font-bold text-xs text-gray-900" x-text="$money(courier.cost)"></span>
                                </label>
                            </template>
                        </div>

                        <div x-show="loadingRates" class="py-8 text-center text-xs text-gray-500">
                            Calculating shipping rates...
                        </div>
                    </div>
                </div>

                <!-- 2B. Store Selection (If Pickup) -->
                <div x-show="fulfillmentType === 'store_pickup'" class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200 shadow-2xs space-y-4">
                    <h3 class="text-base font-bold text-gray-900">2. Select Pickup Store</h3>
                    <div class="space-y-3">
                        <template x-for="st in stores" :key="st.id">
                            <label class="border rounded-xl p-4 cursor-pointer transition flex items-start gap-3 block"
                                   :class="selectedStoreId == st.id ? 'border-black bg-gray-50 ring-1 ring-black' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" name="store_radio" :value="st.id" x-model="selectedStoreId" class="text-black focus:ring-black mt-1">
                                <div>
                                    <h4 class="font-bold text-sm text-gray-900" x-text="st.name"></h4>
                                    <p class="text-xs text-gray-600 mt-0.5" x-text="st.address + ', ' + st.city"></p>
                                    <p class="text-xs text-gray-500 mt-1" x-text="'Hours: ' + (st.operating_hours || '10:00 - 22:00')"></p>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- 4. Payment Method Selection (Powered by Pakasir) -->
                <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200 shadow-2xs space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-900">4. Payment Method</h3>
                            <p class="text-xs text-gray-500">Automated verification powered by Pakasir Gateway.</p>
                        </div>
                        <span class="text-[10px] bg-gray-100 text-gray-800 border border-gray-200 font-semibold px-2.5 py-1 rounded-full uppercase tracking-wider">
                            Pakasir Secured
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <template x-for="pm in paymentMethods" :key="pm.code">
                            <label class="border rounded-xl p-4 cursor-pointer transition flex items-center justify-between gap-3"
                                   :class="selectedPaymentMethod === pm.code ? 'border-black bg-gray-50 ring-1 ring-black' : 'border-gray-200 hover:border-gray-300'">
                                <div class="flex items-center gap-3">
                                    <input type="radio" name="payment_method_radio" :value="pm.code" x-model="selectedPaymentMethod" class="text-black focus:ring-black">
                                    <div>
                                        <span class="font-bold text-xs text-gray-900 block" x-text="pm.name"></span>
                                        <span class="text-[10px] text-gray-400" x-text="pm.provider ? 'Via ' + pm.provider : 'Instant Confirmation'"></span>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-400">●</span>
                            </label>
                        </template>
                    </div>
                </div>

                <!-- Notes / Catatan -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-2xs space-y-2">
                    <label class="block text-xs font-bold text-gray-800">Delivery Notes (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="Instructions for courier or order notes..."
                              class="w-full text-xs p-3 rounded-lg border border-gray-300 focus:outline-none focus:border-black"></textarea>
                </div>
            </div>

            <!-- Right Column: Order Summary (1 Col) -->
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-gray-200 shadow-2xs space-y-6 h-fit sticky top-28">
                <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3">Order Summary</h3>

                <div class="space-y-3 max-h-60 overflow-y-auto divide-y divide-gray-100">
                    <template x-for="item in items" :key="item.product_id">
                        <div class="pt-2.5 first:pt-0 flex justify-between gap-3 text-xs">
                            <div>
                                <h5 class="font-bold text-gray-900" x-text="item.product_name"></h5>
                                <span class="text-gray-400" x-text="(item.variant_name || 'Standard') + ' × ' + item.quantity"></span>
                            </div>
                            <span class="font-mono font-bold text-gray-900" x-text="$money(item.subtotal)"></span>
                        </div>
                    </template>
                </div>

                <div class="border-t border-gray-100 pt-4 space-y-2.5 text-xs text-gray-600">
                    <div class="flex justify-between items-center">
                        <span>Items Subtotal</span>
                        <span class="font-bold text-gray-900 font-mono" x-text="$money(subtotal)"></span>
                    </div>

                    <div class="flex justify-between items-center">
                        <div>
                            <span>Shipping</span>
                            <span class="text-[11px] text-gray-400 block" x-text="fulfillmentType === 'home_delivery' ? (selectedCourier + ' ' + selectedService) : 'Store Pickup'"></span>
                        </div>
                        <span class="font-bold text-gray-900 font-mono" x-text="fulfillmentType === 'home_delivery' ? $money(shippingCost) : 'FREE'"></span>
                    </div>

                    <template x-if="discountAmount > 0">
                        <div class="flex justify-between items-center text-emerald-700 font-semibold">
                            <span>Discount</span>
                            <span class="font-mono" x-text="'- ' + $money(discountAmount)"></span>
                        </div>
                    </template>

                    <div class="border-t border-gray-200 pt-3 flex justify-between items-baseline text-sm">
                        <span class="font-bold text-gray-900">Total</span>
                        <span class="text-2xl font-extrabold text-gray-900" x-text="$money(totalAmount)"></span>
                    </div>
                </div>

                <button type="submit"
                        :disabled="submitting || (fulfillmentType === 'home_delivery' && !selectedAddressId)"
                        class="w-full bg-[#111827] hover:bg-black text-white font-medium py-3.5 px-4 rounded-xl shadow-sm transition text-xs uppercase tracking-wider flex items-center justify-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed">
                    <span x-show="submitting" class="animate-spin text-xs">⏳</span>
                    <span x-text="submitting ? 'Connecting to Payment...' : 'Complete Order'"></span>
                </button>

                <div class="text-[11px] text-gray-400 text-center leading-relaxed">
                    🔒 Secure checkout powered by <strong>Pakasir</strong>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function checkoutApp(initialPreview, initialAddresses, initialStores) {
    return {
        items: initialPreview?.items || [],
        addresses: initialAddresses || [],
        stores: initialStores || [],
        fulfillmentType: initialPreview?.fulfillment_type || 'home_delivery',
        selectedAddressId: initialPreview?.selected_address?.id || (initialAddresses[0]?.id || null),
        selectedStoreId: initialPreview?.selected_store?.id || (initialStores[0]?.id || null),
        availableCouriers: initialPreview?.available_couriers || [],
        selectedCourier: initialPreview?.shipping?.courier || 'JNE',
        selectedService: initialPreview?.shipping?.service || 'REG',
        shippingCost: initialPreview?.shipping_cost || 0,
        subtotal: initialPreview?.subtotal || 0,
        discountAmount: initialPreview?.discount_amount || 0,
        totalAmount: initialPreview?.total_amount || 0,
        paymentMethods: initialPreview?.available_payment_methods || [],
        selectedPaymentMethod: 'qris',
        loadingRates: false,
        submitting: false,

        selectCourier(courier, service, cost, etd) {
            this.selectedCourier = courier;
            this.selectedService = service;
            this.shippingCost = cost;
            this.recalculate();
        },

        async recalculate() {
            this.loadingRates = true;
            try {
                const res = await fetch('/checkout/calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({
                        fulfillment_type: this.fulfillmentType,
                        address_id: this.selectedAddressId,
                        store_id: this.selectedStoreId,
                        shipping_courier: this.selectedCourier,
                        shipping_service: this.selectedService,
                    })
                });

                const json = await res.json();
                if (json.success && json.data) {
                    const data = json.data;
                    this.subtotal = data.subtotal;
                    this.shippingCost = data.shipping_cost;
                    this.discountAmount = data.discount_amount;
                    this.totalAmount = data.total_amount;
                    if (data.available_couriers && data.available_couriers.length > 0) {
                        this.availableCouriers = data.available_couriers;
                    }
                }
            } catch (e) {
                console.error('Recalculate error:', e);
            } finally {
                this.loadingRates = false;
            }
        },

        submitOrder(event) {
            this.submitting = true;
        }
    };
}
</script>
@endsection
