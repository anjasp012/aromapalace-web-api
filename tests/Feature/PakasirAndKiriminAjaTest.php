<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\KiriminAjaShippingService;
use App\Services\PakasirPaymentService;
use Tests\TestCase;

class PakasirAndKiriminAjaTest extends TestCase
{
    protected User $user;
    protected User $admin;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        if (User::where('email', 'user@aromapalace.com')->doesntExist()) {
            $this->artisan('db:seed');
        }

        $this->user = User::where('email', 'user@aromapalace.com')->first();
        $this->admin = User::where('email', 'admin@aromapalace.com')->first();
        $this->product = Product::first();

        // Reset user cart
        $cart = \App\Models\Cart::where('user_id', $this->user->id)->first();
        if ($cart) {
            $cart->items()->delete();
        }
    }

    public function test_kiriminaja_shipping_rates_calculation(): void
    {
        $service = app(KiriminAjaShippingService::class);
        $rates = $service->getShippingRates('Surabaya', 1000);

        $this->assertNotEmpty($rates);
        $this->assertArrayHasKey('courier', $rates[0]);
        $this->assertArrayHasKey('cost', $rates[0]);
        $this->assertArrayHasKey('etd', $rates[0]);
    }

    public function test_checkout_preview_and_order_placement_with_pakasir(): void
    {
        $this->actingAs($this->user);

        // Add to cart
        $this->postJson('/cart', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        // Dynamic checkout calculate
        $calcRes = $this->postJson('/checkout/calculate', [
            'fulfillment_type' => 'home_delivery',
            'shipping_courier' => 'JNE',
            'shipping_service' => 'REG',
        ]);

        $calcRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['available_couriers', 'available_payment_methods', 'shipping_cost']]);

        // Place order with QRIS via Pakasir
        $orderRes = $this->postJson('/checkout', [
            'fulfillment_type' => 'home_delivery',
            'address_id' => $this->user->primaryAddress->id,
            'shipping_courier' => 'JNE',
            'shipping_service' => 'REG',
            'payment_method' => 'qris',
        ]);

        $orderRes->assertStatus(200)
            ->assertJsonPath('success', true);

        $orderId = $orderRes->json('order.id');
        $order = Order::find($orderId);

        $this->assertNotNull($order);
        $this->assertEquals('pending_payment', $order->order_status);

        // Assert payment record has gateway pakasir
        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals('pakasir', $payment->payment_gateway);
        $this->assertEquals('qris', $payment->payment_type);
        $this->assertNotEmpty($payment->qr_string);
    }

    public function test_pakasir_webhook_settlement(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'AP-TEST-PKS-' . uniqid(),
            'order_status' => 'pending_payment',
            'payment_status' => 'unpaid',
            'subtotal' => 500000,
            'shipping_cost' => 0,
            'total_amount' => 500000,
            'fulfillment_type' => 'home_delivery',
            'payment_method' => 'qris',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_gateway' => 'pakasir',
            'transaction_id' => 'PKS-TRX-' . rand(1000, 9999),
            'payment_type' => 'qris',
            'gross_amount' => 500000,
            'transaction_status' => 'pending',
        ]);

        // Send Pakasir webhook
        $response = $this->postJson('/api/v1/payments/pakasir/webhook', [
            'order_id' => $order->order_number,
            'transaction_id' => 'PKS-SETTLED-12345',
            'status' => 'settlement',
            'payment_method' => 'qris',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->order_status);
    }

    public function test_admin_generate_kiriminaja_awb_and_tracking_webhook(): void
    {
        $order = Order::create([
            'user_id' => $this->user->id,
            'order_number' => 'AP-TEST-KA-' . uniqid(),
            'fulfillment_type' => 'home_delivery',
            'shipping_courier' => 'JNE',
            'shipping_service' => 'REG',
            'order_status' => 'processing',
            'payment_status' => 'paid',
            'subtotal' => 750000,
            'shipping_cost' => 15000,
            'total_amount' => 765000,
            'payment_method' => 'bca_va',
        ]);

        // Admin invokes KiriminAja AWB generation
        $this->actingAs($this->admin);
        $awbRes = $this->post("/admin/orders/{$order->id}/kiriminaja-awb");
        $awbRes->assertRedirect();

        $order->refresh();
        $this->assertEquals('shipped', $order->order_status);
        $this->assertNotEmpty($order->tracking_number);
        $this->assertStringContainsString('KA-JNE', $order->tracking_number);

        // KiriminAja webhook simulation for delivered package
        $trackRes = $this->postJson('/api/v1/shipping/kiriminaja/webhook', [
            'order_id' => $order->order_number,
            'awb' => $order->tracking_number,
            'status' => 'delivered',
            'note' => 'Paket diterima oleh Pak Satpam',
        ]);

        $trackRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $order->refresh();
        $this->assertEquals('completed', $order->order_status);
    }
}
