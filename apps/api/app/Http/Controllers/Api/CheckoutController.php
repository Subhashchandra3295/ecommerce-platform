<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function store(Request $request)
    {
        $cart = $request->user()->cart()->with('items.product')->firstOrCreate();

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Your cart is empty'], 422);
        }

        foreach ($cart->items as $item) {
            if ($item->quantity > $item->product->stock) {
                return response()->json([
                    'message' => "Not enough stock for \"{$item->product->name}\"",
                ], 422);
            }
        }

        $order = DB::transaction(function () use ($cart) {
            $order = Order::create([
                'user_id' => $cart->user_id,
                'total_cents' => $cart->items->sum(fn ($item) => $item->quantity * $item->product->price_cents),
            ]);

            foreach ($cart->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'unit_price_cents' => $item->product->price_cents,
                    'quantity' => $item->quantity,
                ]);
            }

            return $order;
        });

        Stripe::setApiKey(config('services.stripe.secret'));

        $frontendUrl = rtrim(config('app.frontend_url'), '/');

        $session = Session::create([
            'mode' => 'payment',
            'line_items' => $order->items->map(fn ($item) => [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => $item->product_name],
                    'unit_amount' => $item->unit_price_cents,
                ],
                'quantity' => $item->quantity,
            ])->all(),
            'success_url' => "{$frontendUrl}/orders/{$order->id}?success=true",
            'cancel_url' => "{$frontendUrl}/cart?canceled=true",
            'metadata' => ['order_id' => $order->id],
        ]);

        $order->update(['stripe_checkout_session_id' => $session->id]);

        return response()->json(['checkout_url' => $session->url]);
    }
}
