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
    $isOwner = $user->role === 'owner';
    $initials = collect(explode(' ', $user->name))
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->take(2)->implode('');
    $roleLabel = $isOwner ? 'Pemilik' : 'Staf';
    $pendingCount = \App\Models\Order::where('status', 'pending_payment')->count();

    // [label, alamat, pola route aktif, khusus pemilik?, angka lencana]
    $items = [
        ['Dashboard',        route('admin.dashboard'),               'admin.dashboard',     false, 0],
        ['Pesanan',          route('admin.pesanan.index'),           'admin.pesanan.*',     false, $pendingCount],
        ['Kelola Komoditas', route('admin.dashboard') . '#daftar',   'admin.komoditas.*',   false, 0],
        ['Kelola Kategori',  route('admin.kategori.index'),          'admin.kategori.*',    false, 0],
        ['Catat Stok',       route('admin.stok.index'),              'admin.stok.*',        false, 0],
        ['Laporan',          route('admin.laporan.index'),           'admin.laporan.*',     true,  0],
        ['Pengaturan Toko',  route('admin.pengaturan.edit'),         'admin.pengaturan.*',  true,  0],
        ['Kelola Pengguna',  route('admin.pengguna.index'),          'admin.pengguna.*',    true,  0],
        ['Akun Saya',        route('admin.akun.edit'),               'admin.akun.*',        false, 0],
    ];
    $items = array_values(array_filter($items, fn ($i) => $isOwner || ! $i[3]));
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

        <nav class="flex-1 space-y-1 overflow-y-auto text-sm">
            @foreach ($items as [$label, $url, $pattern, $ownerOnly, $badge])
                <a href="{{ $url }}"
                   class="flex items-center justify-between rounded-lg px-3 py-2 {{ request()->routeIs($pattern) ? 'bg-white/10 font-semibold' : 'text-cyan-100/80' }}">
                    <span>{{ $label }}</span>
                    @if ($badge)
                        <span class="rounded-full bg-orange-400 px-2 py-0.5 text-[11px] font-bold text-[#0b3b4a]">{{ $badge }}</span>
                    @endif
                </a>
            @endforeach
            <a href="{{ route('catalog') }}" target="_blank" class="block rounded-lg px-3 py-2 text-cyan-100/80">Lihat Katalog ↗</a>
        </nav>

        <form method="POST" action="{{ route('logout') }}" class="mt-3">
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
                @foreach ($items as [$label, $url, $pattern, $ownerOnly, $badge])
                    <a href="{{ $url }}"
                       class="shrink-0 rounded-lg px-3 py-1.5 {{ request()->routeIs($pattern) ? 'bg-white/15 font-semibold' : 'border border-white/20' }}">
                        {{ $label }}{{ $badge ? " ({$badge})" : '' }}
                    </a>
                @endforeach
                <a href="{{ route('catalog') }}" class="shrink-0 rounded-lg border border-white/20 px-3 py-1.5">Katalog</a>
            </nav>
        </header>

        <main class="p-4 sm:p-8">
            @if (session('status'))
                <p class="no-print mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800" role="status">{{ session('status') }}</p>
            @endif
            @if (session('error'))
                <p class="no-print mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ session('error') }}</p>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>