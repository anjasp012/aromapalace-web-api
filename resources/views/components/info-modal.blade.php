<!-- Customer Service & Policy Modal (FAQs, Shipping, Returns, Privacy, Terms) -->
<div x-data="{
        isOpen: false,
        activeTab: 'faq',
        selectedFaq: null,
        openModal(tab = 'faq') {
            this.activeTab = tab;
            this.isOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        closeModal() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
            if (['#faq', '#shipping', '#returns', '#privacy', '#terms'].includes(window.location.hash)) {
                history.replaceState(null, null, ' ');
            }
        },
        toggleFaq(index) {
            this.selectedFaq = this.selectedFaq === index ? null : index;
        },
        init() {
            window.openInfoModal = (tab) => this.openModal(tab);
            window.addEventListener('open-info-modal', (e) => this.openModal(e.detail || 'faq'));

            // Open if hash is present in URL
            const hash = window.location.hash.replace('#', '');
            if (['faq', 'shipping', 'returns', 'privacy', 'terms'].includes(hash)) {
                this.openModal(hash);
            }
        }
    }"
    @keydown.window.escape="if(isOpen) closeModal()"
    id="info-policy-modal"
    x-cloak>

    <!-- Backdrop Overlay -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-5"
         @click.self="closeModal()"
         style="display: none;">

        <!-- Modal Window Container -->
        <div x-show="isOpen"
             x-transition:enter="transition ease-out duration-250"
             x-transition:enter-start="opacity-0 scale-95 translate-y-3"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-3"
             @click.stop
             class="w-full max-w-3xl bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col max-h-[90vh]">

            <!-- Modal Header -->
            <div class="px-4 sm:px-6 py-3.5 sm:py-4 border-b border-gray-100 flex items-center justify-between bg-stone-50/70 shrink-0">
                <div class="flex items-center gap-2 sm:gap-2.5">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#650506]/10 text-[#650506] flex items-center justify-center font-bold text-xs">
                        ✦
                    </div>
                    <div>
                        <h3 class="text-sm sm:text-base font-bold text-gray-900 leading-tight">Pusat Informasi &amp; Kebijakan</h3>
                        <p class="text-[10px] sm:text-xs text-gray-500">Aroma Palace &bull; Dubai's Signature Perfumes</p>
                    </div>
                </div>

                <!-- Close Button -->
                <button @click="closeModal()" 
                        type="button" 
                        class="p-1.5 sm:p-2 rounded-full text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition focus:outline-none"
                        title="Tutup (ESC)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Tab Navigation Header (Scrollable horizontally on mobile) -->
            <div class="flex items-center gap-1 sm:gap-2 px-4 sm:px-6 pt-2 pb-2 border-b border-gray-100 overflow-x-auto no-scrollbar bg-white shrink-0">
                <!-- Tab 1: FAQs -->
                <button type="button"
                        @click="activeTab = 'faq'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition flex items-center gap-1.5"
                        :class="activeTab === 'faq' ? 'bg-[#650506] text-white shadow-2xs' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>FAQs</span>
                </button>

                <!-- Tab 2: Shipping Info -->
                <button type="button"
                        @click="activeTab = 'shipping'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition flex items-center gap-1.5"
                        :class="activeTab === 'shipping' ? 'bg-[#650506] text-white shadow-2xs' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    <span>Pengiriman</span>
                </button>

                <!-- Tab 3: Returns & Refunds -->
                <button type="button"
                        @click="activeTab = 'returns'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition flex items-center gap-1.5"
                        :class="activeTab === 'returns' ? 'bg-[#650506] text-white shadow-2xs' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Retur &amp; Garansi</span>
                </button>

                <!-- Tab 4: Privacy Policy -->
                <button type="button"
                        @click="activeTab = 'privacy'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition flex items-center gap-1.5"
                        :class="activeTab === 'privacy' ? 'bg-[#650506] text-white shadow-2xs' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>Kebijakan Privasi</span>
                </button>

                <!-- Tab 5: Terms of Service -->
                <button type="button"
                        @click="activeTab = 'terms'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition flex items-center gap-1.5"
                        :class="activeTab === 'terms' ? 'bg-[#650506] text-white shadow-2xs' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Syarat &amp; Ketentuan</span>
                </button>
            </div>

            <!-- Modal Body (Scrollable) -->
            <div class="p-4 sm:p-6 overflow-y-auto space-y-4 text-xs sm:text-sm text-gray-600 leading-relaxed max-h-[calc(90vh-140px)]">

                <!-- ════ TAB 1: FAQs ════ -->
                <div x-show="activeTab === 'faq'" class="space-y-3">
                    <div class="mb-4">
                        <h4 class="text-base sm:text-lg font-bold text-gray-900">Pertanyaan yang Sering Diajukan (FAQs)</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Temukan jawaban cepat seputar keaslian produk, pengiriman, dan layanan Aroma Palace.</p>
                    </div>

                    <div class="space-y-2.5">
                        <!-- FAQ 1 -->
                        <div class="border border-gray-200 rounded-xl overflow-hidden transition">
                            <button @click="toggleFaq(1)" class="w-full text-left px-4 py-3 bg-stone-50/50 hover:bg-stone-50 flex items-center justify-between gap-3 font-semibold text-gray-800 text-xs sm:text-sm">
                                <span>Apakah seluruh produk parfum di Aroma Palace 100% Original?</span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="selectedFaq === 1 ? 'rotate-180 text-[#650506]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="selectedFaq === 1" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="px-4 py-3 bg-white text-gray-600 border-t border-gray-100 space-y-1.5 text-xs sm:text-sm leading-relaxed">
                                <p><strong>Ya, 100% Original dan Terjamin.</strong> Seluruh koleksi wewangian kami diimpor langsung dari rumah parfum terkemuka di Dubai, Uni Emirat Arab (UEA) dan Prancis dengan batch code yang dapat diverifikasi keasliannya.</p>
                                <p class="text-emerald-700 font-medium">Garansi uang kembali 100% jika terbukti ada produk kami yang tidak orisinil.</p>
                            </div>
                        </div>

                        <!-- FAQ 2 -->
                        <div class="border border-gray-200 rounded-xl overflow-hidden transition">
                            <button @click="toggleFaq(2)" class="w-full text-left px-4 py-3 bg-stone-50/50 hover:bg-stone-50 flex items-center justify-between gap-3 font-semibold text-gray-800 text-xs sm:text-sm">
                                <span>Berapa lama estimasi pengiriman pesanan sampai di alamat saya?</span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="selectedFaq === 2 ? 'rotate-180 text-[#650506]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="selectedFaq === 2" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="px-4 py-3 bg-white text-gray-600 border-t border-gray-100 text-xs sm:text-sm leading-relaxed">
                                <p>Pesanan yang terkonfirmasi sebelum pukul 15.00 WIB dikirim pada hari yang sama. Estimasi tiba:</p>
                                <ul class="list-disc list-inside mt-1.5 space-y-1 text-gray-700">
                                    <li><strong>Jabodetabek &amp; Pulau Jawa:</strong> 1 - 2 hari kerja.</li>
                                    <li><strong>Luar Pulau Jawa (Kota Besar):</strong> 2 - 4 hari kerja.</li>
                                    <li><strong>Wilayah Kepulauan / Terpencil:</strong> 3 - 6 hari kerja.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- FAQ 3 -->
                        <div class="border border-gray-200 rounded-xl overflow-hidden transition">
                            <button @click="toggleFaq(3)" class="w-full text-left px-4 py-3 bg-stone-50/50 hover:bg-stone-50 flex items-center justify-between gap-3 font-semibold text-gray-800 text-xs sm:text-sm">
                                <span>Bagaimana jika botol parfum pecah atau rusak saat diterima?</span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="selectedFaq === 3 ? 'rotate-180 text-[#650506]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="selectedFaq === 3" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="px-4 py-3 bg-white text-gray-600 border-t border-gray-100 text-xs sm:text-sm leading-relaxed">
                                <p>Kami menjamin paket Anda tiba dengan aman. Namun jika terjadi kerusakan fisik atau botol pecah di perjalanan kurir, <strong>kami ganti botol baru 100% tanpa biaya tambahan</strong>. Syaratnya cukup rekam <em>video unboxing utuh tanpa jeda</em> saat pertama kali membuka paket dan hubungi CS WhatsApp kami dalam 2x24 jam.</p>
                            </div>
                        </div>

                        <!-- FAQ 4 -->
                        <div class="border border-gray-200 rounded-xl overflow-hidden transition">
                            <button @click="toggleFaq(4)" class="w-full text-left px-4 py-3 bg-stone-50/50 hover:bg-stone-50 flex items-center justify-between gap-3 font-semibold text-gray-800 text-xs sm:text-sm">
                                <span>Bagaimana cara melacak pesanan saya (Track Order)?</span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="selectedFaq === 4 ? 'rotate-180 text-[#650506]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="selectedFaq === 4" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="px-4 py-3 bg-white text-gray-600 border-t border-gray-100 text-xs sm:text-sm leading-relaxed">
                                <p>Anda dapat mengecek perjalanan paket secara real-time di halaman <a href="{{ route('account.orders') }}" class="text-[#650506] font-semibold underline">Akun Saya &gt; Riwayat Pesanan</a>. Setelah pesanan diserahkan ke kurir, nomor resi otomatis aktif dan dapat dilacak langsung.</p>
                            </div>
                        </div>

                        <!-- FAQ 5 -->
                        <div class="border border-gray-200 rounded-xl overflow-hidden transition">
                            <button @click="toggleFaq(5)" class="w-full text-left px-4 py-3 bg-stone-50/50 hover:bg-stone-50 flex items-center justify-between gap-3 font-semibold text-gray-800 text-xs sm:text-sm">
                                <span>Metode pembayaran apa saja yang tersedia?</span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200" :class="selectedFaq === 5 ? 'rotate-180 text-[#650506]' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="selectedFaq === 5" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="px-4 py-3 bg-white text-gray-600 border-t border-gray-100 text-xs sm:text-sm leading-relaxed">
                                <p>Aroma Palace mendukung pembayaran instan terverifikasi otomatis, meliputi:</p>
                                <ul class="list-disc list-inside mt-1 space-y-0.5 text-gray-700">
                                    <li><strong>QRIS Instan:</strong> GoPay, OVO, Dana, ShopeePay, LinkAja, BCA Mobile, dll.</li>
                                    <li><strong>Virtual Account:</strong> BCA, Mandiri, BNI, BRI, Permata Bank.</li>
                                    <li><strong>Transfer Bank Manual</strong> dengan konfirmasi cepat.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ════ TAB 2: SHIPPING INFO ════ -->
                <div x-show="activeTab === 'shipping'" class="space-y-4">
                    <div>
                        <h4 class="text-base sm:text-lg font-bold text-gray-900">Informasi Pengiriman &amp; Ekspedisi</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Komitmen kami mengantarkan parfum mewah Dubai Anda dengan aman dan cepat.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div class="p-3.5 rounded-xl border border-gray-200 bg-stone-50/50 space-y-1.5">
                            <div class="flex items-center gap-2 text-gray-900 font-bold text-xs sm:text-sm">
                                <span class="text-[#650506]">📦</span> Standar Kemasan Fragrance
                            </div>
                            <p class="text-xs text-gray-600">
                                Botol parfum dibalut 4 lapis bubble wrap tebal, disegel rapat, dan dimasukkan ke dalam corrugated box kokoh anti-benturan berlabel FRAGILE.
                            </p>
                        </div>

                        <div class="p-3.5 rounded-xl border border-gray-200 bg-stone-50/50 space-y-1.5">
                            <div class="flex items-center gap-2 text-gray-900 font-bold text-xs sm:text-sm">
                                <span class="text-[#650506]">⚡</span> Jadwal Pengiriman Hari Sama
                            </div>
                            <p class="text-xs text-gray-600">
                                Pesanan yang diselesaikan sebelum jam 15.00 WIB (Senin - Sabtu) langsung diserahkan ke kurir pada hari yang sama.
                            </p>
                        </div>

                        <div class="p-3.5 rounded-xl border border-gray-200 bg-stone-50/50 space-y-1.5">
                            <div class="flex items-center gap-2 text-gray-900 font-bold text-xs sm:text-sm">
                                <span class="text-[#650506]">🚚</span> Ekspedisi Rekanan Terpercaya
                            </div>
                            <p class="text-xs text-gray-600">
                                Terintegrasi dengan sistem pengiriman KiriminAja: JNE Express, SiCepat Ekspres, J&amp;T Express, dan Ninja Xpress dengan nomor resi otomatis.
                            </p>
                        </div>

                        <div class="p-3.5 rounded-xl border border-gray-200 bg-stone-50/50 space-y-1.5">
                            <div class="flex items-center gap-2 text-gray-900 font-bold text-xs sm:text-sm">
                                <span class="text-[#650506]">🛡️</span> Asuransi Pengiriman 100%
                            </div>
                            <p class="text-xs text-gray-600">
                                Setiap pengiriman otomatis terlindungi dari risiko hilang atau rusak di perjalanan hingga sampai ke tangan Anda.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- ════ TAB 3: RETURNS & REFUNDS ════ -->
                <div x-show="activeTab === 'returns'" class="space-y-4">
                    <div>
                        <h4 class="text-base sm:text-lg font-bold text-gray-900">Kebijakan Pengembalian &amp; Garansi</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Kepuasan dan ketenangan berbelanja Anda adalah prioritas tertinggi kami.</p>
                    </div>

                    <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200/80 space-y-2 text-xs sm:text-sm text-amber-900">
                        <p class="font-bold flex items-center gap-2">
                            <span>✦</span> Garansi Penggantian Produk Baru:
                        </p>
                        <p class="leading-relaxed">
                            Aroma Palace memberikan garansi penggantian unit baru tanpa biaya tambahan apabila paket parfum yang Anda terima mengalami kerusakan, botol pecah di perjalanan kurir, atau varian tidak sesuai pesanan.
                        </p>
                    </div>

                    <div class="space-y-2 text-xs sm:text-sm">
                        <h5 class="font-bold text-gray-900">Syarat &amp; Tata Cara Klaim Garansi:</h5>
                        <ol class="list-decimal list-inside space-y-2 text-gray-600 pl-1">
                            <li><strong>Wajib Video Unboxing:</strong> Rekam video saat pertama kali membuka paket pengiriman tanpa terputus (unboxing video) yang memperlihatkan label resi dan kondisi barang di dalam kotak.</li>
                            <li><strong>Batas Waktu Klaim:</strong> Permintaan retur atau klaim garansi disampaikan maksimal <strong>2x24 jam</strong> sejak status pengiriman kurir tercatat "Diterima / Delivered".</li>
                            <li><strong>Kondisi Produk:</strong> Untuk kesalahan varian, parfum belum digunakan (volume utuh) dan kemasan segel masih lengkap.</li>
                            <li><strong>Proses Cepat:</strong> Hubungi Customer Support kami via WhatsApp, kirim nomor pesanan beserta video unboxing. Tim kami akan memverifikasi dan mengirimkan unit pengganti dalam 1x24 jam kerja.</li>
                        </ol>
                    </div>
                </div>

                <!-- ════ TAB 4: PRIVACY POLICY ════ -->
                <div x-show="activeTab === 'privacy'" class="space-y-3 text-xs sm:text-sm leading-relaxed">
                    <div>
                        <h4 class="text-base sm:text-lg font-bold text-gray-900">Kebijakan Privasi (Privacy Policy)</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Perlindungan informasi pribadi pelanggan Aroma Palace.</p>
                    </div>

                    <div class="space-y-3 text-gray-600">
                        <p>
                            Aroma Palace menghormati privasi Anda dan berkomitmen melindungi data pribadi pelanggan kami sesuai dengan prinsip keamanan data dan peraturan perundang-undangan yang berlaku di Indonesia.
                        </p>
                        <h5 class="font-bold text-gray-900">1. Pengumpulan Informasi</h5>
                        <p>
                            Kami mengumpulkan informasi yang Anda berikan saat mendaftar akun, melakukan pemesanan wewangian, atau menghubungi dukungan pelanggan, termasuk nama, nomor telepon, alamat email, dan alamat pengiriman.
                        </p>
                        <h5 class="font-bold text-gray-900">2. Penggunaan Informasi</h5>
                        <p>
                            Data pribadi Anda hanya digunakan untuk memproses pesanan, mengatur pengiriman dengan mitra kurir, memberikan pembaruan status transaksi, dan mengirimkan informasi promo eksklusif jika Anda memilih untuk menerimanya.
                        </p>
                        <h5 class="font-bold text-gray-900">3. Keamanan Data &amp; Kerahasiaan</h5>
                        <p>
                            Kami menerapkan enkripsi SSL/TLS berstandar industri perbankan untuk melindungi data Anda. Kami <strong>tidak pernah memperjualbelikan, menyewakan, atau memberikan</strong> data pribadi Anda kepada pihak ketiga untuk tujuan komersial di luar pemenuhan pesanan Anda.
                        </p>
                    </div>
                </div>

                <!-- ════ TAB 5: TERMS OF SERVICE ════ -->
                <div x-show="activeTab === 'terms'" class="space-y-3 text-xs sm:text-sm leading-relaxed">
                    <div>
                        <h4 class="text-base sm:text-lg font-bold text-gray-900">Syarat &amp; Ketentuan Layanan (Terms of Service)</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Ketentuan penggunaan platform e-commerce resmi Aroma Palace.</p>
                    </div>

                    <div class="space-y-3 text-gray-600">
                        <p>
                            Dengan mengakses dan melakukan pembelian di platform web Aroma Palace, Anda menyetujui syarat dan ketentuan berikut:
                        </p>
                        <h5 class="font-bold text-gray-900">1. Akun dan Keamanan</h5>
                        <p>
                            Pengguna bertanggung jawab penuh atas kerahasiaan kata sandi akun dan aktivitas yang terjadi di bawah akun masing-masing.
                        </p>
                        <h5 class="font-bold text-gray-900">2. Pemesanan &amp; Ketersediaan Produk</h5>
                        <p>
                            Semua pesanan parfum bergantung pada ketersediaan stok aktual. Kami berhak membatalkan atau menyesuaikan pesanan apabila terjadi kesalahan sistem harga atau ketersediaan stok mendadak, dengan pengembalian dana penuh 100%.
                        </p>
                        <h5 class="font-bold text-gray-900">3. Kode Voucher Promo &amp; Poin Reward</h5>
                        <p>
                            Voucher promo dan poin loyalitas Aroma Palace tunduk pada masa berlaku, kuota, dan ketentuan minimum belanja yang tertera pada masing-masing voucher.
                        </p>
                        <h5 class="font-bold text-gray-900">4. Hak Kekayaan Intelektual</h5>
                        <p>
                            Seluruh merek dagang, logo Aroma Palace, deskripsi aroma, foto produk, dan artikel wewangian di situs ini merupakan hak milik eksklusif Aroma Palace dan dilindungi undang-undang.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Modal Footer (Contact Support Action Bar) -->
            <div class="px-4 sm:px-6 py-3 bg-stone-50 border-t border-gray-100 flex flex-wrap items-center justify-between gap-3 shrink-0">
                <span class="text-[11px] sm:text-xs text-gray-500">
                    Perlu bantuan personal atau konsultasi aroma?
                </span>
                <div class="flex items-center gap-2">
                    <a href="https://wa.me/6281188888888?text=Halo%20Aroma%20Palace%2C%20saya%20butuh%20informasi%20lebih%20lanjut" 
                       target="_blank" 
                       rel="noopener noreferrer"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-lg transition shadow-2xs">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                        <span>Chat CS WhatsApp</span>
                    </a>
                    <button @click="closeModal()" 
                            type="button" 
                            class="px-3 py-1.5 bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 font-semibold text-xs rounded-lg transition">
                        Tutup
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
