@extends('layouts.shop')

@section('title', 'Checkout')

@section('content')
@php
    $rp  = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');
    $input = 'w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm';
    $method = old('delivery_method', 'pickup');
@endphp

<a href="{{ route('cart.index') }}" class="text-sm text-teal-700">← Kembali ke keranjang</a>
<h1 class="mt-2 text-2xl font-semibold text-[#0b3b4a]">Checkout</h1>

@if ($errors->any())
    <div class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
        <ul class="list-disc pl-4">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="mt-6 grid gap-6 lg:grid-cols-5">
    <form method="POST" action="{{ route('checkout.store') }}" class="space-y-4 rounded-xl border border-slate-200 bg-white p-5 lg:col-span-3">
        @csrf

        <div>
            <label for="customer_name" class="mb-1 block text-sm font-semibold">Nama</label>
            <input id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required maxlength="100" class="{{ $input }}">
        </div>

        <div>
            <label for="customer_phone" class="mb-1 block text-sm font-semibold">Nomor WhatsApp</label>
            <input id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" required
                   inputmode="tel" placeholder="081234567890" class="{{ $input }}">
        </div>

        <fieldset>
            <legend class="mb-2 text-sm font-semibold">Cara pengambilan</legend>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3">
                    <input type="radio" name="delivery_method" value="pickup" @checked($method === 'pickup')> Ambil di tempat
                </label>
                <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3">
                    <input type="radio" name="delivery_method" value="delivery" @checked($method === 'delivery')> Diantar
                </label>
            </div>
        </fieldset>

        <div id="address-wrap" class="{{ $method === 'delivery' ? '' : 'hidden' }}">
            <label for="address" class="mb-1 block text-sm font-semibold">Alamat pengantaran</label>
            <textarea id="address" name="address" rows="3" maxlength="300" class="{{ $input }}">{{ old('address') }}</textarea>
            <p class="mt-1 text-xs text-slate-500">Ongkos kirim ditentukan penjual lewat WhatsApp setelah melihat alamatmu.</p>
        </div>

        <div>
            <label for="note" class="mb-1 block text-sm font-semibold">Catatan (opsional)</label>
            <input id="note" name="note" value="{{ old('note') }}" maxlength="200"
                   placeholder="Contoh: tolong dibersihkan" class="{{ $input }}">
        </div>

        <button class="w-full rounded-lg bg-[#0b3b4a] py-3 text-sm font-semibold text-white">Buat pesanan</button>
    </form>

    <aside class="h-fit rounded-xl border border-slate-200 bg-white p-5 lg:col-span-2">
        <h2 class="font-semibold text-[#0b3b4a]">Ringkasan pesanan</h2>
        <ul class="mt-3 divide-y divide-slate-100 text-sm">
            @foreach ($items as $item)
                <li class="flex justify-between gap-3 py-2">
                    <span>{{ $item['commodity']->name }} · {{ $fmt($item['quantity']) }} {{ $item['commodity']->unit }}</span>
                    <span class="font-semibold">{{ $rp($item['subtotal']) }}</span>
                </li>
            @endforeach
        </ul>
        <div class="mt-3 flex justify-between border-t border-slate-200 pt-3">
            <span class="text-sm text-slate-500">Estimasi total</span>
            <span class="text-lg font-bold text-teal-700">{{ $rp($total) }}</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">Total akhir dikonfirmasi setelah ikan ditimbang.</p>
    </aside>
</div>

<script>
    const wrap = document.getElementById('address-wrap');
    document.querySelectorAll('input[name="delivery_method"]').forEach((r) =>
        r.addEventListener('change', () => wrap.classList.toggle('hidden', r.value !== 'delivery' || !r.checked)));
</script>
@endsection