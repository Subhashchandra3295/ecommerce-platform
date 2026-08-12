<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_add_a_product_to_their_cart(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_adding_the_same_product_twice_increments_quantity_instead_of_duplicating(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $product = Product::factory()->create(['stock' => 10]);

        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])->assertCreated();
        $this->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 2])->assertCreated();

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 3]);
    }

    public function test_a_user_cannot_modify_another_users_cart_item(): void
    {
        $owner = User::factory()->create();
        Sanctum::actingAs($owner);
        $product = Product::factory()->create(['stock' => 10]);
        $item = $owner->cart()->firstOrCreate()->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $intruder = User::factory()->create();
        Sanctum::actingAs($intruder);

        $this->patchJson("/api/cart/items/{$item->id}", ['quantity' => 5])->assertStatus(404);
        $this->deleteJson("/api/cart/items/{$item->id}")->assertStatus(404);

        $this->assertDatabaseHas('cart_items', ['id' => $item->id, 'quantity' => 1]);
    }

    public function test_removing_a_cart_item(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $product = Product::factory()->create(['stock' => 10]);
        $item = $user->cart()->firstOrCreate()->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->deleteJson("/api/cart/items/{$item->id}")->assertOk();

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }
}
