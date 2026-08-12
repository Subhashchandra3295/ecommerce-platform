@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Dashboard</h1>

    <div class="mb-8 grid grid-cols-3 gap-4">
        <div class="rounded-xl border border-black/10 p-5">
            <p class="text-sm text-black/60">Products</p>
            <p class="mt-1 text-3xl font-semibold">{{ $productCount }}</p>
        </div>
        <div class="rounded-xl border border-black/10 p-5">
            <p class="text-sm text-black/60">Orders</p>
            <p class="mt-1 text-3xl font-semibold">{{ $orderCount }}</p>
        </div>
        <div class="rounded-xl border border-black/10 p-5">
            <p class="text-sm text-black/60">Revenue (paid orders)</p>
            <p class="mt-1 text-3xl font-semibold">${{ number_format($paidTotalCents / 100, 2) }}</p>
        </div>
    </div>

    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-black/60">Recent orders</h2>
    <div class="overflow-hidden rounded-xl border border-black/10">
        <table class="w-full text-sm">
            <thead class="bg-black/5 text-left">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Customer</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentOrders as $order)
                    <tr class="border-t border-black/10">
                        <td class="px-4 py-2"><a href="{{ route('admin.orders.show', $order) }}" class="underline">#{{ $order->id }}</a></td>
                        <td class="px-4 py-2">{{ $order->user->name }}</td>
                        <td class="px-4 py-2">{{ $order->status->value }}</td>
                        <td class="px-4 py-2">${{ $order->formattedTotal() }}</td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-3 text-black/60" colspan="4">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
