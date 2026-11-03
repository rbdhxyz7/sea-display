@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
@php
    $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');
    $rp  = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $badge = fn ($s) => match ($s) {
        'Habis'        => 'bg-red-100 text-red-700',
        'Stok Menipis' => 'bg-orange-100 text-orange-700',
        default        => '',
    };
@endphp

<div class="flex flex-wrap items-start justify-between gap-4">
    <div>
        <p class="text-xs font-bold uppercase tracking-widest text-teal-700">{{ $today }}</p>
        <h1 class="mt-1 text-2xl font-semibold text-[#0b3b4a]">Selamat datang, {{ auth()->user()->name }}</h1>
        <p class="text-sm text-slate-500">Berikut ringkasan usaha laut Anda hari ini.</p>
    </div>
    <a href="{{ route('admin.komoditas.create') }}" class="rounded-lg bg-[#0b3b4a] px-4 py-2.5 text-sm font-semibold text-white">+ Tambah Komoditas</a>
</div>

<div class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Total Komoditas</p>
        <p class="mt-1 text-3xl font-semibold">{{ $stats['total'] }}</p>
        <p class="text-[11px] text-slate-400">Produk aktif</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Total Stok</p>
        <p class="mt-1 text-3xl font-semibold">{{ $fmt($stats['stock']) }} <span class="text-base font-normal">kg</span></p>
        <p class="text-[11px] text-slate-400">Semua kategori</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Stok Menipis</p>
        <p class="mt-1 text-3xl font-semibold {{ $stats['low'] > 0 ? 'text-red-600' : '' }}">{{ $stats['low'] }}</p>
        <p class="text-[11px] text-slate-400">Perlu diperbarui</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Rata-rata Margin</p>
        <p class="mt-1 text-3xl font-semibold">{{ $stats['margin'] }}%</p>
        <p class="text-[11px] text-slate-400">Terhadap harga modal</p>
    </div>
</div>

<section id="daftar" class="mt-6 rounded-xl border border-slate-200 bg-white">
    <div class="flex flex-wrap items-end justify-between gap-3 p-4 sm:p-6">
        <div>
            <h2 class="text-lg font-medium text-[#0b3b4a]">Daftar Komoditas</h2>
            <p class="text-xs text-slate-500">Kelola harga dan stok produk Anda.</p>
        </div>
        <form method="GET" action="{{ route('admin.dashboard') }}#daftar" class="flex w-full gap-2 sm:w-auto">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari komoditas..."
                   class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm sm:w-56">
            <select name="kategori" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                <option value="">Semua</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->slug }}" @selected(request('kategori') === $cat->slug)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if ($commodities->isEmpty())
        <p class="border-t border-slate-100 p-10 text-center text-sm text-slate-500">
            Tidak ada komoditas yang cocok dengan pencarianmu.
        </p>
    @else
        <div class="hidden overflow-x-auto md:block">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Foto</th>
                        <th class="px-3 py-3">Nama</th>
                        <th class="px-3 py-3">Kategori</th>
                        <th class="px-3 py-3">Harga modal</th>
                        <th class="px-3 py-3">Harga jual</th>
                        <th class="px-3 py-3">Margin</th>
                        <th class="px-3 py-3">Stok</th>
                        <th class="px-3 py-3">Satuan</th>
                        <th class="px-3 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($commodities as $c)
                        <tr>
                            <td class="px-6 py-3">@include('admin.commodities._thumb', ['c' => $c, 'size' => 'h-10 w-10'])</td>
                            <td class="px-3 py-3 font-semibold">{{ $c->name }}</td>
                            <td class="px-3 py-3">
                                <span class="rounded-full bg-teal-50 px-2 py-0.5 text-xs text-teal-800">{{ $c->category->name }}</span>
                            </td>
                            <td class="px-3 py-3 text-slate-500">{{ $rp($c->cost_price) }}</td>
                            <td class="px-3 py-3 font-semibold">{{ $rp($c->selling_price) }}</td>
                            <td class="px-3 py-3 {{ $c->margin >= 0 ? 'text-green-700' : 'text-red-600' }}">
                                <span class="font-semibold">{{ $c->margin >= 0 ? '+' : '' }}{{ $rp($c->margin) }}</span><br>
                                <span class="text-xs">{{ str_replace('.', ',', $c->margin_percent) }}%</span>
                            </td>
                            <td class="px-3 py-3">
                                @if ($badge($c->stock_status))
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $badge($c->stock_status) }}">{{ $fmt($c->stock) }}</span>
                                @else
                                    {{ $fmt($c->stock) }}
                                @endif
                            </td>
                            <td class="px-3 py-3 text-slate-500">{{ $c->unit }}</td>
                            <td class="px-3 py-3">
                                <div class="flex gap-1.5">
                                    <a href="{{ route('admin.komoditas.edit', $c) }}" class="rounded-lg border border-teal-200 px-3 py-1.5 text-xs font-semibold text-teal-700">Edit</a>
                                    <form method="POST" action="{{ route('admin.komoditas.destroy', $c) }}"
                                          onsubmit="return confirm(@js('Hapus ' . $c->name . ' dari katalog?'))">
                                        @csrf @method('DELETE')
                                        <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="divide-y divide-slate-100 border-t border-slate-100 md:hidden">
            @foreach ($commodities as $c)
                <div class="flex gap-3 p-4">
                    @include('admin.commodities._thumb', ['c' => $c, 'size' => 'h-12 w-12'])
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="font-semibold">{{ $c->name }}</p>
                            <span class="shrink-0 rounded-full bg-teal-50 px-2 py-0.5 text-xs text-teal-800">{{ $c->category->name }}</span>
                        </div>
                        <p class="mt-1 text-sm">
                            <span class="font-semibold">{{ $rp($c->selling_price) }}</span>
                            <span class="text-xs text-slate-400">modal {{ $rp($c->cost_price) }}</span>
                        </p>
                        <p class="text-xs {{ $c->margin >= 0 ? 'text-green-700' : 'text-red-600' }}">
                            Margin {{ $c->margin >= 0 ? '+' : '' }}{{ $rp($c->margin) }} ({{ str_replace('.', ',', $c->margin_percent) }}%)
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Stok:
                            @if ($badge($c->stock_status))
                                <span class="rounded-full px-2 py-0.5 font-semibold {{ $badge($c->stock_status) }}">{{ $fmt($c->stock) }} {{ $c->unit }}</span>
                            @else
                                {{ $fmt($c->stock) }} {{ $c->unit }}
                            @endif
                        </p>
                        <div class="mt-2 flex gap-2">
                            <a href="{{ route('admin.komoditas.edit', $c) }}" class="rounded-lg border border-teal-200 px-4 py-1.5 text-xs font-semibold text-teal-700">Edit</a>
                            <form method="POST" action="{{ route('admin.komoditas.destroy', $c) }}"
                                  onsubmit="return confirm(@js('Hapus ' . $c->name . ' dari katalog?'))">
                                @csrf @method('DELETE')
                                <button class="rounded-lg border border-red-200 px-4 py-1.5 text-xs font-semibold text-red-600">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</section>
@endsection