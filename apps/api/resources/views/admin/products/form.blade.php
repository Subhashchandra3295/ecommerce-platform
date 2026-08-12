@extends('admin.layout')

@section('title', $product->exists ? 'Edit Product' : 'New Product')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold">{{ $product->exists ? 'Edit Product' : 'New Product' }}</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
          class="flex max-w-xl flex-col gap-4">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <label class="flex flex-col gap-1 text-sm">
            Category
            <select name="category_id" class="rounded-md border border-black/15 px-3 py-2">
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label class="flex flex-col gap-1 text-sm">
            Name
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                   class="rounded-md border border-black/15 px-3 py-2">
        </label>

        <label class="flex flex-col gap-1 text-sm">
            Description
            <textarea name="description" rows="3"
                      class="rounded-md border border-black/15 px-3 py-2">{{ old('description', $product->description) }}</textarea>
        </label>

        <div class="grid grid-cols-2 gap-4">
            <label class="flex flex-col gap-1 text-sm">
                Price (USD)
                <input type="number" step="0.01" min="0" name="price"
                       value="{{ old('price', $product->exists ? number_format($product->price_cents / 100, 2, '.', '') : '') }}"
                       required class="rounded-md border border-black/15 px-3 py-2">
            </label>
            <label class="flex flex-col gap-1 text-sm">
                Stock
                <input type="number" min="0" name="stock" value="{{ old('stock', $product->stock) }}" required
                       class="rounded-md border border-black/15 px-3 py-2">
            </label>
        </div>

        <label class="flex flex-col gap-1 text-sm">
            Image path (optional, e.g. /images/mug.jpg)
            <input type="text" name="image_path" value="{{ old('image_path', $product->image_path) }}"
                   class="rounded-md border border-black/15 px-3 py-2">
        </label>

        <button type="submit" class="mt-2 w-fit rounded-md bg-black px-4 py-2 text-sm font-medium text-white">
            {{ $product->exists ? 'Save changes' : 'Create product' }}
        </button>
    </form>
@endsection
