@extends('layouts.shop')

@section('title', 'Cek Pesanan')

@section('content')
@php $input = 'w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm'; @endphp

<div class="mx-auto max-w-md">
    <p class="text-xs font-bold uppercase tracking-widest text-teal-700">Status pesanan</p>
    <h1 class="mt-1 text-2xl font-semibold text-[#0b3b4a]">Cek Pesanan</h1>
    <p class="text-sm text-slate-500">Masukkan kode pesanan dan nomor WhatsApp yang kamu pakai saat memesan.</p>

    <form method="POST" action="{{ route('orders.find') }}" class="mt-6 space-y-4 rounded-xl border border-slate-200 bg-white p-5">
        @csrf

        @if ($errors->any())
            <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">{{ $errors->first() }}</p>
        @endif

        <div>
            <label for="order_code" class="mb-1 block text-sm font-semibold">Kode pesanan</label>
            <input id="order_code" name="order_code" value="{{ old('order_code') }}" required maxlength="20"
                   placeholder="BLD-0001" class="{{ $input }} uppercase">
        </div>
        <div>
            <label for="phone" class="mb-1 block text-sm font-semibold">Nomor WhatsApp</label>
            <input id="phone" name="phone" value="{{ old('phone') }}" required inputmode="tel"
                   placeholder="081234567890" class="{{ $input }}">
        </div>

        <button class="w-full rounded-lg bg-[#0b3b4a] py-3 text-sm font-semibold text-white">Cek pesanan</button>
    </form>
</div>
@endsection