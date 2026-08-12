<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Jobs\ProcessPaidOrder;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcessPaidOrderJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_decrements_stock_and_clears_the_users_cart(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $cart = $user->cart()->firstOrCreate();
        $cart->items()->create(['product_id' => $product->id, 'quantity' => 2]);

        $order = Order::factory()->create(['user_id' => $user->id, 'status' => OrderStatus::Paid]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'unit_price_cents' => $product->price_cents,
            'quantity' => 3,
        ]);

        (new ProcessPaidOrder($order->id))->handle();

        $this->assertSame(7, $product->fresh()->stock);
        $this->assertSame(0, $cart->items()->count());
    }
}
