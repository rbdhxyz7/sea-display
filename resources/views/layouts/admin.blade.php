<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') – Sea Display</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
    $roleLabel = $user->role === 'owner' ? 'Pemilik' : 'Staf';

    $pendingCount = \App\Models\Order::where('status', 'pending_payment')->count();

    // [nama route, pola route aktif, label]
    $links = [
        ['admin.pengaturan.edit', 'admin.pengaturan.*', 'Pengaturan'],
        ['admin.dashboard',      'admin.dashboard',  'Dashboard'],
        ['admin.pesanan.index',  'admin.pesanan.*',  'Pesanan' . ($pendingCount ? " ({$pendingCount})" : '')],
        ['admin.kategori.index', 'admin.kategori.*', 'Kategori'],
        ['admin.stok.index',     'admin.stok.*',     'Catat Stok'],
        ['admin.laporan.index',  'admin.laporan.*',  'Laporan'],
    ];
@endphp

<div class="min-h-screen lg:flex">

    {{-- Sidebar (layar lebar) --}}
    <aside class="no-print hidden w-60 shrink-0 flex-col bg-[#0b3b4a] p-4 text-white lg:flex">
        <p class="px-2 py-3 text-lg font-bold">≈ Sea Display</p>

        <div class="my-4 flex items-center gap-3 border-y border-white/10 px-2 py-4">
            <span class="grid h-9 w-9 place-items-center rounded-full bg-cyan-200 text-xs font-bold text-[#0b3b4a]">{{ $initials }}</span>
            <div>
                <p class="text-sm font-semibold">{{ $user->name }}</p>
                <p class="text-xs text-cyan-100/70">{{ $roleLabel }}</p>
            </div>
        </div>

        <nav class="flex-1 space-y-1 text-sm">
            <a href="{{ route('admin.dashboard') }}"
               class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 font-semibold' : 'text-cyan-100/80' }}">
                Dashboard
            </a>
            <a href="{{ route('admin.pesanan.index') }}"
               class="flex items-center justify-between rounded-lg px-3 py-2 {{ request()->routeIs('admin.pesanan.*') ? 'bg-white/10 font-semibold' : 'text-cyan-100/80' }}">
                <span>Pesanan</span>
                @if ($pendingCount)
                    <span class="rounded-full bg-orange-400 px-2 py-0.5 text-[11px] font-bold text-[#0b3b4a]">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.dashboard') }}#daftar" class="block rounded-lg px-3 py-2 text-cyan-100/80">
                Kelola Komoditas
            </a>
            <a href="{{ route('admin.kategori.index') }}"
               class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.kategori.*') ? 'bg-white/10 font-semibold' : 'text-cyan-100/80' }}">
                Kelola Kategori
            </a>
            <a href="{{ route('admin.stok.index') }}"
               class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.stok.*') ? 'bg-white/10 font-semibold' : 'text-cyan-100/80' }}">
                Catat Stok
            </a>
            <a href="{{ route('admin.laporan.index') }}"
               class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.laporan.*') ? 'bg-white/10 font-semibold' : 'text-cyan-100/80' }}">
                Laporan Penjualan
            </a>
            <a href="{{ route('admin.pengaturan.edit') }}"
               class="block rounded-lg px-3 py-2 {{ request()->routeIs('admin.pengaturan.*') ? 'bg-white/10 font-semibold' : 'text-cyan-100/80' }}">
                Pengaturan Toko
            </a>
            <a href="{{ route('catalog') }}" target="_blank" class="block rounded-lg px-3 py-2 text-cyan-100/80">
                Lihat Katalog ↗
            </a>
        </nav>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="w-full rounded-lg border border-white/20 px-3 py-2 text-left text-sm">Keluar</button>
        </form>
    </aside>

    <div class="min-w-0 flex-1">

        {{-- Bar atas (ponsel) --}}
        <header class="no-print bg-[#0b3b4a] px-4 pt-3 text-white lg:hidden">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-bold">≈ Sea Display</p>
                    <p class="text-xs text-cyan-100/70">{{ $user->name }} · {{ $roleLabel }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-lg border border-white/20 px-3 py-1.5 text-xs">Keluar</button>
                </form>
            </div>
            <nav class="-mx-4 mt-3 flex gap-1.5 overflow-x-auto px-4 pb-3 text-xs">
                @foreach ($links as [$name, $pattern, $label])
                    <a href="{{ route($name) }}"
                       class="shrink-0 rounded-lg px-3 py-1.5 {{ request()->routeIs($pattern) ? 'bg-white/15 font-semibold' : 'border border-white/20' }}">
                        {{ $label }}
                    </a>
                @endforeach
                <a href="{{ route('catalog') }}" class="shrink-0 rounded-lg border border-white/20 px-3 py-1.5">Katalog</a>
            </nav>
        </header>

        <main class="p-4 sm:p-8">
            @if (session('status'))
                <p class="no-print mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</p>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>