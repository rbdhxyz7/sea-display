@extends('layouts.admin')

@section('title', 'Pesanan')

@section('content')
@php
    $rp = fn ($n) => 'Rp ' . number_format($n, 0, ',', '.');
    $badge = fn ($s) => match ($s) {
        'pending_payment'    => 'bg-orange-100 text-orange-800',
        'paid', 'processing' => 'bg-blue-100 text-blue-800',
        'done'               => 'bg-green-100 text-green-800',
        default              => 'bg-red-100 text-red-800',
    };
@endphp

<p class="text-xs font-bold uppercase tracking-widest text-teal-700">Operasional</p>
<h1 class="mt-1 text-2xl font-semibold text-[#0b3b4a]">Pesanan</h1>
<p class="text-sm text-slate-500">Pesanan dari pembeli lewat katalog online.</p>

<section class="mt-6 rounded-xl border border-slate-200 bg-white">
    <form method="GET" action="{{ route('admin.pesanan.index') }}" class="flex flex-wrap gap-2 p-4 sm:p-6">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari kode atau nama..."
               class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm sm:max-w-xs">
        <select name="status" onchange="this.form.submit()" class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm">
            <option value="">Semua status</option>
            @foreach (\App\Models\Order::STATUSES as $key => $label)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold">Cari</button>
    </form>

    @if ($orders->isEmpty())
        <p class="border-t border-slate-100 p-10 text-center text-sm text-slate-500">Belum ada pesanan.</p>
    @else
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-left text-sm">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-3">Kode</th>
                        <th class="px-3 py-3">Pelanggan</th>
                        <th class="px-3 py-3">Item</th>
                        <th class="px-3 py-3">Total</th>
                        <th class="px-3 py-3">Status</th>
                        <th class="px-3 py-3">Bukti</th>
                        <th class="px-3 py-3">Dibuat</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($orders as $o)
                        <tr>
                            <td class="px-6 py-3 font-semibold">{{ $o->order_code }}</td>
                            <td class="px-3 py-3">{{ $o->customer_name }}</td>
                            <td class="px-3 py-3 text-slate-500">{{ $o->items_count }}</td>
                            <td class="px-3 py-3 font-semibold">{{ $rp($o->final_total ?? $o->estimated_total) }}</td>
                            <td class="px-3 py-3">
                                <span class="rounded-full px-2 py-0.5 text-xs font-semibold {{ $badge($o->status) }}">{{ $o->status_label }}</span>
                            </td>
                            <td class="px-3 py-3">{!! $o->payment_proof_path ? '<span class="text-green-700">✓ Ada</span>' : '<span class="text-slate-400">–</span>' !!}</td>
                            <td class="px-3 py-3 text-slate-500">{{ $o->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-3">
                                <a href="{{ route('admin.pesanan.show', $o) }}" class="rounded-lg border border-teal-200 px-3 py-1.5 text-xs font-semibold text-teal-700">Buka</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-4">{{ $orders->links() }}</div>
    @endif
</section>
@endsection