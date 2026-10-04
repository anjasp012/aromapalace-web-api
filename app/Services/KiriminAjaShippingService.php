<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use KiriminAja\Base\Config\KiriminAjaConfig;
use KiriminAja\Models\PackageData;
use KiriminAja\Models\PackageItemData;
use KiriminAja\Models\RequestPickupData;
use KiriminAja\Models\ShippingPriceData;
use KiriminAja\Services\KiriminAja;

class KiriminAjaShippingService
{
    protected string $mode;
    protected string $baseUrl;
    protected string $apiKey;
    protected string $senderName;
    protected string $senderPhone;
    protected string $senderAddress;
    protected int $senderCityId;

    public function __construct()
    {
        $this->mode = (string) config('services.kiriminaja.mode', 'staging');
        $this->baseUrl = (string) config('services.kiriminaja.base_url', 'https://tdev.kiriminaja.com');
        $this->apiKey = (string) config('services.kiriminaja.api_key', 'demo_kiriminaja_key');
        $this->senderName = (string) config('services.kiriminaja.sender_name', 'Aroma Palace Haute Parfumerie');
        $this->senderPhone = (string) config('services.kiriminaja.sender_phone', '081100001111');
        $this->senderAddress = (string) config('services.kiriminaja.sender_address', 'Jl. M.H. Thamrin No. 88, Menteng, Jakarta Pusat');
        $this->senderCityId = (int) config('services.kiriminaja.sender_city_id', 151); // Jakarta Pusat

        // Inisialisasi konfigurasi SDK KiriminAja
        try {
            KiriminAjaConfig::setMode($this->mode);
            if (!empty($this->apiKey)) {
                KiriminAjaConfig::setApiTokenKey($this->apiKey);
            }
            if (!empty($this->baseUrl)) {
                KiriminAjaConfig::setBaseUrl($this->baseUrl);
            }
        } catch (\Throwable $e) {
            Log::warning('KiriminAjaConfig initialization note: ' . $e->getMessage());
        }
    }

    /**
     * Cek tarif ongkos kirim agregator kurir (JNE, J&T, SiCepat, Anteraja, Ninja, dll.)
     * Menggunakan KiriminAja SDK resmi (KiriminAja::getPrice)
     */
    public function getShippingRates(string $destinationCity, int $weightInGrams = 1000): array
    {
        // Jika mode live dengan API key asli, panggil KiriminAja SDK
        if ($this->apiKey !== 'demo_kiriminaja_key' && !app()->environment('testing')) {
            try {
                $destCityId = $this->resolveCityId($destinationCity);
                $rates = $this->getLivePricingServices($this->senderCityId, $destCityId, $weightInGrams, ['jne', 'jnt', 'sicepat', 'anteraja', 'ninja']);

                if (!empty($rates)) {
                    $results = [];
                    foreach ($rates as $rate) {
                        $courierCode = strtolower($rate['service'] ?? 'kiriminaja');
                        $serviceCode = $rate['service_type'] ?? ($rate['service_name'] ?? 'REG');
                        $results[] = [
                            'courier' => strtoupper($courierCode),
                            'service' => $rate['service_name'] ?? $serviceCode,
                            'service_type' => $serviceCode,
                            'cost' => (float) ($rate['cost'] ?? 15000),
                            'etd' => ($rate['etd'] ?? '2-3') . ' Hari',
                            'description' => $rate['text'] ?? ($rate['service_name'] ?? ''),
                        ];
                    }
                    if (!empty($results)) {
                        return $results;
                    }
                }
            } catch (Exception $e) {
                Log::warning('KiriminAja SDK rate calculation error: ' . $e->getMessage());
            }
        }

        // Realistic Fallback / Sandbox Rates berdasarkan kota tujuan
        return $this->generateFallbackRates($destinationCity);
    }

