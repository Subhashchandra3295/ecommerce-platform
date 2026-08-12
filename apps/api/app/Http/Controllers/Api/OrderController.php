<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->orders()->with('items')->latest()->get()
        );
    }

    public function show(Request $request, int $id)
    {
        $order = $request->user()->orders()->with('items')->findOrFail($id);

        return response()->json($order);
    }
}
