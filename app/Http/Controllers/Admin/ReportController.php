<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->period($request);

        $totals = $this->salesQuery($from, $to)
            ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) AS revenue,
                         COALESCE(SUM(quantity * cost_price), 0) AS cost,
                         COALESCE(SUM(quantity), 0) AS qty,
                         COUNT(*) AS trx')
            ->first();

        $waste = StockMovement::where('source', 'waste')
            ->whereBetween('moved_at', [$from, $to])
            ->selectRaw('COALESCE(SUM(quantity * cost_price), 0) AS loss,
                         COALESCE(SUM(quantity), 0) AS qty')
            ->first();

        $perDay = $this->salesQuery($from, $to)
            ->selectRaw('DATE(moved_at) AS day, SUM(quantity * unit_price) AS revenue')
            ->groupBy('day')
            ->get()
            ->pluck('revenue', 'day');

        // Hari tanpa penjualan tetap tampil sebagai batang 0
        $labels = [];
        $values = [];
        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $labels[] = $d->format('d/m');
            $values[] = (float) ($perDay[$d->toDateString()] ?? 0);
        }

        $perCommodity = StockMovement::query()
            ->join('commodities', 'commodities.id', '=', 'stock_movements.commodity_id')
            ->where('stock_movements.source', 'sale')
            ->whereBetween('stock_movements.moved_at', [$from, $to])
            ->selectRaw('commodities.name, commodities.unit,
                         SUM(stock_movements.quantity) AS qty,
                         SUM(stock_movements.quantity * stock_movements.unit_price) AS revenue,
                         SUM(stock_movements.quantity * stock_movements.cost_price) AS cost')
            ->groupBy('commodities.id', 'commodities.name', 'commodities.unit')
            ->orderByDesc('revenue')
            ->get();

        return view('admin.report.index', compact(
            'from', 'to', 'totals', 'waste', 'labels', 'values', 'perCommodity'
        ));
    }

    public function csv(Request $request)
    {
        [$from, $to] = $this->period($request);

        $name = 'laporan-penjualan-' . $from->format('Ymd') . '-' . $to->format('Ymd') . '.csv';
        $num  = fn ($n) => str_replace('.', ',', (string) round($n, 2)); // koma desimal untuk Excel Indonesia

        $rows = $this->salesQuery($from, $to)->with('commodity')->orderBy('moved_at')->orderBy('id')->get();

        return response()->streamDownload(function () use ($rows, $num) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // penanda UTF-8 agar Excel membaca huruf dengan benar
            fputcsv($out, ['Tanggal', 'Komoditas', 'Jumlah', 'Satuan', 'Harga jual', 'Harga modal', 'Omzet', 'Laba'], ';');

            foreach ($rows as $m) {
                fputcsv($out, [
                    $m->moved_at->format('d/m/Y H:i'),
                    $m->commodity->name,
                    $num($m->quantity),
                    $m->commodity->unit,
                    $num($m->unit_price),
                    $num($m->cost_price),
                    $num($m->quantity * $m->unit_price),
                    $num($m->quantity * ($m->unit_price - $m->cost_price)),
                ], ';');
            }

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function salesQuery(Carbon $from, Carbon $to)
    {
        return StockMovement::sales()->whereBetween('moved_at', [$from, $to]);
    }

    private function period(Request $request): array
    {
        $request->validate([
            'dari'   => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ], [
            'date'           => 'Format tanggal tidak valid.',
            'after_or_equal' => 'Tanggal akhir tidak boleh sebelum tanggal awal.',
        ]);

        $from = Carbon::parse($request->filled('dari') ? $request->dari : now()->startOfMonth())->startOfDay();
        $to   = Carbon::parse($request->filled('sampai') ? $request->sampai : now())->endOfDay();

        // Batasi rentang maksimal 1 tahun supaya halaman tetap ringan
        if ($from->copy()->addDays(366)->lt($to)) {
            $to = $from->copy()->addDays(366)->endOfDay();
        }

        return [$from, $to];
    }
}