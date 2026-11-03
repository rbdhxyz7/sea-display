<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') – Sea Display</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
<nav class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex h-14 max-w-5xl items-center justify-between px-4">
        <a href="{{ route('catalog') }}" class="text-lg font-bold">≈ Sea Display</a>
        <div class="flex items-center gap-4 text-sm font-medium">
            <a href="{{ route('orders.lookup') }}">Cek Pesanan</a>
            <a href="{{ route('cart.index') }}">Keranjang ({{ count(session('cart', [])) }})</a>
        </div>
    </div>
</nav>

<main class="mx-auto max-w-5xl px-4 py-8">
    @if (session('status'))
        <p class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</p>
    @endif
    @if (session('error'))
        <p class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ session('error') }}</p>
    @endif

    @yield('content')
</main>
</body>
</html>