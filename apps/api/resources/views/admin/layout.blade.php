<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') &middot; ShopCraft Admin</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-white text-black antialiased">
    <div class="flex min-h-screen">
        <aside class="w-56 shrink-0 border-r border-black/10 p-6">
            <p class="mb-8 font-semibold">ShopCraft Admin</p>
            <nav class="flex flex-col gap-2 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2 hover:bg-black/5 {{ request()->routeIs('admin.dashboard') ? 'bg-black/5 font-medium' : '' }}">Dashboard</a>
                <a href="{{ route('admin.products.index') }}" class="rounded-md px-3 py-2 hover:bg-black/5 {{ request()->routeIs('admin.products.*') ? 'bg-black/5 font-medium' : '' }}">Products</a>
                <a href="{{ route('admin.orders.index') }}" class="rounded-md px-3 py-2 hover:bg-black/5 {{ request()->routeIs('admin.orders.*') ? 'bg-black/5 font-medium' : '' }}">Orders</a>
            </nav>
            <form method="POST" action="{{ route('admin.logout') }}" class="mt-8">
                @csrf
                <button type="submit" class="w-full rounded-md border border-black/10 px-3 py-2 text-left text-sm hover:bg-black/5">Log out</button>
            </form>
        </aside>
        <main class="flex-1 p-8">
            @if (session('status'))
                <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