    /**
     * Ambil daftar layanan kurir aktif beserta tarif real-time dari API KiriminAja via SDK
     */
    public function getLivePricingServices(int $originCityId, int $destCityId, int $weightInGrams = 1000, array $couriers = []): array
    {
        if (empty($couriers)) {
            $couriers = ['jne', 'sicepat', 'jnt', 'anteraja', 'ninja', 'idexpress', 'sap', 'lion'];
        }

        try {
            $priceData = new ShippingPriceData();
            $priceData->origin = $originCityId;
            $priceData->destination = $destCityId;
            $priceData->weight = max(100, $weightInGrams);
            $priceData->courier = $couriers;

            $response = KiriminAja::getPrice($priceData);

            if ($response->status && !empty($response->data)) {
                $rates = $response->data['results'] ?? ($response->data['details'] ?? $response->data);
                if (is_array($rates)) {
                    return $rates;
                }
            } else {
                if (str_contains($response->message, '401')) {
                    Log::warning("KiriminAja IP Whitelist / API Key notice: {$response->message}. Pastikan IP server di-whitelist di Dashboard KiriminAja.");
                }
            }
        } catch (\Throwable $th) {
            Log::warning('KiriminAja SDK getPrice exception: ' . $th->getMessage());
        }

        return [];
    }

