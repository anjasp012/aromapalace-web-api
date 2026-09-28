<?php

namespace Tests\Feature;

use App\Models\Membership;
use App\Models\Product;
use App\Models\Reward;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AromaPalaceApiTest extends TestCase
{
    protected string $token;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed data jika belum ada
        if (User::where('email', 'user@aromapalace.com')->doesntExist()) {
            $this->artisan('db:seed');
        }

        $this->user = User::where('email', 'user@aromapalace.com')->first();
        $this->token = $this->user->createToken('TestToken')->plainTextToken;

        // Reset keranjang untuk keandalan test berulang
        $cart = \App\Models\Cart::where('user_id', $this->user->id)->first();
        if ($cart) {
            $cart->items()->delete();
            $cart->update(['applied_promo_code' => null]);
        }
        Product::query()->update(['stock' => 50]);
        \App\Models\ProductVariant::query()->update(['stock' => 50]);
        Membership::where('user_id', $this->user->id)->update(['points' => 500]);
    }

    public function test_health_check_endpoint(): void
    {
        $response = $this->getJson('/');
        $response->assertStatus(200)
            ->assertJsonPath('name', 'Aroma Palace Web & Mobile API');
    }

    public function test_auth_login_and_me(): void
    {
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@aromapalace.com',
            'password' => 'password123',
        ]);

        $loginRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $token = $loginRes->json('data.token');

        $meRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $meRes->assertStatus(200)
            ->assertJsonPath('data.user.email', 'user@aromapalace.com');
        $this->assertContains($meRes->json('data.membership.tier'), ['Silver', 'Gold', 'Platinum']);
    }

    public function test_home_screen_payload(): void
    {
        $response = $this->getJson('/api/v1/home');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'banners',
                    'categories',
                    'recommended_products',
                    'popular_products',
                    'discount_products',
                    'featured_brands',
                    'exclusive_promos',
                ],
            ]);
    }

    public function test_product_catalog_and_filter(): void
    {
        // All products
        $response = $this->getJson('/api/v1/products');
        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        // Filter discount & sort
        $filtered = $this->getJson('/api/v1/products?is_discount=true&sort_by=price_asc');
        $filtered->assertStatus(200);

        // Product detail
        $product = Product::first();
        $detail = $this->getJson("/api/v1/products/{$product->slug}");
        $detail->assertStatus(200)
            ->assertJsonPath('data.product.id', $product->id)
            ->assertJsonStructure(['data' => ['product', 'rating_summary', 'share']]);
    }

    public function test_store_locator_nearby_with_postgis(): void
    {
        // Koordinat Jakarta Pusat (-6.1953, 106.8207)
        $response = $this->getJson('/api/v1/stores?lat=-6.1953&lng=106.8207&radius=10000');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $stores = $response->json('data');
        $this->assertNotEmpty($stores);
        $this->assertArrayHasKey('distance_meters', $stores[0]);
    }

    public function test_cart_and_checkout_flow(): void
    {
        $product = Product::where('slug', 'replica-jazz-club-edt')->with('variants')->first();
        $variant = $product->variants->first();

        // 1. Tambah ke keranjang
        $addCart = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/cart', [
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'quantity' => 1,
            ]);
        $addCart->assertStatus(201);

        // 2. Lihat keranjang
        $getCart = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/cart');
        $getCart->assertStatus(200)
            ->assertJsonPath('data.total_items', 1);

        // 3. Pasang promo code
        $promoRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/cart/apply-promo', [
                'promo_code' => 'AROMA20',
            ]);
        $promoRes->assertStatus(200);

        // 4. Checkout Preview
        $preview = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/checkout/preview', [
                'fulfillment_type' => 'home_delivery',
                'shipping_service' => 'REG',
            ]);
        $preview->assertStatus(200)
            ->assertJsonStructure(['data' => ['items', 'subtotal', 'shipping_cost', 'discount_amount', 'total_amount']]);

        // 5. Place Order (Store Pickup)
        $store = Store::first();
        $orderRes = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->postJson('/api/v1/checkout/process', [
                'fulfillment_type' => 'store_pickup',
                'store_id' => $store->id,
                'payment_method' => 'bca_va',
            ]);

        $orderRes->assertStatus(201)
            ->assertJsonPath('success', true);

        $orderNumber = $orderRes->json('data.order.order_number');
        $this->assertNotEmpty($orderNumber);

        // 6. Cek Status Pembayaran
        $paymentStatus = $this->getJson("/api/v1/payments/status/{$orderNumber}");
        $paymentStatus->assertStatus(200)
            ->assertJsonPath('data.payment_status', 'unpaid');

        // 7. Simulasi Pembayaran Sukses
        $simPay = $this->postJson("/api/v1/payments/simulate/{$orderNumber}", [
            'status' => 'success',
        ]);
        $simPay->assertStatus(200)
            ->assertJsonPath('data.payment_status', 'paid')
            ->assertJsonPath('data.order_status', 'ready_for_pickup');

        // 8. Cek Order Tracking
        $tracking = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/orders/{$orderNumber}/tracking");
        $tracking->assertStatus(200)
            ->assertJsonPath('data.order_status', 'ready_for_pickup');

        // 9. Cek Store Pickup Status
        $pickup = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson("/api/v1/pickup/{$orderNumber}/status");
        $pickup->assertStatus(200)
            ->assertJsonPath('data.is_ready_to_collect', true);
    }

    public function test_membership_and_rewards_redemption(): void
    {
        // Cek status member (otomatis Platinum setelah akumulasi belanja transaksi sebelumnya > 5jt)
        $status = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/membership/status');
        $status->assertStatus(200);
        $this->assertEquals('Member', $status->json('data.tier'));

        // List rewards
        $rewards = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/membership/rewards');
        $rewards->assertStatus(200);

        // Redeem 1st reward
        $reward = Reward::first();
        if ($reward) {
            $redeem = $this->withHeader('Authorization', "Bearer {$this->token}")
                ->postJson("/api/v1/membership/rewards/{$reward->id}/redeem");
            $redeem->assertStatus(200)
                ->assertJsonStructure(['data' => ['voucher_code', 'remaining_points']]);
        }
    }

    public function test_beauty_content_and_search_suggestions(): void
    {
        // Articles
        $articles = $this->getJson('/api/v1/beauty-content/articles');
        $articles->assertStatus(200);

        // Topics
        $topics = $this->getJson('/api/v1/beauty-content/topics');
        $topics->assertStatus(200);

        // Search suggestions
        $search = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/search/suggestions?q=Jazz');
        $search->assertStatus(200)
            ->assertJsonStructure(['data' => ['products', 'brands']]);

        // Recent search history
        $history = $this->withHeader('Authorization', "Bearer {$this->token}")
            ->getJson('/api/v1/search/history');
        $history->assertStatus(200);
    }
}
