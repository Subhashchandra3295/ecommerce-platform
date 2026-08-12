<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function show(Request $request)
    {
        $cart = $request->user()->cart()->with('items.product')->firstOrCreate();

        return response()->json($cart);
    }

    public function addItem(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $request->user()->cart()->firstOrCreate();

        $item = $cart->items()->where('product_id', $data['product_id'])->first();
        if ($item) {
            $item->increment('quantity', $data['quantity']);
        } else {
            $item = $cart->items()->create($data);
        }

        return response()->json($cart->load('items.product'), 201);
    }

    public function updateItem(Request $request, int $itemId)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $request->user()->cart()->firstOrCreate();
        $item = $cart->items()->findOrFail($itemId);
        $item->update($data);

        return response()->json($cart->load('items.product'));
    }

    public function removeItem(Request $request, int $itemId)
    {
        $cart = $request->user()->cart()->firstOrCreate();
        $cart->items()->findOrFail($itemId)->delete();

        return response()->json($cart->load('items.product'));
    }
}