    /**
     * Buat pemesanan penjemputan paket dan generate nomor resi kurir (AWB Booking) via KiriminAja SDK
     */
    public function createShipment(Order $order): array
    {
        $courier = strtolower($order->shipping_courier ?: 'jne');
        $rawService = strtolower($order->shipping_service ?: 'reg');
        $service = strtoupper($rawService);

        // Jika mode live dengan API key asli (bukan testing/demo)
        if ($this->apiKey !== 'demo_kiriminaja_key' && !app()->environment('testing')) {
            $snap = $order->shipping_address_snapshot ?? [];
            $recipientName = $snap['recipient_name'] ?? ($order->address?->recipient_name ?? ($order->user?->name ?? 'Customer'));
            $recipientPhone = $snap['phone_number'] ?? ($order->address?->phone_number ?? '08123456789');
            $destinationAddress = $snap['full_address'] ?? ($order->address?->full_address ?? 'Alamat Pemesan');
            $destinationCity = $snap['city'] ?? ($order->address?->city ?? 'Jakarta');
            $destinationPostal = $snap['postal_code'] ?? ($order->address?->postal_code ?? '10110');
            $destCityId = $this->resolveCityId($destinationCity);
            $packageWeight = max(250, (int) $order->items->sum(fn($i) => ($i->quantity * 250)));

            // 1. Cek layanan dan kurir aktif langsung dari KiriminAja pricing via SDK
            $liveRates = $this->getLivePricingServices($this->senderCityId, $destCityId, $packageWeight, [$courier, 'jne', 'sicepat', 'jnt', 'anteraja', 'ninja']);

            // 2. Susun antrian percobaan (attempts) kurir & service type
            $attempts = [];
            $seenKeys = [];

            $addAttempt = function (string $c, string $st, int $cost = 0, bool $isAlternative = false) use (&$attempts, &$seenKeys) {
                $key = strtolower($c) . ':' . strtolower($st);
                if (!isset($seenKeys[$key])) {
                    $seenKeys[$key] = true;
                    $attempts[] = [
                        'service' => strtolower($c),
                        'service_type' => $st,
                        'cost' => $cost,
                        'is_alternative' => $isAlternative,
                    ];
                }
            };

            // Tahap A: Prioritaskan layanan yang ditemukan di live pricing untuk kurir yang dipilih user
            $courierLiveRates = array_filter($liveRates, fn($r) => strtolower($r['service'] ?? '') === $courier);
            if (!empty($courierLiveRates)) {
                $matchedRate = null;
                foreach ($courierLiveRates as $r) {
                    $st = strtolower($r['service_type'] ?? '');
                    $sn = strtolower($r['service_name'] ?? '');
                    if (str_contains($st, $rawService) || str_contains($sn, $rawService)) {
                        $matchedRate = $r;
                        break;
                    }
                }
                if ($matchedRate) {
                    $addAttempt($matchedRate['service'], $matchedRate['service_type'], (int) ($matchedRate['cost'] ?? 0));
                }
                foreach ($courierLiveRates as $r) {
                    $addAttempt($r['service'], $r['service_type'], (int) ($r['cost'] ?? 0));
                }
            }

            // Tahap B: Tambahkan candidate codes standar untuk kurir yang diminta
            if ($courier === 'jne') {
                $isJakartaRoute = in_array($this->senderCityId, [151, 152, 153, 154, 155]) && in_array($destCityId, [151, 152, 153, 154, 155]);
                $jneCandidates = $isJakartaRoute
                    ? ['CTC', 'REG', 'REG23', 'CTC23', 'FLREG23', 'REG19', 'ctc', 'reg', 'oke']
                    : ['REG', 'REG23', 'REG19', 'CTC', 'FLREG23', 'reg', 'ctc', 'oke'];
                foreach ($jneCandidates as $cand) {
                    $addAttempt('jne', $cand);
                }
            } elseif ($courier === 'jnt' || $courier === 'j&t') {
                foreach (['EZ', 'ez', 'reg', 'standard'] as $cand) {
                    $addAttempt('jnt', $cand);
                }
            } elseif ($courier === 'sicepat') {
                foreach (['SIUNT', 'REG', 'BEST', 'GOKIL', 'siunt', 'reg'] as $cand) {
                    $addAttempt('sicepat', $cand);
                }
            } elseif ($courier === 'ninja') {
                foreach (['STANDARD', 'REG', 'standard', 'reg'] as $cand) {
                    $addAttempt('ninja', $cand);
                }
            } elseif ($courier === 'anteraja') {
                foreach (['REG', 'SD', 'reg'] as $cand) {
                    $addAttempt('anteraja', $cand);
                }
            } else {
                $addAttempt($courier, strtoupper($rawService));
                $addAttempt($courier, strtolower($rawService));
            }

            // Tahap C: Kurir alternatif jika kurir asli belum aktif
            foreach ($liveRates as $r) {
                if (strtolower($r['service'] ?? '') !== $courier && !empty($r['service']) && !empty($r['service_type'])) {
                    $addAttempt($r['service'], $r['service_type'], (int) ($r['cost'] ?? 0), true);
                }
            }

            $addAttempt('sicepat', 'SIUNT', 0, true);
            $addAttempt('jnt', 'EZ', 0, true);
            $addAttempt('ninja', 'STANDARD', 0, true);

            // Normalisasi nomor telepon penerima
            $cleanPhone = preg_replace('/[^0-9]/', '', $recipientPhone);
            if (!str_starts_with($cleanPhone, '0') && !str_starts_with($cleanPhone, '62')) {
                $cleanPhone = '0' . $cleanPhone;
            }

            // Prefix Order ID (wajib unik di akun KiriminAja)
            $prefix = config('services.kiriminaja.order_prefix', 'ARPL');
            $cleanPrefix = rtrim($prefix ?: 'ARPL', '-');
            $rawOrder = $order->order_number;
            if (!str_starts_with($rawOrder, $cleanPrefix)) {
                $cleanSuffix = preg_replace('/^[A-Za-z0-9]+-?/', '', $rawOrder);
                $kiriminAjaOrderId = Str::limit($cleanPrefix . '-' . $cleanSuffix, 20, '');
            } else {
                $kiriminAjaOrderId = Str::limit($rawOrder, 20, '');
            }

            // 1. Cek apakah pengiriman untuk order_id ini sudah sukses terdaftar di KiriminAja sebelumnya
            $existing = $this->checkExistingShipment($kiriminAjaOrderId);
            if ($existing) {
                $this->updateOrderTracking($order, $existing['tracking_number'], $existing['courier'], $existing['service'], $existing['booking_id']);
                return $existing;
            }

            // 2. Susun data item paket (PackageItemData)
            $packageItems = [];
            foreach ($order->items as $item) {
                $pkgItem = new PackageItemData();
                $pkgItem->name = (string) Str::limit($item->product_name ?? 'Parfum Aroma Palace', 50, '');
                $pkgItem->price = (int) round($item->price ?? 100000);
                $pkgItem->qty = max(1, (int) ($item->quantity ?? 1));
                $pkgItem->weight = max(100, (int) round(($item->quantity ?? 1) * 250));
                $packageItems[] = $pkgItem;
            }

            $pickupSchedule = $this->resolvePickupSchedule();

            $successfulResponse = null;
            $succeededCourier = $courier;
            $succeededService = $service;
            $wasAdapted = false;
            $lastError = 'Gagal menghubungi KiriminAja API';

            // 3. Eksekusi request_pickup via SDK
            foreach ($attempts as $attempt) {
                $currCourier = $attempt['service'];
                $currServiceType = $attempt['service_type'];
                $currCost = ($attempt['cost'] > 0) ? $attempt['cost'] : (int) round($order->shipping_cost);

                $pickupData = new RequestPickupData();
                $pickupData->address = $this->senderAddress;
                $pickupData->phone = $this->senderPhone;
                $pickupData->name = $this->senderName;
                $pickupData->zipcode = '10350';
                $pickupData->kecamatan_id = $this->senderCityId;
                $pickupData->schedule = $pickupSchedule;

                $pkg = new PackageData();
                $pkg->order_id = $kiriminAjaOrderId;
                $pkg->destination_name = (string) Str::limit($recipientName, 50, '');
                $pkg->destination_phone = (string) Str::limit($cleanPhone, 15, '');
                $pkg->destination_address = (string) Str::limit($destinationAddress, 200, '');
                $pkg->destination_kecamatan_id = $destCityId;
                $pkg->destination_zipcode = $destinationPostal;
                $pkg->weight = $packageWeight;
                $pkg->width = 10;
                $pkg->length = 10;
                $pkg->height = 10;
                $pkg->qty = max(1, (int) $order->items->sum('quantity'));
                $pkg->item_value = (int) round($order->total_amount);
                $pkg->shipping_cost = $currCost;
                $pkg->item_name = 'Parfum Eksklusif Aroma Palace (Haute Fragrance)';
                $pkg->service = $currCourier;
                $pkg->service_type = $currServiceType;
                $pkg->cod = 0;
                $pkg->package_type_id = 1;
                if (!empty($packageItems)) {
                    $pkg->items = $packageItems;
                }

                $pickupData->packages->add($pkg);

                try {
                    $sdkRes = KiriminAja::requestPickup($pickupData);
                    if ($sdkRes->status && !empty($sdkRes->data)) {
                        $successfulResponse = $sdkRes->data;
                        $succeededCourier = $currCourier;
                        $succeededService = $currServiceType;
                        $wasAdapted = !empty($attempt['is_alternative']) || ($currCourier !== $courier);
                        break;
                    }
                    $lastError = $sdkRes->message;
                } catch (\Throwable $e) {
                    $lastError = $e->getMessage();
                }

                if (str_contains(strtolower($lastError), 'ip') && str_contains(strtolower($lastError), 'whitelist')) {
                    $lastError .= " (IP Server belum di-whitelist di Dashboard KiriminAja)";
                    break;
                }

                // Jika terjadi duplikasi order_id, coba retry dengan fresh prefix
                if (str_contains(strtolower($lastError), 'terjadi kesalahan') || str_contains(strtolower($lastError), 'already')) {
                    $tracked = $this->checkExistingShipment($pkg->order_id);
                    if ($tracked) {
                        $this->updateOrderTracking($order, $tracked['tracking_number'], $tracked['courier'], $tracked['service'], $tracked['booking_id']);
                        return $tracked;
                    }

                    $freshOrderId = Str::limit($cleanPrefix . '-' . strtoupper(Str::random(10)), 20, '');
                    $pkg->order_id = $freshOrderId;
                    $retryPickupData = clone $pickupData;
                    $retryPickupData->packages = new \KiriminAja\Models\RequestPickupDataList();
                    $retryPickupData->packages->add($pkg);

                    try {
                        $retryRes = KiriminAja::requestPickup($retryPickupData);
                        if ($retryRes->status && !empty($retryRes->data)) {
                            $successfulResponse = $retryRes->data;
                            $succeededCourier = $currCourier;
                            $succeededService = $currServiceType;
                            $wasAdapted = !empty($attempt['is_alternative']) || ($currCourier !== $courier);
                            break;
                        }
                    } catch (\Throwable $e) {
                        $lastError = $e->getMessage();
                    }
                }
            }

            // 4. Jika seluruh percobaan gagal, lempar exception
            if (!$successfulResponse) {
                if (str_contains(strtolower($lastError), 'tidak tersedia')) {
                    $lastError .= ". Pastikan ekspedisi " . strtoupper($courier) . " telah diaktifkan di Dashboard KiriminAja -> Pengaturan / Integrasi -> Ekspedisi, atau pastikan saldo KA Pay mencukupi.";
                }
                Log::warning("KiriminAja SDK requestPickup failed: {$lastError}");
                throw new Exception($lastError);
            }

            // 5. Parsing response dan ambil nomor AWB / Resi resmi
            $details = $successfulResponse['details'] ?? [];
            $firstDetail = (is_array($details) && isset($details[0]) && is_array($details[0]))
                ? $details[0]
                : (is_array($details) ? $details : []);

            $awb = $firstDetail['awb']
                ?? ($firstDetail['tracking_number']
                ?? ($successfulResponse['awb']
                ?? ($successfulResponse['tracking_number'] ?? null)));

            $pickupNumber = $successfulResponse['pickup_number']
                ?? ($firstDetail['pickup_number']
                ?? ($successfulResponse['booking_id']
                ?? ($firstDetail['booking_id'] ?? null)));

            $bookingId = $pickupNumber ?: ($firstDetail['order_id'] ?? $kiriminAjaOrderId);

            // Nomor resi pengiriman
            $trackingNumber = (!empty($awb) && is_string($awb))
                ? $awb
                : ($pickupNumber ?: $kiriminAjaOrderId);

            // Simpan resi ke order
            $this->updateOrderTracking($order, $trackingNumber, strtoupper($succeededCourier), strtoupper($succeededService), $bookingId);

            if ($wasAdapted) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status' => 'shipped',
                    'title' => 'Penyesuaian Ekspedisi Pengiriman',
                    'description' => "Kurir otomatis dialihkan dari " . strtoupper($courier) . " ke " . strtoupper($succeededCourier) . " (" . strtoupper($succeededService) . ") karena layanan " . strtoupper($courier) . " sedang tidak aktif di akun KiriminAja.",
                ]);
            }

            return [
                'success' => true,
                'courier' => strtoupper($succeededCourier),
                'service' => strtoupper($succeededService),
                'tracking_number' => $trackingNumber,
                'booking_id' => $bookingId,
                'is_simulation' => false,
                'adapted' => $wasAdapted,
                'original_courier' => strtoupper($courier),
            ];
        }

        // Simulasi Resi Resmi Kurir Otomatis (Testing & Sandbox Fallback)
        $trackingNumber = 'KA-' . strtoupper($courier) . '-' . rand(1000000000, 9999999999);
        $bookingId = 'BKG-' . strtoupper(Str::random(8));

        $this->updateOrderTracking($order, $trackingNumber, strtoupper($courier), strtoupper($service), $bookingId);

        return [
            'success' => true,
            'courier' => strtoupper($courier),
            'service' => strtoupper($service),
            'tracking_number' => $trackingNumber,
            'booking_id' => $bookingId,
            'is_simulation' => true,
            'adapted' => false,
        ];
    }

    /**
     * Dapatkan jadwal pickup kurir dari SDK KiriminAja (KiriminAja::getSchedules)
     */
    public function resolvePickupSchedule(): string
    {
        $now = now()->timezone('Asia/Jakarta');

        try {
            $res = KiriminAja::getSchedules();
            if ($res->status && !empty($res->data) && is_array($res->data)) {
                foreach ($res->data as $slot) {
                    if (empty($slot['expired']) && empty($slot['libur']) && !empty($slot['clock'])) {
                        $clock = trim($slot['clock']);
                        if (strlen($clock) === 5) {
                            $clock .= ':00';
                        }
                        $slotDateTime = \Illuminate\Support\Carbon::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-d') . ' ' . $clock, 'Asia/Jakarta');
                        if ($slotDateTime && $slotDateTime->isFuture() && $slotDateTime->diffInMinutes($now) >= 45) {
                            return $slotDateTime->format('Y-m-d H:i:s');
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::info('KiriminAja SDK schedules check notice: ' . $e->getMessage());
        }

        // Jika lewat pukul 14:00 WIB, semua slot pickup kurir hari ini sudah ditutup
        if ($now->hour >= 14) {
            $target = $now->copy()->addDay();
            if ($target->isSunday()) {
                $target->addDay();
            }
            return $target->setTime(10, 0, 0)->format('Y-m-d H:i:s');
        }

        if ($now->hour < 9) {
            return $now->copy()->setTime(10, 0, 0)->format('Y-m-d H:i:s');
        }

        return $now->copy()->addHours(2)->setTime($now->hour + 2, 0, 0)->format('Y-m-d H:i:00');
    }

    /**
     * Periksa apakah order sudah pernah terdaftar di KiriminAja via SDK
     */
    public function checkExistingShipment(string $orderId): ?array
    {
        if (empty($orderId) || $this->apiKey === 'demo_kiriminaja_key' || app()->environment('testing')) {
            return null;
        }

        try {
            $res = KiriminAja::getTracking($orderId);
            if ($res->status && !empty($res->data['details'])) {
                $details = $res->data['details'];
                if (is_array($details)) {
                    $awb = $details['awb'] ?? ($details['order_id'] ?? $orderId);
                    return [
                        'success' => true,
                        'courier' => strtoupper($details['service'] ?? 'KIRIMINAJA'),
                        'service' => strtoupper($details['service_name'] ?? 'REG'),
                        'tracking_number' => $awb,
                        'booking_id' => $details['order_id'] ?? $orderId,
                        'is_simulation' => false,
                        'adapted' => false,
                        'original_courier' => strtoupper($details['service'] ?? 'KIRIMINAJA'),
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::info("KiriminAja SDK checkExistingShipment error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Lacak milestone status pengiriman via KiriminAja SDK (KiriminAja::getTracking)
     */
    public function trackShipment(string $courier, string $trackingNumber): array
    {
        if ($this->apiKey !== 'demo_kiriminaja_key' && !app()->environment('testing')) {
            try {
                $res = KiriminAja::getTracking($trackingNumber);
                if ($res->status && !empty($res->data['histories']) && is_array($res->data['histories'])) {
                    $milestones = [];
                    foreach ($res->data['histories'] as $history) {
                        $milestones[] = [
                            'status' => $history['status'] ?? 'UPDATE',
                            'note' => $history['note'] ?? ($history['message'] ?? ''),
                            'time' => $history['created_at'] ?? now()->format('d M Y, H:i'),
                        ];
                    }
                    if (!empty($milestones)) {
                        return $milestones;
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('KiriminAja SDK trackShipment error: ' . $e->getMessage());
            }
        }

        // Fallback milestone
        return [
            ['status' => 'PICKED_UP', 'note' => 'Paket berhasil dijemput kurir', 'time' => now()->subDay()->format('d M Y, H:i')],
            ['status' => 'IN_TRANSIT', 'note' => 'Paket sedang dalam perjalanan menuju hub transit', 'time' => now()->subHours(12)->format('d M Y, H:i')],
            ['status' => 'OUT_FOR_DELIVERY', 'note' => 'Kurir sedang mengantarkan paket ke alamat Anda', 'time' => now()->format('d M Y, H:i')],
        ];
    }

    /**
     * Batalkan penjemputan paket / pengiriman di KiriminAja via SDK
     */
    public function cancelShipment(string $trackingNumber, string $reason = 'Pembatalan pesanan'): array
    {
        if ($this->apiKey === 'demo_kiriminaja_key' || app()->environment('testing')) {
            return ['status' => true, 'message' => 'Simulasi pembatalan pesanan di KiriminAja sukses.'];
        }

        try {
            $res = KiriminAja::cancelShipment($trackingNumber, $reason);
            return [
                'status' => $res->status,
                'message' => $res->message,
                'data' => $res->data,
            ];
        } catch (\Throwable $e) {
            Log::warning('KiriminAja SDK cancelShipment error: ' . $e->getMessage());
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Cetak label pengiriman thermal / PDF via KiriminAja SDK
     */
    public function printShippingLabel(array|string $trackingNumbers): ?string
    {
        try {
            $awbs = is_array($trackingNumbers) ? $trackingNumbers : [$trackingNumbers];
            $res = KiriminAja::printAWB(['awb' => $awbs]);
            if ($res->status && !empty($res->data)) {
                return is_string($res->data) ? $res->data : ($res->data['url'] ?? json_encode($res->data));
            }
        } catch (\Throwable $e) {
            Log::warning('KiriminAja SDK printShippingLabel error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Dapatkan daftar provinsi resmi dari KiriminAja SDK
     */
    public function getProvinces(): array
    {
        try {
            $res = KiriminAja::getProvince();
            return ($res->status && is_array($res->data)) ? $res->data : [];
        } catch (\Throwable $e) {
            Log::warning('KiriminAja getProvince error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Dapatkan daftar kota resmi berdasarkan ID provinsi dari KiriminAja SDK
     */
    public function getCities(int $provinceId): array
    {
        try {
            $res = KiriminAja::getCity($provinceId);
            return ($res->status && is_array($res->data)) ? $res->data : [];
        } catch (\Throwable $e) {
            Log::warning('KiriminAja getCity error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Dapatkan daftar kecamatan resmi berdasarkan ID kota dari KiriminAja SDK
     */
    public function getDistricts(int $cityId): array
    {
        try {
            $res = KiriminAja::getDistrict($cityId);
            return ($res->status && is_array($res->data)) ? $res->data : [];
        } catch (\Throwable $e) {
            Log::warning('KiriminAja getDistrict error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Webhook tracking event dari KiriminAja
     */
    public function handleTrackingWebhook(array $payload): array
    {
        $orderNumber = $payload['order_id'] ?? null;
        $trackingNumber = $payload['awb'] ?? ($payload['tracking_number'] ?? null);
        $status = strtolower($payload['status'] ?? '');

        if (!$orderNumber && !$trackingNumber) {
            throw new Exception('Data pesanan atau nomor resi tidak valid.');
        }

        $order = Order::where(function ($q) use ($orderNumber, $trackingNumber) {
            if ($orderNumber) {
                $q->where('order_number', $orderNumber)->orWhere('order_number', 'like', "%{$orderNumber}%");
            }
            if ($trackingNumber) {
                $q->orWhere('tracking_number', $trackingNumber);
            }
        })->first();

        if (!$order) {
            return ['status' => 'ignored', 'message' => 'Pesanan tidak ditemukan'];
        }

        if ($trackingNumber && $order->tracking_number !== $trackingNumber) {
            $order->update(['tracking_number' => $trackingNumber]);
        }

        if (in_array($status, ['delivered', 'selesai', 'sukses'])) {
            $order->update([
                'order_status' => 'completed',
                'completed_at' => now(),
            ]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'completed',
                'title' => 'Paket Berhasil Diterima & Pesanan Selesai',
                'description' => $payload['note'] ?? 'Pesanan telah diterima oleh penerima yang bersangkutan.',
            ]);

            return ['status' => 'success', 'order_status' => 'completed'];
        }

        return ['status' => 'received', 'message' => 'Status tracking berhasil dicatat.'];
    }

    /**
     * Update nomor resi di order dan simpan riwayat timeline
     */
    protected function updateOrderTracking(Order $order, string $trackingNumber, string $courier, string $service, string $bookingId): void
    {
        $order->update([
            'tracking_number' => $trackingNumber,
            'shipping_courier' => $courier,
            'shipping_service' => $service,
            'order_status' => 'shipped',
            'shipped_at' => now(),
        ]);

        $desc = ($trackingNumber === $bookingId)
            ? "Paket telah dijadwalkan pickup kurir {$courier} via KiriminAja. No. Booking / Resi: {$trackingNumber}."
            : "Paket telah dijadwalkan pickup kurir {$courier} via KiriminAja. No. Resi: {$trackingNumber} (ID Booking: {$bookingId}).";

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'shipped',
            'title' => 'Pesanan Dikirim dengan ' . $courier,
            'description' => $desc,
        ]);
    }

    /**
     * Estimasi tarif kurir realistis berdasarkan kota (Fallback ketika offline/testing)
     */
    protected function generateFallbackRates(string $destinationCity): array
    {
        $city = strtolower($destinationCity);
        $isJabodetabek = Str::contains($city, ['jakarta', 'bogor', 'depok', 'tangerang', 'bekasi']);
        $isJava = Str::contains($city, ['bandung', 'semarang', 'surabaya', 'yogyakarta', 'solo', 'malang']);

        $baseJne = $isJabodetabek ? 12000 : ($isJava ? 18000 : 28000);
        $baseJnt = $isJabodetabek ? 11000 : ($isJava ? 17000 : 27000);
        $baseSicepat = $isJabodetabek ? 12500 : ($isJava ? 18500 : 29000);
        $baseAnteraja = $isJabodetabek ? 10000 : ($isJava ? 16000 : 26000);

        return [
            [
                'courier' => 'JNE',
                'service' => 'REG',
                'service_name' => 'JNE Reguler',
                'cost' => (float) $baseJne,
                'etd' => $isJabodetabek ? '1-2 Hari' : ($isJava ? '2-3 Hari' : '3-5 Hari'),
                'description' => 'Layanan reguler terpercaya ke seluruh pelosok Indonesia',
            ],
            [
                'courier' => 'JNE',
                'service' => 'YES',
                'service_name' => 'JNE YES (Yakin Esok Sampai)',
                'cost' => (float) ($baseJne + 12000),
                'etd' => '1 Hari Kerja',
                'description' => 'Pengiriman kilat premium 1 hari tiba',
            ],
            [
                'courier' => 'SICEPAT',
                'service' => 'REG',
                'service_name' => 'SiCepat Regular',
                'cost' => (float) $baseSicepat,
                'etd' => $isJabodetabek ? '1-2 Hari' : ($isJava ? '2-3 Hari' : '3-4 Hari'),
                'description' => 'Pengiriman cepat dengan notifikasi SMS real-time',
            ],
            [
                'courier' => 'J&T',
                'service' => 'EZ',
                'service_name' => 'J&T Express EZ',
                'cost' => (float) $baseJnt,
                'etd' => $isJabodetabek ? '1-2 Hari' : ($isJava ? '2-3 Hari' : '3-5 Hari'),
                'description' => 'Operasional 365 hari tanpa libur',
            ],
            [
                'courier' => 'ANTERAJA',
                'service' => 'REG',
                'service_name' => 'Anteraja Regular',
                'cost' => (float) $baseAnteraja,
                'etd' => $isJabodetabek ? '1-2 Hari' : ($isJava ? '2-3 Hari' : '3-5 Hari'),
                'description' => 'Layanan hemat dan efisien oleh Satria Anteraja',
            ],
        ];
    }

    /**
     * Resolusi ID Kota / Kecamatan untuk KiriminAja
     */
    protected function resolveCityId(string $cityName): int
    {
        $city = strtolower(trim($cityName));
        $map = [
            'jakarta pusat' => 151,
            'jakarta utara' => 154,
            'jakarta barat' => 153,
            'jakarta selatan' => 152,
            'jakarta timur' => 155,
            'surabaya' => 444,
            'bandung' => 23,
            'semarang' => 399,
            'yogyakarta' => 501,
            'jogja' => 501,
            'solo' => 445,
            'surakarta' => 445,
            'malang' => 256,
            'denpasar' => 114,
            'badung' => 17,
            'bali' => 114,
            'medan' => 278,
            'makassar' => 254,
            'palembang' => 327,
            'tangerang selatan' => 457,
            'tangerang' => 455,
            'bekasi' => 55,
            'depok' => 115,
            'bogor' => 79,
        ];

        foreach ($map as $key => $id) {
            if (str_contains($city, $key)) {
                return $id;
            }
        }

        return 151; // Default Jakarta Pusat
    }
}
