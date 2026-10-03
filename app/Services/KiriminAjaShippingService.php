<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KiriminAjaShippingService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $senderName;
    protected string $senderPhone;
    protected int $senderCityId;

    public function __construct()
    {
        $this->baseUrl = config('services.kiriminaja.base_url', 'https://tdev.kiriminaja.com/api/v3');
        $this->apiKey = config('services.kiriminaja.api_key', 'demo_kiriminaja_key');
        $this->senderName = config('services.kiriminaja.sender_name', 'Aroma Palace Haute Parfumerie');
        $this->senderPhone = config('services.kiriminaja.sender_phone', '081100001111');
        $this->senderCityId = (int) config('services.kiriminaja.sender_city_id', 151); // Jakarta Pusat
    }

    protected function getMitraBaseUrl(): string
    {
        $base = rtrim($this->baseUrl, '/');
        if (str_ends_with($base, '/api/mitra')) {
            return $base;
        }
        return $base . '/api/mitra';
    }

    /**
     * Cek tarif ongkos kirim agregator kurir (JNE, J&T, SiCepat, Anteraja, dll.)
     */
    /**
     * Cek tarif ongkos kirim agregator kurir (JNE, J&T, SiCepat, Anteraja, dll.)
     */
    public function getShippingRates(string $destinationCity, int $weightInGrams = 1000): array
    {
        // Jika mode live dengan API key asli, panggil KiriminAja API
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
                Log::warning('KiriminAja API rate calculation error: ' . $e->getMessage());
            }
        }

        // Realistic Fallback / Sandbox Rates berdasarkan kota tujuan
        return $this->generateFallbackRates($destinationCity);
    }

    /**
     * Ambil daftar layanan kurir aktif beserta tarif real-time dari API KiriminAja
     */
    public function getLivePricingServices(int $originCityId, int $destCityId, int $weightInGrams = 1000, array $couriers = []): array
    {
        if (empty($couriers)) {
            $couriers = ['jne', 'sicepat', 'jnt', 'anteraja', 'ninja', 'idexpress', 'sap', 'lion'];
        }

        $payload = [
            'origin' => $originCityId,
            'destination' => $destCityId,
            'weight' => max(100, $weightInGrams),
            'courier' => $couriers,
        ];

        $mitraBase = $this->getMitraBaseUrl();

        // Coba endpoint v6.1 terlebih dahulu
        $response = Http::timeout(8)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])
            ->post("{$mitraBase}/v6.1/shipping_price", $payload);

        // Fallback ke endpoint legacy jika v6.1 gagal
        if (!$response->successful() || !$response->json('status')) {
            $response = Http::timeout(8)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$mitraBase}/shipping_price", $payload);
        }

        if ($response->status() === 401) {
            $resJson = $response->json();
            if (isset($resJson['your_ip'])) {
                Log::warning("KiriminAja IP Whitelist blocked IP {$resJson['your_ip']}. Tambahkan IP ini ke Dashboard KiriminAja -> Integrasi -> IP Whitelist.");
            }
        }

        if ($response->successful() && $response->json('status')) {
            $rates = $response->json('results') ?? $response->json('data') ?? $response->json('datas') ?? [];
            if (is_array($rates)) {
                return $rates;
            }
        }

        return [];
    }

    /**
     * Buat pemesanan penjemputan paket dan generate nomor resi kurir (AWB Booking)
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

            // 1. Cek layanan dan kurir aktif langsung dari KiriminAja pricing API
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
                // Cari yang paling cocok dengan pilihan layanan pelanggan (misal: reg/ctc/ez/siunt)
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
                // Tambahkan semua rate lain dari kurir yang sama
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

            // Tahap C: Jika kurir pilihan pelanggan tidak aktif di akun KiriminAja (misal JNE belum di-whitelist/aktif),
            // siapkan alternatif kurir yang benar-benar AKTIF dari live rates (SiCepat, J&T, Ninja, dll.)
            foreach ($liveRates as $r) {
                if (strtolower($r['service'] ?? '') !== $courier && !empty($r['service']) && !empty($r['service_type'])) {
                    $addAttempt($r['service'], $r['service_type'], (int) ($r['cost'] ?? 0), true);
                }
            }

            // Tahap D: Fallback sandbox umum jika live pricing kosong / offline
            $addAttempt('sicepat', 'SIUNT', 0, true);
            $addAttempt('jnt', 'EZ', 0, true);
            $addAttempt('ninja', 'STANDARD', 0, true);

            // 3. Susun data paket
            $prefix = config('services.kiriminaja.order_prefix', '');
            $rawOrder = $order->order_number;
            if (!empty($prefix)) {
                if (!str_starts_with($rawOrder, $prefix)) {
                    $cleanSuffix = preg_replace('/^[A-Za-z0-9]+-?/', '', $rawOrder);
                    $kiriminAjaOrderId = $prefix . $cleanSuffix;
                } else {
                    $kiriminAjaOrderId = $rawOrder;
                }
            } else {
                $kiriminAjaOrderId = $rawOrder;
            }

            $pickupSchedule = $this->resolvePickupSchedule();

            $basePackageData = [
                'order_id' => (string) Str::limit($kiriminAjaOrderId, 20, ''),
                'destination_name' => (string) Str::limit($recipientName, 50, ''),
                'destination_phone' => (string) Str::limit(preg_replace('/[^0-9]/', '', $recipientPhone), 15, ''),
                'destination_address' => (string) Str::limit($destinationAddress, 200, ''),
                'destination_kecamatan_id' => $destCityId,
                'destination_zipcode' => $destinationPostal,
                'weight' => $packageWeight,
                'width' => 10,
                'length' => 10,
                'height' => 10,
                'qty' => max(1, (int) $order->items->sum('quantity')),
                'item_value' => (int) round($order->total_amount),
                'shipping_cost' => (int) round($order->shipping_cost),
                'item_name' => 'Parfum Eksklusif Aroma Palace (Haute Fragrance)',
                'cod' => 0,
                'package_type_id' => 1,
            ];

            $mitraBase = $this->getMitraBaseUrl();
            $successfulResponse = null;
            $succeededCourier = $courier;
            $succeededService = $service;
            $wasAdapted = false;
            $lastError = 'Gagal menghubungi KiriminAja API';

            // 4. Eksekusi request_pickup dengan antrian candidates
            foreach ($attempts as $attempt) {
                $currCourier = $attempt['service'];
                $currServiceType = $attempt['service_type'];
                $currCost = ($attempt['cost'] > 0) ? $attempt['cost'] : (int) round($order->shipping_cost);

                $pkg = array_merge($basePackageData, [
                    'service' => $currCourier,
                    'service_type' => $currServiceType,
                    'shipping_cost' => $currCost,
                ]);

                $pickupPayload = [
                    'address' => 'Jl. M.H. Thamrin No. 88, Menteng, Jakarta Pusat',
                    'phone' => $this->senderPhone,
                    'name' => $this->senderName,
                    'zipcode' => '10350',
                    'kecamatan_id' => $this->senderCityId,
                    'schedule' => $pickupSchedule,
                    'packages' => [$pkg],
                ];

                // Coba endpoint v6.1 terlebih dahulu
                $res = Http::timeout(10)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post("{$mitraBase}/v6.1/request_pickup", $pickupPayload);

                // Coba endpoint legacy jika v6.1 gagal
                if (!$res->successful() || !$res->json('status')) {
                    $res = Http::timeout(10)
                        ->withHeaders([
                            'Authorization' => 'Bearer ' . $this->apiKey,
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                        ])
                        ->post("{$mitraBase}/request_pickup", $pickupPayload);
                }

                if ($res->successful() && $res->json('status')) {
                    $successfulResponse = $res;
                    $succeededCourier = $currCourier;
                    $succeededService = $currServiceType;
                    $wasAdapted = !empty($attempt['is_alternative']) || ($currCourier !== $courier);
                    break;
                }

                // Catat pesan error
                $json = $res->json() ?? [];
                $lastError = $json['text'] ?? ($json['message'] ?? ("HTTP {$res->status()}"));
                if (isset($json['your_ip'])) {
                    $lastError .= " (IP Server: {$json['your_ip']} belum di-whitelist di Dashboard KiriminAja)";
                    // Jika IP terblokir, tidak perlu loop berulang kali
                    break;
                }
            }

            // 5. Jika seluruh percobaan gagal, lempar exception
            if (!$successfulResponse) {
                if (str_contains(strtolower($lastError), 'tidak tersedia')) {
                    $lastError .= ". Pastikan ekspedisi " . strtoupper($courier) . " telah diaktifkan di Dashboard KiriminAja -> Pengaturan / Integrasi -> Ekspedisi, atau pastikan saldo KA Pay mencukupi.";
                }
                Log::warning("KiriminAja request_pickup failed after attempts: {$lastError}");
                throw new Exception($lastError);
            }

            // 6. Parsing response sukses dan ambil nomor AWB / Resi resmi
            $data = $successfulResponse->json('data') ?? [];
            $trackingNumber = null;
            $bookingId = null;

            if (is_array($data)) {
                if (isset($data['awb']) || isset($data['booking_id'])) {
                    $trackingNumber = $data['awb'] ?? null;
                    $bookingId = $data['booking_id'] ?? null;
                } elseif (isset($data['details'][0])) {
                    $first = $data['details'][0];
                    $trackingNumber = $first['awb'] ?? ($first['tracking_number'] ?? null);
                    $bookingId = $first['booking_id'] ?? ($data['pickup_number'] ?? null);
                } elseif (isset($data[0])) {
                    $first = $data[0];
                    $trackingNumber = $first['awb'] ?? ($first['tracking_number'] ?? null);
                    $bookingId = $first['booking_id'] ?? null;
                }
            }

            $trackingNumber = $trackingNumber ?: ($data['pickup_number'] ?? null);
            $bookingId = $bookingId ?: ($data['pickup_number'] ?? ('BKG-' . Str::random(8)));

            if (empty($trackingNumber)) {
                throw new Exception("KiriminAja berhasil menerima penjemputan namun belum mengembalikan nomor resi.");
            }

            // Simpan resi ke order
            $this->updateOrderTracking($order, $trackingNumber, strtoupper($succeededCourier), strtoupper($succeededService), $bookingId);

            // Jika kurir dialihkan karena kurir asli tidak aktif di KiriminAja
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

        // Simulasi Resi Resmi Kurir Otomatis (Hanya untuk testing/mock development)
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
     * Dapatkan jadwal pickup kurir dalam format mandatory: Y-m-d H:i:s
     */
    public function resolvePickupSchedule(): string
    {
        $now = now()->timezone('Asia/Jakarta');

        // Coba periksa slot jadwal resmi dari API KiriminAja
        try {
            $mitraBase = $this->getMitraBaseUrl();
            $res = Http::timeout(4)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->post("{$mitraBase}/v2/schedules");

            if ($res->successful() && $res->json('status')) {
                $schedules = $res->json('schedules') ?? [];
                foreach ($schedules as $slot) {
                    if (empty($slot['expired']) && empty($slot['libur']) && !empty($slot['clock'])) {
                        $clock = trim($slot['clock']);
                        if (strlen($clock) === 5) {
                            $clock .= ':00';
                        }
                        return $now->format('Y-m-d') . ' ' . $clock;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::info('KiriminAja schedules check notice: ' . $e->getMessage());
        }

        // Fallback waktu yang valid (format: Y-m-d H:i:s)
        if ($now->hour >= 16) {
            return $now->copy()->addDay()->setTime(10, 0, 0)->format('Y-m-d H:i:s');
        }
        if ($now->hour < 9) {
            return $now->copy()->setTime(11, 0, 0)->format('Y-m-d H:i:s');
        }

        return $now->copy()->addHours(2)->format('Y-m-d H:i:00');
    }

    /**
     * Map service type agar valid sesuai ketentuan kurir KiriminAja
     */
    protected function resolveServiceType(string $courier, string $service, int $originId, int $destId): string
    {
        $courier = strtolower($courier);
        $service = strtolower($service);

        // JNE: Intra-DKI Jakarta / sesama kota menggunakan CTC (City to City), bukan REG
        if ($courier === 'jne') {
            $isJakartaOrigin = in_array($originId, [151, 152, 153, 154, 155]);
            $isJakartaDest = in_array($destId, [151, 152, 153, 154, 155]);

            if ($isJakartaOrigin && $isJakartaDest) {
                if (in_array($service, ['reg', 'regular', 'standard'])) {
                    return 'ctc';
                }
                if ($service === 'yes') {
                    return 'ctcyes';
                }
                if ($service === 'oke') {
                    return 'ctcoke';
                }
            }
        }

        // J&T: Layanan standar adalah EZ
        if ($courier === 'jnt' || $courier === 'j&t') {
            if (in_array($service, ['reg', 'regular', 'standard'])) {
                return 'ez';
            }
        }

        return $service;
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

        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => 'shipped',
            'title' => 'Pesanan Dikirim dengan ' . $courier,
            'description' => "Paket telah di-pickup kurir {$courier} via KiriminAja. No. Resi: {$trackingNumber} (ID Booking: {$bookingId})",
        ]);
    }

    /**
     * Lacak milestone status pengiriman
     */
    public function trackShipment(string $courier, string $trackingNumber): array
    {
        if ($this->apiKey !== 'demo_kiriminaja_key' && !app()->environment('testing')) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders(['Authorization' => 'Bearer ' . $this->apiKey])
                    ->get("{$this->baseUrl}/tracking", [
                        'courier' => strtolower($courier),
                        'awb' => $trackingNumber,
                    ]);

                if ($response->successful()) {
                    return $response->json('data') ?? [];
                }
            } catch (Exception $e) {
                Log::warning('KiriminAja trackShipment error: ' . $e->getMessage());
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
            if ($orderNumber) $q->where('order_number', $orderNumber);
            if ($trackingNumber) $q->orWhere('tracking_number', $trackingNumber);
        })->first();

        if (!$order) {
            return ['status' => 'ignored', 'message' => 'Pesanan tidak ditemukan'];
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
     * Estimasi tarif kurir realistis berdasarkan kota
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
