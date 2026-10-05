<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Pengiriman - #{{ $order->order_number }} - KiriminAja</title>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        @page {
            size: 100mm 150mm;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #e2e8f0;
            margin: 0;
            padding: 20px;
            color: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .action-bar {
            width: 100mm;
            max-width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
        }
        .btn-print {
            background: #059669;
            color: #ffffff;
        }
        .btn-back {
            background: #ffffff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .label-container {
            width: 100mm;
            min-height: 145mm;
            background: #ffffff;
            border: 2px solid #000000;
            padding: 8px 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000000;
            padding-bottom: 6px;
        }
        .courier-badge {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .courier-logo {
            font-size: 22px;
            font-weight: 900;
            letter-spacing: -0.5px;
            color: #000000;
            line-height: 1;
        }
        .service-pill {
            background: #000000;
            color: #ffffff;
            padding: 3px 8px;
            font-size: 14px;
            font-weight: 900;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }
        .sorting-code {
            text-align: right;
        }
        .sorting-title {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 800;
            color: #64748b;
        }
        .sorting-value {
            font-size: 20px;
            font-weight: 900;
            font-family: monospace;
            line-height: 1;
        }
        .barcode-section {
            text-align: center;
            border-bottom: 2px solid #000000;
            padding: 6px 0;
        }
        .barcode-svg {
            width: 90%;
            height: 52px;
            margin: 0 auto;
            display: block;
        }
        .awb-text-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2px;
            font-family: monospace;
            font-size: 12px;
            font-weight: 800;
        }
        .shipping-type-banner {
            background: #000000;
            color: #ffffff;
            text-align: center;
            padding: 4px;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 4px 0;
            border-radius: 3px;
        }
        .meta-strip {
            display: flex;
            border-bottom: 2px solid #000000;
            border-top: 1px solid #000000;
            font-size: 10px;
            margin-bottom: 6px;
        }
        .meta-cell {
            flex: 1;
            padding: 4px;
            border-right: 1px solid #000000;
        }
        .meta-cell:last-child {
            border-right: none;
        }
        .meta-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #475569;
            font-weight: 700;
            display: block;
        }
        .meta-value {
            font-size: 11px;
            font-weight: 800;
        }
        .parties-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            border-bottom: 2px solid #000000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .party-box {
            font-size: 9.5px;
            line-height: 1.35;
        }
        .party-title {
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            margin-bottom: 2px;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 1px;
        }
        .party-name {
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 1px;
        }
        .party-phone {
            font-weight: 700;
            font-family: monospace;
            margin-bottom: 2px;
        }
        .party-address {
            color: #1e293b;
        }
        .party-city-zip {
            font-weight: 800;
            margin-top: 2px;
            text-transform: uppercase;
        }
        .items-section {
            border-bottom: 2px solid #000000;
            padding-bottom: 4px;
            margin-bottom: 4px;
            font-size: 9px;
        }
        .items-header {
            display: flex;
            justify-content: space-between;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 8.5px;
            color: #475569;
            border-bottom: 1px dashed #94a3b8;
            padding-bottom: 2px;
            margin-bottom: 3px;
        }
        .item-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            line-height: 1.25;
        }
        .item-name {
            font-weight: 600;
            max-width: 75%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .item-qty {
            font-weight: 800;
            font-family: monospace;
        }
        .footer-section {
            font-size: 8.5px;
            line-height: 1.3;
            color: #334155;
        }
        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .kiriminaja-watermark {
            font-size: 8.5px;
            font-weight: 900;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .warning-note {
            font-size: 8px;
            font-weight: 700;
            color: #dc2626;
            text-transform: uppercase;
            margin-top: 2px;
        }
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .action-bar {
                display: none;
            }
            .label-container {
                border: 2px solid #000000;
                box-shadow: none;
                width: 100mm;
                height: 145mm;
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>

    @php
        $snap = $order->shipping_address_snapshot ?? [];
        $recipientName = $snap['recipient_name'] ?? ($order->address?->recipient_name ?? ($order->user?->name ?? 'Customer'));
        $recipientPhone = $snap['phone_number'] ?? ($order->address?->phone_number ?? '08123456789');
        $recipientAddress = $snap['full_address'] ?? ($order->address?->full_address ?? 'Alamat Pemesan');
        $recipientCity = $snap['city'] ?? ($order->address?->city ?? 'Jakarta');
        $recipientPostal = $snap['postal_code'] ?? ($order->address?->postal_code ?? '10110');

        $courier = strtoupper($order->shipping_courier ?: 'JNE');
        $service = strtoupper($order->shipping_service ?: 'REG');
        $trackingNumber = $order->tracking_number ?: ('KA-' . $courier . '-' . rand(1000000000, 9999999999));
        $totalItems = $order->items->sum('quantity') ?: 1;
        $totalWeight = max(1000, (int) $order->items->sum(fn($i) => ($i->quantity * 250)));
        $isInsurance = ($order->total_amount >= 500000);
        $sortingOrigin = 'CGK';
        $sortingDest = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $recipientCity), 0, 3)) ?: 'SUB';
    @endphp

    <div class="action-bar">
        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-back">
            &larr; Kembali ke Detail Pesanan
        </a>
        <button onclick="window.print()" class="btn btn-print">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Label Thermal (100x150 mm)
        </button>
    </div>

    <!-- THERMAL SHIPPING LABEL CONTAINER (A6 / 100x150 mm) -->
    <div class="label-container" id="shipping-label">
        
        <!-- 1. Header: Logistic Logo, Service Label & Sorting Code -->
        <div>
            <div class="header-row">
                <div class="courier-badge">
                    @php
                        $courierLogo = \App\Services\KiriminAjaShippingService::getCourierLogoUrl($courier);
                    @endphp
                    @if($courierLogo)
                        <img src="{{ $courierLogo }}" alt="{{ $courier }}" style="height: 28px; max-width: 90px; object-fit: contain;">
                    @else
                        <span class="courier-logo">{{ $courier }}</span>
                    @endif
                    <span class="service-pill">{{ $service }}</span>
                </div>
                <div class="sorting-code">
                    <div class="sorting-title">Routing / Sorting Hub</div>
                    <div class="sorting-value">{{ $sortingOrigin }} &rarr; {{ $sortingDest }}</div>
                </div>
            </div>

            <!-- 2. Barcode & AWB Label (Format Code 128) -->
            <div class="barcode-section">
                <svg id="awb-barcode" class="barcode-svg"></svg>
                <div class="awb-text-row">
                    <span>AWB: {{ $trackingNumber }}</span>
                    <span>REF: #{{ $order->order_number }}</span>
                </div>
            </div>

            <!-- 3. Shipping Type (Wajib CASHLESS Non-COD / COD sesuai panduan UAT) -->
            <div class="shipping-type-banner">
                CASHLESS NON-COD
            </div>

            <!-- 4. Metadata Strip (Weight, Qty, Insurance, Origin/Dest) -->
            <div class="meta-strip">
                <div class="meta-cell">
                    <span class="meta-label">Berat Paket</span>
                    <span class="meta-value">{{ number_format($totalWeight, 0, ',', '.') }} gr</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">Total Qty</span>
                    <span class="meta-value">{{ $totalItems }} Pcs</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">Asuransi</span>
                    <span class="meta-value">{{ $isInsurance ? 'YA (TERCOVER)' : 'TIDAK' }}</span>
                </div>
                <div class="meta-cell">
                    <span class="meta-label">Tujuan</span>
                    <span class="meta-value">{{ strtoupper($recipientCity) }}</span>
                </div>
            </div>

            <!-- 5. Parties Details (Sender & Recipient Details) -->
            <div class="parties-grid">
                <!-- Penerima -->
                <div class="party-box">
                    <div class="party-title">Kepada (Penerima):</div>
                    <div class="party-name">{{ $recipientName }}</div>
                    <div class="party-phone">{{ $recipientPhone }}</div>
                    <div class="party-address">{{ $recipientAddress }}</div>
                    <div class="party-city-zip">{{ $recipientCity }}, {{ $recipientPostal }}</div>
                </div>

                <!-- Pengirim -->
                <div class="party-box">
                    <div class="party-title">Dari (Pengirim):</div>
                    <div class="party-name">Aroma Palace Haute Parfumerie</div>
                    <div class="party-phone">081100001111</div>
                    <div class="party-address">Jl. M.H. Thamrin No. 88, Menteng</div>
                    <div class="party-city-zip">Jakarta Pusat, 10350</div>
                </div>
            </div>

            <!-- 6. Item Details (Nama/List/Harga Item) -->
            <div class="items-section">
                <div class="items-header">
                    <span>Rincian Barang (Deskripsi Isi Paket)</span>
                    <span>Qty</span>
                </div>
                @foreach($order->items as $item)
                    <div class="item-row">
                        <span class="item-name">{{ $item->product_name }} {{ $item->variant_name ? '(' . $item->variant_name . ')' : '' }}</span>
                        <span class="item-qty">&times;{{ $item->quantity }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- 7. Footer: Order Ref Label, KiriminAja Watermark & Fragile Note -->
        <div class="footer-section">
            <div class="footer-row">
                <span>Order Ref: <strong>#{{ $order->order_number }}</strong></span>
                <span class="kiriminaja-watermark">Integrated via KiriminAja MitraAPI</span>
            </div>
            <div class="warning-note">
                &#9888; PERHATIAN: FRAGILE / MUDAH PECAH - PARFUM EKSKLUSIF - JANGAN DIBANTING / DIBALIK
            </div>
        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            JsBarcode("#awb-barcode", "{{ $trackingNumber }}", {
                format: "CODE128",
                lineColor: "#000000",
                width: 2.2,
                height: 48,
                displayValue: false,
                margin: 0
            });
        });
    </script>
</body>
</html>
