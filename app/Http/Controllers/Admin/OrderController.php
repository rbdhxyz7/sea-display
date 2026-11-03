<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Commodity;
use App\Models\Order;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    private const TRANSITIONS = [
        'confirm'  => ['from' => ['pending_payment'],               'to' => 'paid'],
        'process'  => ['from' => ['paid'],                          'to' => 'processing'],
        'complete' => ['from' => ['paid', 'processing'],            'to' => 'done'],
        'cancel'   => ['from' => ['pending_payment', 'paid', 'processing'], 'to' => 'cancelled'],
    ];

    public function index(Request $request)
    {
        $orders = Order::withCount('items')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->q, fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('order_code', 'like', "%{$term}%")
                ->orWhere('customer_name', 'like', "%{$term}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.commodity');

        return view('admin.orders.show', compact('order'));
    }

    // Koreksi jumlah tiap item sesuai hasil timbang (hanya sebelum pembayaran dikonfirmasi)
    public function items(Request $request, Order $order)
    {
        if ($order->status !== 'pending_payment') {
            return back()->with('error', 'Jumlah hanya bisa diubah sebelum pembayaran dikonfirmasi.');
        }

        $data = $request->validate([
            'quantities'   => ['required', 'array'],
            'quantities.*' => ['required', 'numeric', 'min:0.01', 'max:999'],
        ], [
            'required' => 'Jumlah wajib diisi.',
            'numeric'  => 'Jumlah harus berupa angka.',
            'min'      => 'Jumlah minimal 0,01.',
            'max'      => 'Jumlah terlalu besar.',
        ]);

        DB::transaction(function () use ($order, $data) {
            $items = $order->items()->get();
            $stocks = Commodity::whereIn('id', $items->pluck('commodity_id'))->get()->keyBy('id');

            foreach ($items as $item) {
                $qty = round((float) ($data['quantities'][$item->id] ?? $item->quantity), 2);
                $c = $stocks->get($item->commodity_id);

                if ($qty > $c->stock) {
                    throw ValidationException::withMessages([
                        'quantities' => "Stok {$c->name} hanya tersisa " . (float) $c->stock . " {$c->unit}.",
                    ]);
                }

                $item->update(['quantity' => $qty]);
            }

            $order->update([
                'final_total' => $order->items()->get()->sum(fn ($i) => $i->quantity * $i->unit_price),
            ]);
        });

        return back()->with('status', 'Jumlah disimpan dan total akhir dihitung ulang.');
    }

    public function status(Request $request, Order $order, StockService $stock)
    {
        $action = $request->validate([
            'action' => ['required', Rule::in(array_keys(self::TRANSITIONS))],
        ])['action'];

        $transition = self::TRANSITIONS[$action];

        DB::transaction(function () use ($order, $action, $transition, $stock) {
            // Kunci dan cek ulang status, supaya klik ganda tidak mengurangi stok dua kali
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if (! in_array($order->status, $transition['from'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Perubahan status tidak diizinkan dari status saat ini. Muat ulang halaman.',
                ]);
            }

            if ($action === 'confirm') {
                foreach ($order->items()->with('commodity')->get() as $item) {
                    $stock->record(
                        $item->commodity, 'order', (float) $item->quantity, (float) $item->unit_price,
                        "Pesanan {$order->order_code}", null, auth()->id(), $order->id, (float) $item->cost_price,
                    );
                }

                $order->final_total ??= $order->estimated_total;
            }

            if ($action === 'cancel' && $order->status !== 'pending_payment') {
                $deducted = StockMovement::where('order_id', $order->id)->where('source', 'order')->with('commodity')->get();

                foreach ($deducted as $m) {
                    $stock->record(
                        $m->commodity, 'order_return', (float) $m->quantity, (float) $m->unit_price,
                        "Pembatalan {$order->order_code}", null, auth()->id(), $order->id, (float) $m->cost_price,
                    );
                }
            }

            $order->status = $transition['to'];
            $order->user_id = auth()->id();
            $order->save();
        });

        return back()->with('status', 'Status pesanan diperbarui.');
    }

    // Bukti bayar disimpan privat, jadi hanya bisa dibuka lewat rute admin ini
    public function proof(Order $order)
    {
        abort_unless($order->payment_proof_path && Storage::disk('local')->exists($order->payment_proof_path), 404);

        return Storage::disk('local')->response($order->payment_proof_path);
    }
}