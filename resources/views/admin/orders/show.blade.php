@extends('layouts.admin')

@section('title', 'Pesanan ' . $order->order_code)

@section('content')
@php
    $rp  = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');

    $badge = match ($order->status) {
        'pending_payment'    => 'bg-orange-100 text-orange-800',
        'paid', 'processing' => 'bg-blue-100 text-blue-800',
        'done'               => 'bg-green-100 text-green-800',
        default              => 'bg-red-100 text-red-800',
    };

    $editable = $order->status === 'pending_payment';
    $total = $order->final_total ?? $order->estimated_total;

    $actionMap = [
        'pending_payment' => [
            ['confirm', 'Konfirmasi pembayaran', 'bg-[#0b3b4a] text-white', 'Konfirmasi pembayaran? Stok akan dikurangi sesuai jumlah pesanan.'],
            ['cancel', 'Batalkan pesanan', 'border border-red-200 text-red-600', 'Batalkan pesanan ini?'],
        ],
        'paid' => [
            ['process', 'Mulai proses', 'bg-[#0b3b4a] text-white', 'Tandai pesanan mulai diproses?'],
            ['complete', 'Tandai selesai', 'border border-teal-200 text-teal-700', 'Tandai pesanan selesai?'],
            ['cancel', 'Batalkan pesanan', 'border border-red-200 text-red-600', 'Batalkan pesanan? Stok akan dikembalikan.'],
        ],
        'processing' => [
            ['complete', 'Tandai selesai', 'bg-[#0b3b4a] text-white', 'Tandai pesanan selesai?'],
            ['cancel', 'Batalkan pesanan', 'border border-red-200 text-red-600', 'Batalkan pesanan? Stok akan dikembalikan.'],
        ],
    ];
    $actions = $actionMap[$order->status] ?? [];

    $lines = ["Halo {$order->customer_name}, ini Bu Desy dari UD Bintang Laut Desy.", "Pesanan {$order->order_code}:"];
    foreach ($order->items as $i) {
        $lines[] = "- {$i->commodity_name} {$fmt($i->quantity)} {$i->unit}";
    }
    $lines[] = 'Total: ' . $rp($total);
    $waUrl = 'https://wa.me/' . $order->customer_phone . '?text=' . rawurlencode(implode("\n", $lines));
@endphp

<a href="{{ route('admin.pesanan.index') }}" class="text-sm text-teal-700">← Kembali ke daftar pesanan</a>

<div class="mt-3 flex flex-wrap items-center gap-3">
    <h1 class="text-2xl font-semibold text-[#0b3b4a]">{{ $order->order_code }}</h1>
    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">{{ $order->status_label }}</span>
    <span class="text-xs text-slate-500">{{ $order->created_at->format('d/m/Y H:i') }}</span>
</div>

@if ($errors->any())
    <p class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $errors->first() }}</p>
@endif
@if (session('error'))
    <p class="mt-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ session('error') }}</p>
@endif

<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">

        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-semibold text-[#0b3b4a]">Isi pesanan</h2>

            @if ($editable)
                <p class="mt-1 text-xs text-slate-500">Timbang ikannya, lalu sesuaikan jumlah di bawah. Total akhir dihitung otomatis dan tampil di halaman pesanan pembeli.</p>
            @endif

            <form method="POST" action="{{ route('admin.pesanan.items', $order) }}" class="mt-3">
                @csrf @method('PATCH')
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach ($order->items as $i)
                        <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                            <div class="min-w-0 flex-1 basis-40">
                                <p class="font-semibold">{{ $i->commodity_name }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $rp($i->unit_price) }} / {{ $i->unit }}
                                    @if ($editable) · stok sekarang {{ $fmt($i->commodity->stock) }} {{ $i->unit }} @endif
                                </p>
                            </div>
                            @if ($editable)
                                <div class="flex items-center gap-2">
                                    <input type="number" name="quantities[{{ $i->id }}]" value="{{ old('quantities.' . $i->id, $i->quantity) }}"
                                           min="0.01" step="any" inputmode="decimal" aria-label="Jumlah {{ $i->commodity_name }}"
                                           class="w-24 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                    <span class="text-slate-500">{{ $i->unit }}</span>
                                </div>
                            @else
                                <span>{{ $fmt($i->quantity) }} {{ $i->unit }}</span>
                            @endif
                            <span class="w-28 text-right font-semibold">{{ $rp($i->quantity * $i->unit_price) }}</span>
                        </li>
                    @endforeach
                </ul>

                @if ($editable)
                    <button class="mt-3 rounded-lg border border-teal-200 px-4 py-2 text-sm font-semibold text-teal-700">Simpan jumlah &amp; hitung total</button>
                @endif
            </form>

            <div class="mt-4 space-y-1 border-t border-slate-200 pt-3 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">Estimasi total</span><span>{{ $rp($order->estimated_total) }}</span></div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Total akhir</span>
                    <span class="text-lg font-bold text-teal-700">{{ $order->final_total !== null ? $rp($order->final_total) : 'Belum dihitung' }}</span>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-semibold text-[#0b3b4a]">Pelanggan</h2>
            <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
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
            <a href="{{ $waUrl }}" target="_blank" rel="noopener"
               class="mt-4 inline-block rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white">Hubungi pelanggan via WhatsApp</a>
        </section>
    </div>

    <div class="space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-semibold text-[#0b3b4a]">Bukti pembayaran</h2>
            @if ($order->payment_proof_path)
                <a href="{{ route('admin.pesanan.bukti', $order) }}" target="_blank" class="mt-3 block">
                    <img src="{{ route('admin.pesanan.bukti', $order) }}" alt="Bukti pembayaran {{ $order->order_code }}"
                         class="max-h-72 w-full rounded-lg border border-slate-200 object-contain">
                </a>
                <p class="mt-2 text-xs text-slate-500">Klik gambar untuk membuka ukuran penuh.</p>
            @else
                <p class="mt-2 text-sm text-slate-500">Belum ada bukti pembayaran.</p>
            @endif
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5">
            <h2 class="font-semibold text-[#0b3b4a]">Ubah status</h2>
            @if ($actions)
                <div class="mt-3 space-y-2">
                    @foreach ($actions as [$action, $label, $style, $confirmText])
                        <form method="POST" action="{{ route('admin.pesanan.status', $order) }}"
                              onsubmit="return confirm(@js($confirmText))">
                            @csrf @method('PATCH')
                            <input type="hidden" name="action" value="{{ $action }}">
                            <button class="w-full rounded-lg px-4 py-2.5 text-sm font-semibold {{ $style }}">{{ $label }}</button>
                        </form>
                    @endforeach
                </div>
            @else
                <p class="mt-2 text-sm text-slate-500">Pesanan ini sudah ditutup, jadi statusnya tidak bisa diubah lagi.</p>
            @endif
        </section>
    </div>
</div>
@endsection