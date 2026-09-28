<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Tests\TestCase;

class WebWishlistTest extends TestCase
{
    protected User $user;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        if (User::where('email', 'user@aromapalace.com')->doesntExist()) {
            $this->artisan('db:seed');
        }

        $this->user = User::where('email', 'user@aromapalace.com')->first();
        $this->product = Product::where('is_active', true)->first();
    }

    public function test_guest_cannot_view_wishlist_page_and_is_redirected_to_login(): void
    {
        $response = $this->get(route('wishlist.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_guest_cannot_toggle_wishlist_and_receives_401(): void
    {
        $response = $this->postJson(route('wishlist.toggle'), [
            'product_id' => $this->product->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_toggle_and_persist_wishlist(): void
    {
        Wishlist::where('user_id', $this->user->id)->delete();

        // Toggle ON
        $response = $this->actingAs($this->user)->postJson(route('wishlist.toggle'), [
            'product_id' => $this->product->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_wishlisted' => true,
                'count' => 1,
            ]);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        // Toggle OFF
        $response2 = $this->actingAs($this->user)->postJson(route('wishlist.toggle'), [
            'product_id' => $this->product->id,
        ]);

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_wishlisted' => false,
                'count' => 0,
            ]);

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_user_can_delete_item_from_wishlist_page(): void
    {
        Wishlist::firstOrCreate([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->actingAs($this->user)->delete(route('wishlist.destroy', $this->product->id));
        $response->assertRedirect();

        $this->assertDatabaseMissing('wishlists', [
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_api_v1_wishlist_endpoints(): void
    {
        $token = $this->user->createToken('test-token')->plainTextToken;

        // 1. Store/Toggle via API
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/wishlist', [
                'product_id' => $this->product->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_wishlisted', true);

        // 2. Index via API
        $indexRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/wishlist');

        $indexRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // 3. Move to cart via API
        $moveRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson("/api/v1/wishlist/{$this->product->id}/move-to-cart");

        $moveRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // 4. Mobile Sync endpoint
        $syncRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/wishlist/sync', [
                'product_ids' => [$this->product->id],
            ]);

        $syncRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.synced_items', 1);
    }
}

