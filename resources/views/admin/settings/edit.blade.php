@extends('layouts.admin')

@section('title', 'Pengaturan Toko')

@section('content')
@php $input = 'w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm'; @endphp

<p class="text-xs font-bold uppercase tracking-widest text-teal-700">Pengaturan</p>
<h1 class="mt-1 text-2xl font-semibold text-[#0b3b4a]">Pengaturan Toko</h1>
<p class="text-sm text-slate-500">Info ini ditampilkan ke pembeli di halaman pembayaran.</p>

<form method="POST" action="{{ route('admin.pengaturan.update') }}" enctype="multipart/form-data"
      class="mt-6 max-w-xl space-y-4 rounded-xl border border-slate-200 bg-white p-5 sm:p-8">
    @csrf @method('PUT')

    @if ($errors->any())
        <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div>
        <label for="whatsapp_number" class="mb-1 block text-sm font-semibold">Nomor WhatsApp penjual</label>
        <input id="whatsapp_number" name="whatsapp_number" inputmode="tel" required
               value="{{ old('whatsapp_number', $settings->whatsapp_number) }}" class="{{ $input }}">
        <p class="mt-1 text-xs text-slate-500">Boleh ditulis 0812..., otomatis diubah ke format 62812....</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="bank_name" class="mb-1 block text-sm font-semibold">Nama bank</label>
            <input id="bank_name" name="bank_name" value="{{ old('bank_name', $settings->bank_name) }}" placeholder="Contoh: BCA" class="{{ $input }}">
        </div>
        <div>
            <label for="account_number" class="mb-1 block text-sm font-semibold">Nomor rekening</label>
            <input id="account_number" name="account_number" inputmode="numeric" value="{{ old('account_number', $settings->account_number) }}" class="{{ $input }}">
        </div>
    </div>

    <div>
        <label for="account_holder" class="mb-1 block text-sm font-semibold">Nama pemilik rekening</label>
        <input id="account_holder" name="account_holder" value="{{ old('account_holder', $settings->account_holder) }}" class="{{ $input }}">
    </div>

    <div>
        <label for="qris" class="mb-1 block text-sm font-semibold">Gambar QRIS</label>
        @if ($settings->qris_url)
            <img src="{{ $settings->qris_url }}" alt="QRIS saat ini" class="mb-3 w-40 rounded-lg border border-slate-200">
        @endif
        <input id="qris" name="qris" type="file" accept="image/*"
               class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-teal-800">
        <p class="mt-1 text-xs text-slate-500">Unggah tangkapan layar atau foto QRIS toko (JPG, PNG, WebP, maksimal 4 MB).</p>
    </div>

    <div class="flex justify-end">
        <button class="rounded-lg bg-[#0b3b4a] px-5 py-2.5 text-sm font-semibold text-white">Simpan Pengaturan</button>
    </div>
</form>
@endsection