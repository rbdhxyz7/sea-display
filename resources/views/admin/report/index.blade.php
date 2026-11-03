@extends('layouts.admin')

@section('title', 'Laporan Penjualan')

@section('content')
@php
    $rp  = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');

    $revenue   = (float) $totals->revenue;
    $cost      = (float) $totals->cost;
    $profit    = $revenue - $cost;
    $marginPct = $cost > 0 ? round($profit / $cost * 100) : 0;
    $loss      = (float) $waste->loss;

    $periodLabel = $from->copy()->locale('id')->translatedFormat('d M Y')
        . ' – ' . $to->copy()->locale('id')->translatedFormat('d M Y');

    $lastMonth = now()->subMonthNoOverflow();
    $presets = [
        'Hari ini'   => [now()->toDateString(), now()->toDateString()],
        '7 hari'     => [now()->subDays(6)->toDateString(), now()->toDateString()],
        'Bulan ini'  => [now()->startOfMonth()->toDateString(), now()->toDateString()],
        'Bulan lalu' => [$lastMonth->copy()->startOfMonth()->toDateString(), $lastMonth->copy()->endOfMonth()->toDateString()],
    ];
@endphp

<div class="print-only mb-4">
    <p class="text-lg font-bold">Laporan Penjualan – UD Bintang Laut Desy</p>
    <p class="text-sm">Periode: {{ $periodLabel }}</p>
</div>

<div class="no-print">
    <p class="text-xs font-bold uppercase tracking-widest text-teal-700">Operasional</p>
    <h1 class="mt-1 text-2xl font-semibold text-[#0b3b4a]">Laporan Penjualan</h1>
    <p class="text-sm text-slate-500">Periode: {{ $periodLabel }}</p>
</div>

<form method="GET" action="{{ route('admin.laporan.index') }}"
      class="no-print mt-5 rounded-xl border border-slate-200 bg-white p-4">
    @if ($errors->any())
        <p class="mb-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">{{ $errors->first() }}</p>
    @endif
    <div class="flex flex-wrap items-end gap-3">
        <div>
            <label for="dari" class="mb-1 block text-xs font-semibold">Dari</label>
            <input id="dari" type="date" name="dari" value="{{ $from->toDateString() }}"
                   class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label for="sampai" class="mb-1 block text-xs font-semibold">Sampai</label>
            <input id="sampai" type="date" name="sampai" value="{{ $to->toDateString() }}"
                   class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <button class="rounded-lg bg-[#0b3b4a] px-4 py-2 text-sm font-semibold text-white">Tampilkan</button>
    </div>
    <div class="mt-3 flex flex-wrap gap-2 text-xs">
        @foreach ($presets as $label => [$a, $b])
            <a href="{{ route('admin.laporan.index', ['dari' => $a, 'sampai' => $b]) }}"
               class="rounded-full border border-slate-200 px-3 py-1.5 text-slate-600">{{ $label }}</a>
        @endforeach
    </div>
</form>

<div class="no-print mt-4 flex flex-wrap gap-2">
    <a href="{{ route('admin.laporan.csv', ['dari' => $from->toDateString(), 'sampai' => $to->toDateString()]) }}"
       class="rounded-lg border border-teal-200 px-4 py-2 text-sm font-semibold text-teal-700">Unduh CSV (Excel)</a>
    <button type="button" onclick="window.print()"
            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold">Cetak / Simpan PDF</button>
</div>

<div class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Omzet</p>
        <p class="mt-1 text-xl font-semibold sm:text-2xl">{{ $rp($revenue) }}</p>
        <p class="text-[11px] text-slate-400">{{ $totals->trx }} transaksi · {{ $fmt($totals->qty) }} kg</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Modal terjual</p>
        <p class="mt-1 text-xl font-semibold sm:text-2xl">{{ $rp($cost) }}</p>
        <p class="text-[11px] text-slate-400">Harga modal saat transaksi</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Laba kotor</p>
        <p class="mt-1 text-xl font-semibold sm:text-2xl {{ $profit < 0 ? 'text-red-600' : 'text-green-700' }}">{{ $rp($profit) }}</p>
        <p class="text-[11px] text-slate-400">Margin {{ $marginPct }}% terhadap modal</p>
    </div>
    <div class="rounded-xl border border-slate-200 bg-white p-4">
        <p class="text-xs text-slate-500">Susut / rusak</p>
        <p class="mt-1 text-xl font-semibold sm:text-2xl {{ $loss > 0 ? 'text-red-600' : '' }}">{{ $rp($loss) }}</p>
        <p class="text-[11px] text-slate-400">{{ $fmt($waste->qty) }} kg terbuang</p>
    </div>
</div>

<p class="mt-3 text-sm text-slate-600">
    Laba setelah susut:
    <span class="font-semibold {{ ($profit - $loss) < 0 ? 'text-red-600' : 'text-green-700' }}">{{ $rp($profit - $loss) }}</span>
</p>

@if ($totals->trx == 0)
    <p class="mt-6 rounded-xl border border-slate-200 bg-white p-10 text-center text-sm text-slate-500">
        Belum ada penjualan pada periode ini. Catat penjualan lewat menu Catat Stok, atau ubah rentang tanggalnya.
    </p>
@else
    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-4 sm:p-6">
        <h2 class="text-lg font-medium text-[#0b3b4a]">Omzet per hari</h2>
        <div class="relative mt-4 h-64">
            <canvas id="chart" role="img" aria-label="Grafik batang omzet per hari"></canvas>
        </div>
        <p id="chart-fallback" class="hidden text-sm text-slate-500">
            Grafik tidak bisa dimuat. Periksa koneksi internet, lalu muat ulang halaman.
        </p>
    </section>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white">
        <h2 class="p-4 text-lg font-medium text-[#0b3b4a] sm:p-6">Komoditas terlaris</h2>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[480px] text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Komoditas</th>
                        <th class="px-3 py-3">Terjual</th>
                        <th class="px-3 py-3">Omzet</th>
                        <th class="px-3 py-3">Laba</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($perCommodity as $row)
                        <tr>
                            <td class="px-6 py-3 font-semibold">{{ $row->name }}</td>
                            <td class="px-3 py-3">{{ $fmt($row->qty) }} {{ $row->unit }}</td>
                            <td class="px-3 py-3">{{ $rp($row->revenue) }}</td>
                            <td class="px-3 py-3 font-semibold {{ ($row->revenue - $row->cost) < 0 ? 'text-red-600' : 'text-green-700' }}">
                                {{ $rp($row->revenue - $row->cost) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        if (typeof Chart === 'undefined') {
            document.getElementById('chart').parentElement.classList.add('hidden');
            document.getElementById('chart-fallback').classList.remove('hidden');
        } else {
            new Chart(document.getElementById('chart'), {
                type: 'bar',
                data: {
                    labels: @js($labels),
                    datasets: [{
                        label: 'Omzet',
                        data: @js($values),
                        backgroundColor: '#0e8a8f',
                        borderRadius: 4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID') },
                        },
                    },
                },
            });
        }
    </script>
@endif
@endsection