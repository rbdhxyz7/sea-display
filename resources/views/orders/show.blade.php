@extends('layouts.shop')

@section('title', 'Pesanan ' . $order->order_code)

@section('content')
@php
    $rp  = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');

    $badge = match ($order->status) {
        'pending_payment' => 'bg-orange-100 text-orange-800',
        'paid', 'processing' => 'bg-blue-100 text-blue-800',
        'done' => 'bg-green-100 text-green-800',
        default => 'bg-red-100 text-red-800',
    };

    $lines = ['Halo Bu Desy, saya sudah memesan lewat Sea Display.', "Kode pesanan: {$order->order_code}", "Nama: {$order->customer_name}", ''];
    foreach ($order->items as $i) {
        $lines[] = "- {$i->commodity_name} {$fmt($i->quantity)} {$i->unit}";
    }
    $lines[] = '';
    $lines[] = 'Estimasi total: ' . $rp($order->estimated_total);
    $waUrl = 'https://wa.me/' . $settings->whatsapp_number . '?text=' . rawurlencode(implode("\n", $lines));
@endphp

<div class="mx-auto max-w-2xl">
    <p class="text-xs font-bold uppercase tracking-widest text-teal-700">Pesanan</p>
    <div class="mt-1 flex flex-wrap items-center gap-3">
        <h1 class="text-2xl font-semibold text-[#0b3b4a]">{{ $order->order_code }}</h1>
        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">{{ $order->status_label }}</span>
    </div>
    <p class="mt-2 rounded-lg bg-slate-100 px-3 py-2 text-xs text-slate-600">
        Simpan halaman ini (tambahkan ke penanda) atau catat kode pesananmu. Kamu akan membutuhkannya untuk mengecek status.
    </p>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="font-semibold text-[#0b3b4a]">Isi pesanan</h2>
        <ul class="mt-3 divide-y divide-slate-100 text-sm">
            @foreach ($order->items as $i)
                <li class="flex justify-between gap-3 py-2">
                    <span>{{ $i->commodity_name }} · {{ $fmt($i->quantity) }} {{ $i->unit }}</span>
                    <span class="font-semibold">{{ $rp($i->quantity * $i->unit_price) }}</span>
                </li>
            @endforeach
        </ul>
        <div class="mt-3 flex justify-between border-t border-slate-200 pt-3">
            <span class="text-sm text-slate-500">Estimasi total</span>
            <span class="text-lg font-bold text-teal-700">{{ $rp($order->estimated_total) }}</span>
        </div>
        @if ($order->final_total !== null)
            <div class="mt-1 flex justify-between">
                <span class="text-sm text-slate-500">Total akhir</span>
                <span class="text-lg font-bold">{{ $rp($order->final_total) }}</span>
            </div>
        @else
            <p class="mt-2 text-xs text-slate-500">Total akhir dikonfirmasi penjual setelah ikan ditimbang.</p>
        @endif

        <dl class="mt-4 grid gap-2 border-t border-slate-100 pt-4 text-sm sm:grid-cols-2">
            <div><dt class="text-xs text-slate-500">Nama</dt><dd>{{ $order->customer_name }}</dd></div>
            <div><dt class="text-xs text-slate-500">WhatsApp</dt><dd>{{ $order->customer_phone }}</dd></div>
            <div><dt class="text-xs text-slate-500">Pengambilan</dt>
                <dd>{{ $order->delivery_method === 'delivery' ? 'Diantar' : 'Ambil di tempat' }}</dd></div>
            @if ($order->address)
                <div><dt class="text-xs text-slate-500">Alamat</dt><dd>{{ $order->address }}</dd></div>
            @endif
            @if ($order->note)
                <div class="sm:col-span-2"><dt class="text-xs text-slate-500">Catatan</dt><dd>{{ $order->note }}</dd></div>
            @endif
        </dl>
    </section>

    @if ($order->status === 'pending_payment')
        <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-semibold text-[#0b3b4a]">Pembayaran</h2>
            <p class="mt-1 text-sm text-slate-600">
                   Bayar sebesar total akhir jika sudah tertera, atau estimasi total jika belum. Lalu unggah bukti pembayarannya. Selisih akibat penimbangan diselesaikan lewat WhatsApp.
            </p>

            @if ($settings->qris_url)
                <div class="mt-4 text-center">
                    <img src="{{ $settings->qris_url }}" alt="Kode QRIS pembayaran" class="mx-auto w-56 max-w-full rounded-lg border border-slate-200">
                    <p class="mt-2 text-xs text-slate-500">Ketik nominal pembayaran sendiri saat memindai QRIS.</p>
                </div>
            @endif

            @if ($settings->has_bank)
                <div class="mt-4 rounded-lg bg-slate-50 p-4 text-sm">
                    <p class="text-xs text-slate-500">Transfer bank</p>
                    <p class="font-semibold">{{ $settings->bank_name }}</p>
                    <p class="mt-1 flex items-center gap-2">
                        <span id="acc" class="font-mono text-base font-semibold">{{ $settings->account_number }}</span>
                        <button type="button" id="copy-acc" class="rounded-md border border-slate-200 px-2 py-0.5 text-xs">Salin</button>
                    </p>
                    @if ($settings->account_holder)
                        <p class="text-slate-600">a.n. {{ $settings->account_holder }}</p>
                    @endif
                </div>
            @endif

            @if (! $settings->qris_url && ! $settings->has_bank)
                <p class="mt-4 rounded-lg bg-orange-50 px-4 py-3 text-sm text-orange-800">
                    Info pembayaran belum tersedia. Hubungi penjual lewat WhatsApp untuk mendapatkannya.
                </p>
            @endif

            <form method="POST" action="{{ route('orders.proof', $order->access_token) }}" enctype="multipart/form-data" class="mt-5">
                @csrf
                @error('proof')
                    <p class="mb-2 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">{{ $message }}</p>
                @enderror
                <label for="proof" class="mb-1 block text-sm font-semibold">
                    {{ $order->payment_proof_path ? 'Ganti bukti pembayaran' : 'Unggah bukti pembayaran' }}
                </label>
                @if ($order->payment_proof_path)
                    <p class="mb-2 text-xs text-green-700">✓ Bukti sudah terkirim dan menunggu pengecekan penjual.</p>
                @endif
                <input id="proof" name="proof" type="file" accept="image/*" required
                       class="block w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-teal-800">
                <button class="mt-3 w-full rounded-lg bg-[#0b3b4a] py-3 text-sm font-semibold text-white">Kirim bukti pembayaran</button>
            </form>
        </section>
    @endif

    <a href="{{ $waUrl }}" target="_blank" rel="noopener"
       class="mt-6 block rounded-lg bg-green-600 py-3 text-center text-sm font-semibold text-white">
        Hubungi penjual via WhatsApp
    </a>
</div>

<script>
    const copyBtn = document.getElementById('copy-acc');
    if (copyBtn && navigator.clipboard) {
        copyBtn.addEventListener('click', () => {
            navigator.clipboard.writeText(document.getElementById('acc').textContent.trim());
            copyBtn.textContent = 'Tersalin ✓';
        });
    } else if (copyBtn) {
        copyBtn.remove();
    }
</script>
@endsection