@extends('layouts.admin')

@section('title', 'Catat Stok')

@section('content')
@php
    $fmt = fn ($n) => rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',');
    $rp  = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $input = 'w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm';
    $meta = $commodities->mapWithKeys(fn ($c) => [$c->id => [
        'cost' => $c->cost_price, 'price' => $c->selling_price, 'unit' => $c->unit, 'stock' => $c->stock,
    ]]);
@endphp

<p class="text-xs font-bold uppercase tracking-widest text-teal-700">Operasional</p>
<h1 class="mt-1 text-2xl font-semibold text-[#0b3b4a]">Catat Stok</h1>
<p class="text-sm text-slate-500">Catat stok masuk, penjualan di lapak, dan susut. Stok berubah otomatis.</p>

<form method="POST" action="{{ route('admin.stok.store') }}"
      class="mt-6 rounded-xl border border-slate-200 bg-white p-4 sm:p-6">
    @csrf

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            <ul class="list-disc pl-4">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label for="commodity_id" class="mb-1 block text-sm font-semibold">Komoditas</label>
            <select id="commodity_id" name="commodity_id" required class="{{ $input }}">
                <option value="">Pilih komoditas</option>
                @foreach ($commodities as $c)
                    <option value="{{ $c->id }}" @selected(old('commodity_id') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <p id="stock-info" class="mt-1 text-xs text-slate-500"></p>
        </div>
        <div>
            <label for="source" class="mb-1 block text-sm font-semibold">Jenis</label>
            <select id="source" name="source" required class="{{ $input }}">
                <option value="purchase" @selected(old('source') === 'purchase')>Stok masuk (kulakan)</option>
                <option value="sale" @selected(old('source') === 'sale')>Penjualan langsung</option>
                <option value="waste" @selected(old('source') === 'waste')>Susut / rusak</option>
            </select>
        </div>
        <div>
            <label for="quantity" class="mb-1 block text-sm font-semibold">Jumlah (<span id="unit-label">kg</span>)</label>
            <input id="quantity" name="quantity" type="number" min="0.01" step="any" inputmode="decimal" required
                   value="{{ old('quantity') }}" class="{{ $input }}">
        </div>
        <div id="price-wrap">
            <label for="unit_price" class="mb-1 block text-sm font-semibold"><span id="price-label">Harga kulakan</span> per kg</label>
            <input id="unit_price" name="unit_price" type="number" min="0" step="any" inputmode="decimal"
                   value="{{ old('unit_price') }}" class="{{ $input }}">
        </div>
        <div>
            <label for="moved_at" class="mb-1 block text-sm font-semibold">Tanggal</label>
            <input id="moved_at" name="moved_at" type="date" max="{{ now()->toDateString() }}" required
                   value="{{ old('moved_at', now()->toDateString()) }}" class="{{ $input }}">
        </div>
        <div>
            <label for="note" class="mb-1 block text-sm font-semibold">Catatan (opsional)</label>
            <input id="note" name="note" maxlength="150" value="{{ old('note') }}"
                   placeholder="Contoh: kulakan dari TPI" class="{{ $input }}">
        </div>
    </div>

    <p id="purchase-hint" class="mt-3 text-xs text-slate-500">
        Harga modal komoditas otomatis diperbarui ke harga kulakan ini.
    </p>
    <div class="mt-4 flex justify-end">
        <button class="rounded-lg bg-[#0b3b4a] px-5 py-2.5 text-sm font-semibold text-white">Simpan Catatan</button>
    </div>
</form>

<section class="mt-6 rounded-xl border border-slate-200 bg-white">
    <div class="flex flex-wrap items-end justify-between gap-3 p-4 sm:p-6">
        <h2 class="text-lg font-medium text-[#0b3b4a]">Riwayat Stok</h2>
        <form method="GET" action="{{ route('admin.stok.index') }}" class="flex w-full gap-2 sm:w-auto">
            <select name="komoditas" onchange="this.form.submit()" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                <option value="">Semua komoditas</option>
                @foreach ($commodities as $c)
                    <option value="{{ $c->id }}" @selected(request('komoditas') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="jenis" onchange="this.form.submit()" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
                <option value="">Semua jenis</option>
                @foreach (\App\Models\StockMovement::SOURCES as $key => $label)
                    <option value="{{ $key }}" @selected(request('jenis') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if ($movements->isEmpty())
        <p class="border-t border-slate-100 p-10 text-center text-sm text-slate-500">Belum ada catatan stok.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Tanggal</th>
                        <th class="px-3 py-3">Komoditas</th>
                        <th class="px-3 py-3">Jenis</th>
                        <th class="px-3 py-3">Jumlah</th>
                        <th class="px-3 py-3">Harga/kg</th>
                        <th class="px-3 py-3">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($movements as $m)
                        <tr>
                            <td class="px-6 py-3 text-slate-500">{{ $m->moved_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-3 font-semibold">{{ $m->commodity->name }}</td>
                            <td class="px-3 py-3">{{ $m->source_label }}</td>
                            <td class="px-3 py-3 font-semibold {{ $m->type === 'in' ? 'text-green-700' : 'text-red-600' }}">
                                {{ $m->type === 'in' ? '+' : '−' }}{{ $fmt($m->quantity) }} {{ $m->commodity->unit }}
                            </td>
                            <td class="px-3 py-3 text-slate-500">{{ $m->unit_price !== null ? $rp($m->unit_price) : '–' }}</td>
                            <td class="px-3 py-3 text-slate-500">{{ $m->note ?: '–' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-4">{{ $movements->links() }}</div>
    @endif
</section>

<script>
    const meta = @js($meta);
    const labels = { purchase: 'Harga kulakan', sale: 'Harga jual' };
    const el = (id) => document.getElementById(id);
    const commodity = el('commodity_id'), source = el('source'), price = el('unit_price');

    function sync(prefill) {
        const m = meta[commodity.value];
        const s = source.value;
        el('unit-label').textContent = m ? m.unit : 'kg';
        el('stock-info').textContent = m ? 'Stok saat ini: ' + m.stock + ' ' + m.unit : '';
        el('price-wrap').classList.toggle('hidden', s === 'waste');
        el('purchase-hint').classList.toggle('hidden', s !== 'purchase');
        el('price-label').textContent = labels[s] || '';
        if (prefill && m && s !== 'waste') price.value = s === 'sale' ? m.price : m.cost;
    }
    commodity.addEventListener('change', () => sync(true));
    source.addEventListener('change', () => sync(true));
    sync(!price.value);
</script>
@endsection