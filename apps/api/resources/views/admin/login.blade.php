<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login &middot; ShopCraft</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-white text-black antialiased">
    <div class="w-full max-w-sm rounded-2xl border border-black/10 p-8 shadow-sm">
        <h1 class="mb-6 text-xl font-semibold">ShopCraft Admin</h1>

        @if ($errors->any())
            <p class="mb-4 text-sm text-red-600">{{ $errors->first() }}</p>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" class="flex flex-col gap-4">
            @csrf
            <label class="flex flex-col gap-1 text-sm">
                Email
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="rounded-md border border-black/15 px-3 py-2">
            </label>
            <label class="flex flex-col gap-1 text-sm">
                Password
                <input type="password" name="password" required
                       class="rounded-md border border-black/15 px-3 py-2">
            </label>
            <button type="submit" class="mt-2 rounded-md bg-black px-4 py-2 text-sm font-medium text-white">
                Log in
            </button>
        </form>
    </div>
</body>
</html>
