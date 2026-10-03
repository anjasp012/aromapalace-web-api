<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PakasirPaymentService
{
    protected string $baseUrl;
    protected string $project;
    protected string $apiKey;
    protected string $webhookSecret;

    public function __construct(
        protected PaymentService $paymentService
    ) {
        $this->baseUrl = config('services.pakasir.base_url', 'https://app.pakasir.com');
        $this->project = config('services.pakasir.project', 'aromapalace-id');
        $this->apiKey = config('services.pakasir.api_key', 'demo_pakasir_key');
        $this->webhookSecret = config('services.pakasir.webhook_secret', '');
    }

    /**
     * Inisialisasi pembayaran ke Pakasir Gateway
     */
    public function createPayment(Order $order, string $paymentMethod = 'qris'): array
    {
        $amount = (int) round($order->total_amount);
        $orderNumber = $order->order_number;

        // Map method bila diperlukan (Pakasir v2 support: qris, bri_va, bni_va, permata_va, maybank_va, bnc_va, payment_link)
        $targetMethod = match ($paymentMethod) {
            'bri_va' => 'bri_va',
            'bni_va' => 'bni_va',
            'permata_va' => 'permata_va',
            'maybank_va' => 'maybank_va',
            'bnc_va' => 'bnc_va',
            'payment_link' => 'payment_link',
            'bca_va', 'mandiri_va', 'cimb_niaga_va' => 'bri_va', // Graceful fallback jika akun belum mengaktifkan BCA/Mandiri
            default => 'qris',
        };

        // Jika mode live dengan API key asli, panggil HTTP API Pakasir v2
        if ($this->apiKey !== 'demo_pakasir_key' && !app()->environment('testing')) {
            try {
                $endpoint = rtrim($this->baseUrl, '/') . "/api/v2/create-transaction/{$this->project}/{$orderNumber}";
                $response = Http::timeout(10)
                    ->withHeaders([
                        'X-Api-Key' => $this->apiKey,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ])
                    ->post($endpoint, [
                        'method' => $targetMethod,
                        'amount' => $amount,
                    ]);

                if ($response->successful()) {
                    $resData = $response->json('data') ?? $response->json();
                    return $this->savePaymentRecord($order, $paymentMethod, $amount, $resData);
                }

                Log::warning("Pakasir API create-transaction failed [HTTP {$response->status()}]: " . $response->body(), [
                    'endpoint' => $endpoint,
                    'order' => $orderNumber,
                    'method' => $targetMethod,
                    'amount' => $amount,
                ]);
            } catch (Exception $e) {
                Log::error('Pakasir API connection exception: ' . $e->getMessage(), [
                    'endpoint' => $endpoint ?? null,
                    'order' => $orderNumber,
                ]);
            }
        } else {
            Log::info('Pakasir running in simulation mode (API call skipped).', [
                'reason' => ($this->apiKey === 'demo_pakasir_key') ? 'API key is still demo_pakasir_key (check .env / config:cache)' : 'App environment is testing',
                'env' => app()->environment(),
                'api_key_configured' => ($this->apiKey !== 'demo_pakasir_key'),
            ]);
        }

        // Mode Sandbox / Simulasi Cerdas (Bila API key belum dikonfigurasi / pengujian lokal)
        $simulatedData = $this->generateSimulationData($orderNumber, $paymentMethod, $amount);
        return $this->savePaymentRecord($order, $paymentMethod, $amount, $simulatedData);
    }

    /**
     * Cek status transaksi pembayaran langsung ke server Pakasir
     */
    public function checkTransactionStatus(Order $order): array
    {
        $amount = (int) round($order->total_amount);
        $orderNumber = $order->order_number;

        if ($this->apiKey !== 'demo_pakasir_key' && !app()->environment('testing')) {
            try {
                $url = rtrim($this->baseUrl, '/') . "/api/transactiondetail";
                $response = Http::timeout(10)->get($url, [
                    'project' => $this->project,
                    'order_id' => $orderNumber,
                    'amount' => $amount,
                    'api_key' => $this->apiKey,
                ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $tx = $json['transaction'] ?? $json;
                    $status = strtolower($tx['status'] ?? '');

                    if (in_array($status, ['completed', 'settlement', 'paid', 'success', 'capture'])) {
                        $this->paymentService->processSettlement(
                            $orderNumber,
                            $tx['txn_id'] ?? null,
                            $tx['payment_method'] ?? $order->payment_method
                        );

                        return [
                            'success' => true,
                            'paid' => true,
                            'status' => 'paid',
                            'message' => 'Pembayaran berhasil dikonfirmasi dari Pakasir.',
                        ];
                    }

                    return [
                        'success' => true,
                        'paid' => false,
                        'status' => $status ?: 'pending',
                        'message' => 'Status pembayaran di Pakasir saat ini: ' . ($status ?: 'pending'),
                    ];
                }
            } catch (Exception $e) {
                Log::warning('Pakasir checkTransactionStatus error: ' . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'paid' => ($order->payment_status === 'paid'),
            'status' => $order->payment_status,
            'message' => 'Status pembayaran saat ini: ' . $order->payment_status,
        ];
    }

    /**
     * Simpan / Perbarui data transaksi payment di database
     */
    protected function savePaymentRecord(Order $order, string $paymentMethod, int $amount, array $data): array
    {
        $transactionId = $data['txn_id'] ?? ($data['transaction_id'] ?? ('PKS-' . strtoupper(Str::random(12))));
        $vaNumber = $data['va_number'] ?? null;
        $qrString = $data['qr_string'] ?? null;
        $paymentUrl = $data['payment_link'] ?? ($data['payment_url'] ?? "https://app.pakasir.com/pay-v2/{$transactionId}");
        $isSandbox = $data['is_sandbox'] ?? false;
        $expiredAt = $data['expires_at'] ?? now()->addHours(24)->toIso8601String();

        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'payment_gateway' => 'pakasir',
                'transaction_id' => $transactionId,
                'payment_type' => $paymentMethod,
                'gross_amount' => $amount,
                'transaction_status' => 'pending',
                'va_number' => $vaNumber,
                'qr_string' => $qrString,
                'payload' => array_merge($data, [
                    'gateway' => 'pakasir',
                    'payment_url' => $paymentUrl,
                    'is_sandbox' => $isSandbox,
                ]),
            ]
        );

        return [
            'gateway' => 'pakasir',
            'transaction_id' => $transactionId,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'status' => 'pending',
            'va_number' => $vaNumber,
            'qr_string' => $qrString,
            'qr_image_url' => $qrString ? "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrString) : null,
            'payment_url' => $paymentUrl,
            'is_sandbox' => $isSandbox,
            'expired_at' => $expiredAt,
        ];
    }

    /**
     * Generate data simulasi sandbox Pakasir
     */
    protected function generateSimulationData(string $orderNumber, string $method, int $amount): array
    {
        $txId = 'PKS-' . strtoupper(Str::random(10));

        if ($method === 'qris') {
            return [
                'transaction_id' => $txId,
                'qr_string' => "00020101021226590014ID.LINKAJA.WWW01189360091800000000010215{$orderNumber}520459995303360540" . $amount . "5802ID5912AROMA PALACE6013JAKARTA PUSAT6304ABCD",
                'payment_url' => "https://app.pakasir.com/pay-v2/{$txId}",
                'is_sandbox' => true,
            ];
        }

        // Virtual Accounts
        $bankPrefix = match ($method) {
            'bca_va' => '12345',
            'mandiri_va' => '88908',
            'bni_va' => '98812',
            'bri_va' => '10298',
            'permata_va' => '89201',
            default => '77889',
        };

        return [
            'transaction_id' => $txId,
            'va_number' => $bankPrefix . rand(10000000, 99999999),
            'payment_url' => "https://app.pakasir.com/pay-v2/{$txId}",
            'is_sandbox' => true,
        ];
    }

    /**
     * Verifikasi integritas payload Webhook dari Pakasir
     */
    public function verifyWebhook(array $payload, ?string $signature = null, ?string $secret = null): bool
    {
        // Dalam mode demo / testing, izinkan lolos
        if ($this->apiKey === 'demo_pakasir_key' || app()->environment('testing')) {
            return true;
        }

        $data = $payload['transaction'] ?? $payload;

        // Cek kecocokan project jika ada di payload
        if (isset($data['project']) && $data['project'] !== $this->project) {
            return false;
        }

        // Verifikasi X-Secret dari Pakasir Dashboard
        if (!empty($this->webhookSecret) && !empty($secret)) {
            return hash_equals($this->webhookSecret, $secret);
        }

        // Validasi HMAC signature jika disertakan
        if ($signature) {
            $expected = hash_hmac('sha256', json_encode($payload), $this->apiKey);
            return hash_equals($expected, $signature);
        }

        // Jika webhookSecret dikonfigurasi tetapi secret kosong, tolak demi keamanan
        if (!empty($this->webhookSecret) && empty($secret)) {
            return false;
        }

        return true;
    }

    /**
     * Proses event webhook settlement dari Pakasir
     */
    public function processWebhook(array $payload): array
    {
        $data = $payload['transaction'] ?? $payload;

        $orderNumber = $data['order_id'] ?? ($data['order_number'] ?? null);
        $transactionId = $data['txn_id'] ?? ($data['transaction_id'] ?? null);
        $status = strtolower($data['status'] ?? ($data['transaction_status'] ?? 'settlement'));
        $paymentType = $data['payment_method'] ?? ($data['payment_type'] ?? 'qris');

        if (!$orderNumber) {
            throw new Exception('Payload webhook tidak memiliki order_id.');
        }

        if (in_array($status, ['completed', 'success', 'settlement', 'paid', 'capture'])) {
            $this->paymentService->processSettlement(
                $orderNumber,
                $transactionId ?? ('PKS-TX-' . Str::random(8)),
                $paymentType
            );

            // Tandai order menjadi processing
            $order = Order::where('order_number', $orderNumber)->first();
            if ($order && $order->order_status === 'pending_payment') {
                $order->update(['order_status' => 'processing']);
            }

            return [
                'status' => 'success',
                'order_number' => $orderNumber,
                'message' => 'Pembayaran pesanan berhasil dikonfirmasi via Pakasir.',
            ];
        }

        return [
            'status' => 'ignored',
            'order_number' => $orderNumber,
            'message' => "Status pembayaran '{$status}' tidak memerlukan aksi settlement.",
        ];
    }
}

