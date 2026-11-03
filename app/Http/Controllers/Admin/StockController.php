<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $movements = StockMovement::with(['commodity', 'user'])
            ->when($request->komoditas, fn ($q, $id) => $q->where('commodity_id', $id))
            ->when($request->jenis, fn ($q, $s) => $q->where('source', $s))
            ->latest('moved_at')->latest('id')
            ->paginate(20)->withQueryString();

        return view('admin.stock.index', [
            'movements'   => $movements,
            'commodities' => Commodity::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, StockService $stock): RedirectResponse
    {
        $data = $request->validate([
            'commodity_id' => ['required', 'exists:commodities,id'],
            'source'       => ['required', Rule::in(['purchase', 'sale', 'waste'])],
            'quantity'     => ['required', 'numeric', 'min:0.01', 'max:99999'],
            'unit_price'   => ['nullable', 'required_if:source,purchase,sale', 'numeric', 'min:0', 'max:99999999'],
            'moved_at'     => ['required', 'date', 'before_or_equal:today'],
            'note'         => ['nullable', 'string', 'max:150'],
        ], [
            'required'           => ':attribute wajib diisi.',
            'required_if'        => ':attribute wajib diisi untuk jenis ini.',
            'numeric'            => ':attribute harus berupa angka.',
            'min.numeric'        => ':attribute terlalu kecil.',
            'max'                => ':attribute terlalu besar atau panjang.',
            'before_or_equal'    => 'Tanggal tidak boleh di masa depan.',
            'in'                 => 'Jenis catatan tidak valid.',
            'exists'             => ':attribute tidak valid.',
        ], [
            'commodity_id' => 'Komoditas',
            'source'       => 'Jenis',
            'quantity'     => 'Jumlah',
            'unit_price'   => 'Harga per kg',
            'moved_at'     => 'Tanggal',
            'note'         => 'Catatan',
        ]);

        $commodity = Commodity::where('is_active', true)->findOrFail($data['commodity_id']);

        $stock->record(
            $commodity,
            $data['source'],
            (float) $data['quantity'],
            isset($data['unit_price']) ? (float) $data['unit_price'] : null,
            $data['note'] ?? null,
            Carbon::parse($data['moved_at'])->setTimeFrom(now()),
            auth()->id(),
        );

        return redirect()->route('admin.stok.index')
            ->with('status', "Catatan stok {$commodity->name} berhasil disimpan.");
    }
}