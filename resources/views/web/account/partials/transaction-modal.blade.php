<!-- 1. DETAIL TRANSAKSI POPUP MODAL (TOCO / MARKETPLACE STYLE - z-50) -->
<div id="transactionDetailModal" 
     class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs transition-opacity duration-200 opacity-0 pointer-events-none"
     role="dialog" aria-modal="true" aria-labelledby="transactionModalTitle">
    
    <!-- Modal Dialog Window -->
    <div id="transactionModalDialog" 
         class="relative w-full max-w-3xl transform transition-all duration-200 scale-95 opacity-0">
        
        <!-- Dynamic Content Injected Here -->
        <div id="transactionModalContent">
            <!-- Loading Skeleton Shimmer -->
            <div class="bg-white rounded-3xl p-7 shadow-2xl border border-stone-200 space-y-6 animate-pulse">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                    <div class="h-6 w-40 bg-stone-200 rounded-lg"></div>
                    <div class="w-8 h-8 rounded-full bg-stone-100"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-7 space-y-4">
                        <div class="space-y-2">
                            <div class="h-4 bg-stone-100 rounded w-full"></div>
                            <div class="h-4 bg-stone-100 rounded w-3/4"></div>
                            <div class="h-4 bg-stone-100 rounded w-5/6"></div>
                        </div>
                        <div class="h-28 bg-stone-100 rounded-2xl"></div>
                        <div class="h-20 bg-stone-100 rounded-2xl"></div>
                    </div>
                    <div class="md:col-span-5 space-y-4">
                        <div class="h-24 bg-stone-100 rounded-xl"></div>
                        <div class="h-20 bg-stone-100 rounded-xl"></div>
                        <div class="h-10 bg-stone-200 rounded-xl"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- 2. LACAK PESANAN POPUP MODAL (z-[60] - STACKED ON TOP OF DETAIL TRANSAKSI) -->
