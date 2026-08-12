<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_is_rejected_with_an_empty_cart(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $user->cart()->firstOrCreate();

        $this->postJson('/api/checkout')->assertStatus(422)->assertJsonFragment(['message' => 'Your cart is empty']);
    }

    public function test_checkout_is_rejected_when_a_cart_item_exceeds_available_stock(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $product = Product::factory()->create(['stock' => 1]);
        $user->cart()->firstOrCreate()->items()->create([
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response = $this->postJson('/api/checkout');

        $response->assertStatus(422);
        $this->assertDatabaseCount('orders', 0);
    }
}
