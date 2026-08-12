@extends('admin.layout')

@section('title', 'Products')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-semibold">Products</h1>
        <a href="{{ route('admin.products.create') }}" class="rounded-md bg-black px-4 py-2 text-sm font-medium text-white">
            New product
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-black/10">
        <table class="w-full text-sm">
            <thead class="bg-black/5 text-left">
                <tr>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Category</th>
                    <th class="px-4 py-2">Price</th>
                    <th class="px-4 py-2">Stock</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-t border-black/10">
                        <td class="px-4 py-2">{{ $product->name }}</td>
                        <td class="px-4 py-2">{{ $product->category->name }}</td>
                        <td class="px-4 py-2">${{ number_format($product->price_cents / 100, 2) }}</td>
                        <td class="px-4 py-2">{{ $product->stock }}</td>
                        <td class="px-4 py-2 text-right">
                            <a href="{{ route('admin.products.edit', $product) }}" class="underline">Edit</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" onsubmit="return confirm('Delete this product?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="ml-3 text-red-600 underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-3 text-black/60" colspan="5">No products yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
@endsection