<div id="trackingDetailModal" 
     class="fixed inset-0 z-[60] flex items-center justify-center p-3 sm:p-4 bg-black/60 backdrop-blur-xs transition-opacity duration-200 opacity-0 pointer-events-none"
     role="dialog" aria-modal="true" aria-labelledby="trackingModalTitle">
    
    <!-- Tracking Modal Dialog Window -->
    <div id="trackingModalDialog" 
         class="relative w-full max-w-2xl lg:max-w-3xl transform transition-all duration-200 scale-95 opacity-0">
        
        <!-- Dynamic Content Injected Here -->
        <div id="trackingModalContent">
            <!-- Loading Skeleton Shimmer -->
            <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-2xl border border-stone-200 space-y-6 animate-pulse">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                    <div class="h-6 w-36 bg-stone-200 rounded-lg"></div>
                    <div class="w-8 h-8 rounded-full bg-stone-100"></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-5 h-44 bg-stone-100 rounded-2xl"></div>
                    <div class="md:col-span-7 space-y-4">
                        <div class="h-12 bg-stone-100 rounded-xl"></div>
                        <div class="h-12 bg-stone-100 rounded-xl"></div>
                        <div class="h-12 bg-stone-100 rounded-xl"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    let currentModalOrder = null;

    function openTransactionModal(orderNumber) {
        if (!orderNumber) return;
        currentModalOrder = orderNumber;

        const modal = document.getElementById('transactionDetailModal');
        const dialog = document.getElementById('transactionModalDialog');
        const contentContainer = document.getElementById('transactionModalContent');

        if (!modal || !dialog || !contentContainer) return;

        // Tampilkan modal dan animasi pop
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        dialog.classList.remove('scale-95', 'opacity-0');
        dialog.classList.add('scale-100', 'opacity-100');
        document.body.style.overflow = 'hidden';

        // Skeleton Shimmer loader
        contentContainer.innerHTML = `
            <div class="bg-white rounded-3xl p-7 shadow-2xl border border-stone-200 space-y-6 animate-pulse">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                    <div class="h-6 w-44 bg-stone-200 rounded-lg"></div>
                    <button type="button" onclick="closeTransactionModal()" class="w-8 h-8 rounded-full bg-stone-100 text-stone-500 font-bold">&times;</button>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <div class="lg:col-span-7 space-y-4">
                        <div class="h-4 bg-stone-100 rounded w-full"></div>
                        <div class="h-4 bg-stone-100 rounded w-3/4"></div>
                        <div class="h-28 bg-stone-100 rounded-2xl"></div>
                    </div>
                    <div class="lg:col-span-5 space-y-4">
                        <div class="h-20 bg-stone-100 rounded-xl"></div>
                        <div class="h-10 bg-amber-200 rounded-xl"></div>
                    </div>
                </div>
            </div>
        `;

        fetch(`/account/orders/${orderNumber}?modal=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Gagal memuat detail transaksi.');
            return response.text();
        })
        .then(html => {
            contentContainer.innerHTML = html;
        })
        .catch(err => {
            contentContainer.innerHTML = `
                <div class="bg-white rounded-3xl p-8 text-center space-y-4 shadow-2xl border border-stone-200">
                    <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto text-xl font-bold">!</div>
                    <h4 class="font-bold text-stone-900 text-base">Gagal Memuat Transaksi</h4>
                    <p class="text-xs text-stone-500">${err.message || 'Terjadi gangguan saat mengambil data pesanan.'}</p>
                    <div class="pt-2 flex justify-center gap-3">
                        <button type="button" onclick="openTransactionModal('${orderNumber}')" class="px-4 py-2 rounded-xl bg-stone-900 text-white font-bold text-xs">Coba Lagi</button>
                        <button type="button" onclick="closeTransactionModal()" class="px-4 py-2 rounded-xl border border-stone-200 text-stone-700 font-bold text-xs">Tutup</button>
                    </div>
                </div>
            `;
        });
    }

    function closeTransactionModal() {
        const modal = document.getElementById('transactionDetailModal');
        const dialog = document.getElementById('transactionModalDialog');
        if (!modal || !dialog) return;

        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
        dialog.classList.remove('scale-100', 'opacity-100');
        dialog.classList.add('scale-95', 'opacity-0');

        // Kembalikan overflow scroll hanya jika tracking modal juga tertutup
        const trackingModal = document.getElementById('trackingDetailModal');
        if (!trackingModal || trackingModal.classList.contains('opacity-0')) {
            document.body.style.overflow = '';
        }
        currentModalOrder = null;
    }

    // FUNGSI UNTUK MEMBUKA POPUP LACAK PESANAN (STACKED MODAL)
    function openTrackingModal(orderNumber) {
        const targetOrder = orderNumber || currentModalOrder;
        if (!targetOrder) return;

        const modal = document.getElementById('trackingDetailModal');
        const dialog = document.getElementById('trackingModalDialog');
        const contentContainer = document.getElementById('trackingModalContent');

        if (!modal || !dialog || !contentContainer) return;

        // Tampilkan modal lacak di atas detail transaksi
        modal.classList.remove('opacity-0', 'pointer-events-none');
        modal.classList.add('opacity-100');
        dialog.classList.remove('scale-95', 'opacity-0');
        dialog.classList.add('scale-100', 'opacity-100');
        document.body.style.overflow = 'hidden';

        // Skeleton Shimmer loader
        contentContainer.innerHTML = `
            <div class="bg-white rounded-3xl p-6 sm:p-7 shadow-2xl border border-stone-200 space-y-6 animate-pulse">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                    <div class="h-6 w-36 bg-stone-200 rounded-lg"></div>
                    <button type="button" onclick="closeTrackingModal()" class="w-8 h-8 rounded-full bg-stone-100 text-stone-500 font-bold">&times;</button>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="md:col-span-5 h-44 bg-stone-100 rounded-2xl"></div>
                    <div class="md:col-span-7 space-y-4">
                        <div class="h-12 bg-stone-100 rounded-xl"></div>
                        <div class="h-12 bg-stone-100 rounded-xl"></div>
                        <div class="h-12 bg-stone-100 rounded-xl"></div>
                    </div>
                </div>
            </div>
        `;

        fetch(`/account/orders/${targetOrder}?tracking_modal=1`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Gagal memuat informasi pelacakan.');
            return response.text();
        })
        .then(html => {
            contentContainer.innerHTML = html;
        })
        .catch(err => {
            contentContainer.innerHTML = `
                <div class="bg-white rounded-3xl p-8 text-center space-y-4 shadow-2xl border border-stone-200">
                    <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto text-xl font-bold">!</div>
                    <h4 class="font-bold text-stone-900 text-base">Gagal Memuat Lacak Pesanan</h4>
                    <p class="text-xs text-stone-500">${err.message || 'Terjadi gangguan saat mengambil data lacak pengiriman.'}</p>
                    <div class="pt-2 flex justify-center gap-3">
                        <button type="button" onclick="openTrackingModal('${targetOrder}')" class="px-4 py-2 rounded-xl bg-stone-900 text-white font-bold text-xs">Coba Lagi</button>
                        <button type="button" onclick="closeTrackingModal()" class="px-4 py-2 rounded-xl border border-stone-200 text-stone-700 font-bold text-xs">Tutup</button>
                    </div>
                </div>
            `;
        });
    }

    function closeTrackingModal() {
        const modal = document.getElementById('trackingDetailModal');
        const dialog = document.getElementById('trackingModalDialog');
        if (!modal || !dialog) return;

        modal.classList.remove('opacity-100');
        modal.classList.add('opacity-0', 'pointer-events-none');
        dialog.classList.remove('scale-100', 'opacity-100');
        dialog.classList.add('scale-95', 'opacity-0');

        // Jika transactionDetailModal masih terbuka, pertahankan scroll lock
        const transactionModal = document.getElementById('transactionDetailModal');
        if (!transactionModal || transactionModal.classList.contains('opacity-0')) {
            document.body.style.overflow = '';
        }
    }

    // Event listener backdrop dan keyboard
    document.addEventListener('DOMContentLoaded', () => {
        const transactionModal = document.getElementById('transactionDetailModal');
        if (transactionModal) {
            transactionModal.addEventListener('click', (e) => {
                if (e.target === transactionModal) {
                    closeTransactionModal();
                }
            });
        }

        const trackingModal = document.getElementById('trackingDetailModal');
        if (trackingModal) {
            trackingModal.addEventListener('click', (e) => {
                if (e.target === trackingModal) {
                    closeTrackingModal();
                }
            });
        }

        // Tutup modal paling atas jika menekan tombol Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const trackingModal = document.getElementById('trackingDetailModal');
                if (trackingModal && !trackingModal.classList.contains('opacity-0')) {
                    closeTrackingModal();
                    return;
                }
                const transactionModal = document.getElementById('transactionDetailModal');
                if (transactionModal && !transactionModal.classList.contains('opacity-0')) {
                    closeTransactionModal();
                }
            }
        });

        // Buka otomatis jika ada parameter URL ?open=AP-...
        const urlParams = new URLSearchParams(window.location.search);
        const openOrder = urlParams.get('open');
        const openTrack = urlParams.get('track');
        if (openOrder) {
            openTransactionModal(openOrder);
            if (openTrack) {
                setTimeout(() => openTrackingModal(openOrder), 300);
            }
        }
    });
</script>
