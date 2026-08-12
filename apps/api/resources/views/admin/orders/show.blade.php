@extends('admin.layout')

@section('title', "Order #{$order->id}")

@section('content')
    <a href="{{ route('admin.orders.index') }}" class="text-sm underline">&larr; Orders</a>

    <h1 class="mb-6 mt-3 text-2xl font-semibold">Order #{{ $order->id }}</h1>

    <div class="mb-8 grid grid-cols-2 gap-6">
        <div class="rounded-xl border border-black/10 p-5">
            <p class="text-sm text-black/60">Customer</p>
            <p class="mt-1">{{ $order->user->name }} &middot; {{ $order->user->email }}</p>
            <p class="mt-4 text-sm text-black/60">Placed</p>
            <p class="mt-1">{{ $order->created_at->format('Y-m-d H:i') }}</p>
        </div>
        <div class="rounded-xl border border-black/10 p-5">
            <p class="mb-2 text-sm text-black/60">Status</p>
            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="flex gap-2">
                @csrf
                @method('PUT')
                <select name="status" class="rounded-md border border-black/15 px-3 py-2 text-sm">
                    @foreach (['pending', 'paid', 'fulfilled', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected($order->status->value === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-md bg-black px-4 py-2 text-sm font-medium text-white">Update</button>
            </form>
        </div>
    </div>

    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-black/60">Items</h2>
    <div class="overflow-hidden rounded-xl border border-black/10">
        <table class="w-full text-sm">
            <thead class="bg-black/5 text-left">
                <tr>
                    <th class="px-4 py-2">Product</th>
                    <th class="px-4 py-2">Unit price</th>
                    <th class="px-4 py-2">Qty</th>
                    <th class="px-4 py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr class="border-t border-black/10">
                        <td class="px-4 py-2">{{ $item->product_name }}</td>
                        <td class="px-4 py-2">${{ number_format($item->unit_price_cents / 100, 2) }}</td>
                        <td class="px-4 py-2">{{ $item->quantity }}</td>
                        <td class="px-4 py-2">${{ number_format($item->unit_price_cents * $item->quantity / 100, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t border-black/10 font-medium">
                    <td class="px-4 py-2" colspan="3">Total</td>
                    <td class="px-4 py-2">${{ $order->formattedTotal() }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
@endsection
