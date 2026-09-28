<?php

namespace Tests\Feature;

use App\Models\BeautyArticle;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class WebAndAdminViewsTest extends TestCase
{
    protected User $user;
    protected User $admin;
    protected Product $product;
    protected BeautyArticle $article;

    protected function setUp(): void
    {
        parent::setUp();

        if (User::where('email', 'user@aromapalace.com')->doesntExist()) {
            $this->artisan('db:seed');
        }

        $this->user = User::where('email', 'user@aromapalace.com')->first();
        $this->admin = User::where('email', 'admin@aromapalace.com')->first();
        $this->product = Product::first();
        $this->article = BeautyArticle::where('is_published', true)->first();
    }

    public function test_web_public_pages_render_successfully(): void
    {
        // Home
        $response = $this->get('/');
        $response->assertStatus(200);

        // Catalog
        $response = $this->get('/products');
        $response->assertStatus(200);

        // Product Detail
        if ($this->product) {
            $response = $this->get('/products/' . $this->product->slug);
            $response->assertStatus(200);
        }

        // Stores
        $response = $this->get('/stores');
        $response->assertStatus(200);

        // Articles
        $response = $this->get('/articles');
        $response->assertStatus(200);

        // Article Detail
        if ($this->article) {
            $response = $this->get('/articles/' . $this->article->slug);
            $response->assertStatus(200)
                ->assertSee('schema.org', false)
                ->assertSee('BlogPosting', false);
        }

        // Sitemap XML
        $sitemapRes = $this->get('/sitemap.xml');
        $sitemapRes->assertStatus(200)
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<urlset', false)
            ->assertSee('<loc>', false);

        // Auth pages for guests
        $this->get('/login')->assertStatus(200);
        $this->get('/register')->assertStatus(200);
    }

    public function test_web_authenticated_customer_pages_render_successfully(): void
    {
        $this->actingAs($this->user);

        $this->get('/cart')->assertStatus(200);
        $this->get('/account')->assertStatus(200);
        $this->get('/account/orders')->assertStatus(200);
        $this->get('/account/rewards')->assertStatus(200);
        $this->get('/account/addresses')->assertStatus(200);
    }

    public function test_customer_can_add_address_via_web(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/account/addresses', [
            'label' => 'Apartemen',
            'recipient_name' => 'Budi Santoso',
            'phone_number' => '081233445566',
            'city' => 'Jakarta Selatan',
            'postal_code' => '12190',
            'full_address' => 'Sudirman Central Business District Tower B No. 12',
            'notes' => 'Titip di resepsionis',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('user_addresses', [
            'user_id' => $this->user->id,
            'recipient_name' => 'Budi Santoso',
            'city' => 'Jakarta Selatan',
        ]);
    }

    public function test_admin_pages_access_control(): void
    {
        // Guest visiting admin login
        $this->get('/admin/login')->assertStatus(200);

        // Customer cannot access admin dashboard and is redirected
        $this->actingAs($this->user);
        $this->get('/admin')->assertRedirect(route('admin.login'));

        // Admin can access admin dashboard & modules
        $this->actingAs($this->admin);
        $this->get('/admin')->assertStatus(200);
        $this->get('/admin/products')->assertStatus(200);
        $this->get('/admin/orders')->assertStatus(200);
        $this->get('/admin/promotions')->assertStatus(200);
        $this->get('/admin/stores')->assertStatus(200);
        $this->get('/admin/articles')->assertStatus(200);
    }

    public function test_cart_ajax_operations_for_alpine(): void
    {
        $this->actingAs($this->user);

        // Add item via AJAX
        $addRes = $this->postJson('/cart', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
        $addRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // Get Cart Summary via AJAX
        $cartRes = $this->getJson('/cart');
        $cartRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['items', 'subtotal', 'total_amount']])
            ->assertJsonPath('data.total_items', 1)
            ->assertJsonPath('data.total_quantity', 2);

        $itemId = $cartRes->json('data.items.0.id');

        // Update quantity via AJAX
        $updateRes = $this->putJson("/cart/{$itemId}", [
            'quantity' => 3,
        ]);
        $updateRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.items.0.quantity', 3);

        // Remove item via AJAX
        $deleteRes = $this->deleteJson("/cart/{$itemId}");
        $deleteRes->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_web_auth_flow_shows_simple_toasts(): void
    {
        // 1. Login with success flash toast
        $loginRes = $this->post(route('login.submit'), [
            'email' => 'user@aromapalace.com',
            'password' => 'password123',
        ]);
        $loginRes->assertRedirect(route('home'));
        $loginRes->assertSessionHas('success', 'Berhasil masuk ke akun Anda.');

        // 2. Logout with success flash toast
        $logoutRes = $this->post(route('logout'));
        $logoutRes->assertRedirect(route('home'));
        $logoutRes->assertSessionHas('success', 'Berhasil keluar dari akun.');

        // 3. Register with success flash toast
        $randomEmail = 'testuser_' . uniqid() . '@aromapalace.com';
        $registerRes = $this->post(route('register.submit'), [
            'name' => 'Test Registered User',
            'email' => $randomEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $registerRes->assertRedirect(route('home'));
        $registerRes->assertSessionHas('success', 'Pendaftaran akun berhasil.');
    }

    public function test_guest_cannot_see_wishlist_on_product_detail_and_card_links_to_login(): void
    {
        // 1. Guest viewing product detail should NOT see the wishlist button
        $guestDetailRes = $this->get('/products/' . $this->product->slug);
        $guestDetailRes->assertStatus(200);
        $guestDetailRes->assertDontSee("toggleWishlist({$this->product->id}", false);

        // 2. Guest viewing product catalog should have love button linking to login
        $guestCatalogRes = $this->get('/products');
        $guestCatalogRes->assertStatus(200);
        $guestCatalogRes->assertSee('title="Masuk untuk menambahkan ke Wishlist"', false);
    }

    public function test_authenticated_user_can_see_wishlist_on_product_detail(): void
    {
        $authDetailRes = $this->actingAs($this->user)->get('/products/' . $this->product->slug);
        $authDetailRes->assertStatus(200);
        $authDetailRes->assertSee("toggleWishlist({$this->product->id}", false);
    }

    public function test_search_suggestions_returns_products_stores_and_articles(): void
    {
        $res = $this->getJson('/api/v1/search/suggestions?q=a');
        $res->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'products',
                    'categories',
                    'brands',
                    'stores',
                    'articles',
                ]
            ]);
    }

    public function test_mobile_bottom_navigation_bar_renders_with_tabs_and_badges(): void
    {
        // 1. Guest viewing home page
        $res = $this->get('/');
        $res->assertStatus(200)
            ->assertSee('aria-label="Navigasi Mobile"', false)
            ->assertSee('Beranda', false)
            ->assertSee('Katalog', false)
            ->assertSee('Wishlist', false)
            ->assertSee('Masuk', false)
            ->assertSee('id="mobile-wishlist-badge"', false)
            ->assertSee('id="cart-badge"', false)
            ->assertSee('aria-label="Open Navigation Menu"', false)
            ->assertSee('aria-label="Close Navigation Menu"', false);

        // 2. Authenticated user viewing catalog
        $authRes = $this->actingAs($this->user)->get('/products');
        $authRes->assertStatus(200)
            ->assertSee('Akun', false);
    }

    public function test_desktop_navigation_bar_renders_with_new_menu_items_and_structure(): void
    {
        $res = $this->get('/');
        $res->assertStatus(200)
            ->assertSee('Free Shipping', false)
            ->assertSee('Authentic Brands', false)
            ->assertSee('Search for perfumes...', false)
            ->assertSee('Account', false)
            ->assertSee('Wishlist', false)
            ->assertSee('Cart', false)
            ->assertSee('Home', false)
            ->assertSee('Shop', false)
            ->assertSee('Best Sellers', false)
            ->assertSee('Special Offers', false)
            ->assertSee('Store Locator', false)
            ->assertSee('About Us', false)
            ->assertSee('Contact', false);
    }
}


