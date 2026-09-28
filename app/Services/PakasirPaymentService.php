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

    public function __construct(
        protected PaymentService $paymentService
    ) {
        $this->baseUrl = config('services.pakasir.base_url', 'https://api.pakasir.com');
        $this->project = config('services.pakasir.project', 'aromapalace');
        $this->apiKey = config('services.pakasir.api_key', 'demo_pakasir_key');
    }

    /**
     * Inisialisasi pembayaran ke Pakasir Gateway
     */
    public function createPayment(Order $order, string $paymentMethod = 'qris'): array
    {
        $amount = (int) round($order->total_amount);
        $orderNumber = $order->order_number;

        // Jika mode live dengan API key asli, panggil HTTP API Pakasir
        if ($this->apiKey !== 'demo_pakasir_key' && !app()->environment('testing')) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Accept' => 'application/json',
                    ])
                    ->post("{$this->baseUrl}/v1/transaction/create", [
                        'project' => $this->project,
                        'order_id' => $orderNumber,
                        'amount' => $amount,
                        'payment_method' => $paymentMethod,
                        'customer_name' => $order->user->name ?? 'Pelanggan Aroma Palace',
                        'customer_email' => $order->user->email ?? 'customer@aromapalace.com',
                        'customer_phone' => $order->address->phone_number ?? ($order->user->phone ?? '08123456789'),
                        'description' => "Pembayaran Pesanan Aroma Palace #{$orderNumber}",
                    ]);

                if ($response->successful()) {
                    $resData = $response->json('data') ?? $response->json();
                    return $this->savePaymentRecord($order, $paymentMethod, $amount, $resData);
                }

                Log::warning('Pakasir API response error: ' . $response->body());
            } catch (Exception $e) {
                Log::error('Pakasir API connection error: ' . $e->getMessage());
            }
        }

        // Mode Sandbox / Simulasi Cerdas (Bila API key belum dikonfigurasi / pengujian lokal)
        $simulatedData = $this->generateSimulationData($orderNumber, $paymentMethod, $amount);
        return $this->savePaymentRecord($order, $paymentMethod, $amount, $simulatedData);
    }

    /**
     * Simpan / Perbarui data transaksi payment di database
     */
    protected function savePaymentRecord(Order $order, string $paymentMethod, int $amount, array $data): array
    {
        $transactionId = $data['transaction_id'] ?? ('PKS-' . strtoupper(Str::random(12)));
        $vaNumber = $data['va_number'] ?? null;
        $qrString = $data['qr_string'] ?? null;
        $paymentUrl = $data['payment_url'] ?? null;

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
            'expired_at' => now()->addHours(24)->toIso8601String(),
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
                'payment_url' => "https://app.pakasir.com/pay/{$txId}",
            ];
        }

        // Virtual Accounts
        $bankPrefix = match ($method) {
            'bca_va' => '12345',
            'mandiri_va' => '88908',
            'bni_va' => '98812',
            'bri_va' => '10298',
            default => '77889',
        };

        return [
            'transaction_id' => $txId,
            'va_number' => $bankPrefix . rand(10000000, 99999999),
            'payment_url' => "https://app.pakasir.com/pay/{$txId}",
        ];
    }

    /**
     * Verifikasi integritas payload Webhook dari Pakasir
     */
    public function verifyWebhook(array $payload, ?string $signature = null): bool
    {
        // Dalam mode demo / testing, izinkan lolos
        if ($this->apiKey === 'demo_pakasir_key' || app()->environment('testing')) {
            return true;
        }

        // Cek kecocokan project jika ada di payload
        if (isset($payload['project']) && $payload['project'] !== $this->project) {
            return false;
        }

        // Validasi HMAC signature jika disertakan
        if ($signature) {
            $expected = hash_hmac('sha256', json_encode($payload), $this->apiKey);
            return hash_equals($expected, $signature);
        }

        return true;
    }

    /**
     * Proses event webhook settlement dari Pakasir
     */
    public function processWebhook(array $payload): array
    {
        $orderNumber = $payload['order_id'] ?? ($payload['order_number'] ?? null);
        $transactionId = $payload['transaction_id'] ?? null;
        $status = strtolower($payload['status'] ?? ($payload['transaction_status'] ?? 'settlement'));
        $paymentType = $payload['payment_method'] ?? ($payload['payment_type'] ?? 'qris');

        if (!$orderNumber) {
            throw new Exception('Payload webhook tidak memiliki order_id.');
        }

        if (in_array($status, ['success', 'settlement', 'paid', 'capture'])) {
            $payment = $this->paymentService->processSettlement(
                $orderNumber,
                $transactionId ?? 'PKS-TX-' . Str::random(8),
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

