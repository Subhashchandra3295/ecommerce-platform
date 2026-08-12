@extends('admin.layout')

@section('title', 'Orders')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">Orders</h1>

    <div class="overflow-hidden rounded-xl border border-black/10">
        <table class="w-full text-sm">
            <thead class="bg-black/5 text-left">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-4 py-2">Customer</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Total</th>
                    <th class="px-4 py-2">Placed</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr class="border-t border-black/10">
                        <td class="px-4 py-2"><a href="{{ route('admin.orders.show', $order) }}" class="underline">#{{ $order->id }}</a></td>
                        <td class="px-4 py-2">{{ $order->user->name }}</td>
                        <td class="px-4 py-2">
                            <span class="rounded-full bg-black/5 px-2 py-0.5 text-xs">{{ $order->status->value }}</span>
                        </td>
                        <td class="px-4 py-2">${{ $order->formattedTotal() }}</td>
                        <td class="px-4 py-2">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-3 text-black/60" colspan="5">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
