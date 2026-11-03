@extends('layouts.shop')

@section('title', 'Keranjang')

@section('content')
@php
    $rp  = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');
@endphp

<h1 class="text-2xl font-semibold text-[#0b3b4a]">Keranjang belanja</h1>

@if ($items->isEmpty())
    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-10 text-center text-slate-500">
        <p>Keranjangmu masih kosong.</p>
        <a href="{{ route('catalog') }}" class="mt-4 inline-block rounded-lg bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white">Lihat katalog</a>
    </div>
@else
    <form method="POST" action="{{ route('cart.update') }}" class="mt-6">
        @csrf @method('PATCH')
        {{-- Tombol bawaan untuk tombol Enter, supaya tidak memicu tombol Hapus --}}
        <button type="submit" class="sr-only" tabindex="-1" aria-hidden="true">Perbarui</button>

        <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white">
            @foreach ($items as $item)
                @php $c = $item['commodity']; @endphp
                <div class="flex flex-wrap items-center gap-3 p-4">
                    <div class="min-w-0 flex-1 basis-40">
                        <p class="font-semibold">{{ $c->name }}</p>
                        <p class="text-xs text-slate-500">
                            {{ $rp($c->selling_price) }} / {{ $c->unit }} · stok tersedia {{ $fmt($c->stock) }} {{ $c->unit }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" name="quantities[{{ $c->id }}]" value="{{ $item['quantity'] }}"
                               min="0" max="{{ $c->stock }}" step="any" inputmode="decimal"
                               aria-label="Jumlah {{ $c->name }}"
                               class="w-24 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <span class="text-sm text-slate-500">{{ $c->unit }}</span>
                    </div>
                    <p class="w-28 text-right font-semibold">{{ $rp($item['subtotal']) }}</p>
                    <button type="submit" name="remove" value="{{ $c->id }}"
                            class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600">Hapus</button>
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex items-center justify-between rounded-xl bg-white p-4 border border-slate-200">
            <span class="text-sm text-slate-500">Estimasi total</span>
            <span class="text-xl font-bold text-teal-700">{{ $rp($total) }}</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">
            Total akhir dikonfirmasi penjual setelah ikan ditimbang, karena beratnya bisa sedikit berbeda.
        </p>

        <div class="mt-5 flex flex-wrap justify-between gap-2">
            <a href="{{ route('catalog') }}" class="rounded-lg border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold">Lanjut belanja</a>
            <div class="flex gap-2">
                <button type="submit" class="rounded-lg border border-teal-200 bg-white px-5 py-2.5 text-sm font-semibold text-teal-700">Perbarui keranjang</button>
                <button type="submit" name="checkout" value="1" class="rounded-lg bg-[#0b3b4a] px-5 py-2.5 text-sm font-semibold text-white">Lanjut ke checkout</button>
            </div>
        </div>
    </form>
@endif
@endsection