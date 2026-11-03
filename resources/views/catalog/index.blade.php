<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sea Display – UD Bintang Laut Deasy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-slate-800 antialiased">

<nav class="border-b border-slate-200">
    <div class="mx-auto flex h-14 max-w-6xl items-center justify-between px-4">
        <span class="text-lg font-bold">≈ Sea Display</span>
        <div class="flex items-center gap-4 text-sm font-medium">
            <a href="{{ route('orders.lookup') }}">Cek Pesanan</a>
            <a href="{{ route('cart.index') }}">Keranjang ({{ count(session('cart', [])) }})</a>
            <a href="{{ route('login') }}">Login Admin</a>
        </div>
    </div>
</nav>

<header class="bg-[#0b3b4a] py-14 text-white">
    <div class="mx-auto max-w-6xl px-4">
        <p class="text-xs font-bold tracking-widest text-cyan-100">SEGAR SETIAP HARI DARI LAUT</p>
        <h1 class="mt-3 text-4xl font-semibold leading-tight sm:text-5xl">
            Hasil Laut Segar,<br><span class="text-cyan-300">Harga Terbaik</span>
        </h1>
        <p class="mt-4 max-w-xl text-sm text-cyan-50/80">
            Pilihan komoditas laut berkualitas untuk rumah makan, usaha, dan keluarga Anda.
            Pesan mudah langsung dari Bu Desy di Freshmarket GLC.
        </p>
        <p class="mt-5 flex gap-5 text-xs text-cyan-100">
            <span>✓ Kualitas terjamin</span><span>✓ Harga transparan</span>
        </p>
    </div>
</header>

<main class="mx-auto max-w-6xl px-4 py-10">
    @if (session('status'))
        <p class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
            {{ session('status') }} <a href="{{ route('cart.index') }}" class="font-semibold underline">Lihat keranjang</a>
        </p>
    @endif
    @if (session('error'))
        <p class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ session('error') }}</p>
    @endif
    <p class="text-xs font-bold tracking-widest text-teal-700">KATALOG HARI INI</p>
    <div class="mt-1 flex items-end justify-between">
        <h2 class="text-2xl">Pilih hasil laut favorit Anda</h2>
        <span class="text-xs text-slate-500">{{ $commodities->count() }} komoditas ditemukan</span>
    </div>

    <form method="GET" action="{{ route('catalog') }}" class="mt-4">
        @if (request('kategori'))
            <input type="hidden" name="kategori" value="{{ request('kategori') }}">
        @endif
        <input type="search" name="q" value="{{ request('q') }}"
               placeholder="Cari ikan, udang, kerang..."
               class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
    </form>

    <div class="mt-4 flex flex-wrap gap-2">
        <a href="{{ route('catalog', ['q' => request('q')]) }}"
           class="rounded-full border px-4 py-1.5 text-sm {{ request('kategori') ? 'border-slate-200' : 'border-teal-700 bg-teal-700 text-white' }}">
            Semua
        </a>
        @foreach ($categories as $cat)
            <a href="{{ route('catalog', ['kategori' => $cat->slug, 'q' => request('q')]) }}"
               class="rounded-full border px-4 py-1.5 text-sm {{ request('kategori') === $cat->slug ? 'border-teal-700 bg-teal-700 text-white' : 'border-slate-200' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    @if ($commodities->isEmpty())
        <p class="mt-8 rounded-xl bg-slate-50 p-10 text-center text-slate-500">
            Tidak ada komoditas yang cocok. Coba kata kunci atau kategori lain.
        </p>
    @else
        <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ($commodities as $c)
                @php
                    $emoji = ['ikan' => '🐟', 'udang' => '🦐', 'cumi' => '🦑', 'kerang' => '🐚'][$c->category->slug] ?? '🐟';
                    $badge = match ($c->stock_status) {
                        'Tersedia'     => 'bg-green-100 text-green-800',
                        'Stok Menipis' => 'bg-orange-100 text-orange-800',
                        default        => 'bg-red-100 text-red-800',
                    };
                    $habis = $c->stock_status === 'Habis';
                    $wa = 'https://wa.me/' . config('shop.whatsapp') . '?text='
                        . urlencode('Halo Bu Desy, saya mau pesan ' . $c->name . '.');
                @endphp

                <article class="flex flex-col overflow-hidden rounded-xl border border-slate-200">
                    <div class="relative flex aspect-[4/3] items-center justify-center overflow-hidden bg-cyan-100 text-5xl">
                        @if ($c->photo_url)
                            <img src="{{ $c->photo_url }}" alt="{{ $c->name }}" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                        @else
                            {{ $emoji }}
                        @endif
                        <span class="absolute left-2 top-2 rounded-md bg-white px-2 py-0.5 text-[10px] font-semibold">
                            {{ $c->category->name }}
                        </span>
                    </div>
                    <div class="flex flex-1 flex-col gap-1.5 p-3">
                        <h3 class="text-sm font-medium">{{ $c->name }}</h3>
                        <span class="w-fit rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $badge }}">
                            {{ $c->stock_status }}
                        </span>
                        <p class="text-lg font-bold text-teal-700">
                            Rp {{ number_format($c->selling_price, 0, ',', '.') }}
                            <span class="text-xs font-normal text-slate-500">/ {{ $c->unit }}</span>
                        </p>
                         @if ($habis)
                            <span class="mt-auto rounded-lg bg-slate-300 py-2.5 text-center text-xs font-semibold text-white">Stok habis</span>
                        @else
                            <form method="POST" action="{{ route('cart.add') }}" class="mt-auto">
                                @csrf
                                <input type="hidden" name="commodity_id" value="{{ $c->id }}">
                                <button class="w-full rounded-lg bg-green-600 py-2.5 text-xs font-semibold text-white">Tambah ke keranjang</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</main>

<footer class="bg-[#0b3b4a] py-6 text-center text-sm text-cyan-100">
    UD Bintang Laut Desy · Freshmarket GLC
</footer>

</body>
</html>