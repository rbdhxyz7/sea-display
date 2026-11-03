<?php

namespace App\Http\Controllers;

use App\Models\Commodity;
use App\Models\Order;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function create(CartService $cart)
    {
        $items = $cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjangmu masih kosong.');
        }

        return view('checkout.create', [
            'items' => $items,
            'total' => $cart->total($items),
        ]);
    }

    public function store(Request $request, CartService $cart)
    {
        $items = $cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Keranjangmu masih kosong.');
        }

        $data = $request->validate([
            'customer_name'   => ['required', 'string', 'max:100'],
            'customer_phone'  => ['required', 'string', 'max:20'],
            'delivery_method' => ['required', Rule::in(['pickup', 'delivery'])],
            'address'         => ['required_if:delivery_method,delivery', 'nullable', 'string', 'max:300'],
            'note'            => ['nullable', 'string', 'max:200'],
        ], [
            'required'    => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi untuk pengantaran.',
            'max'         => ':attribute terlalu panjang.',
            'in'          => 'Pilihan pengambilan tidak valid.',
        ], [
            'customer_name'   => 'Nama',
            'customer_phone'  => 'Nomor WhatsApp',
            'delivery_method' => 'Cara pengambilan',
            'address'         => 'Alamat',
            'note'            => 'Catatan',
        ]);

        $phone = Order::normalizePhone($data['customer_phone']);

        if (! $phone) {
            throw ValidationException::withMessages([
                'customer_phone' => 'Nomor WhatsApp tidak valid. Contoh: 081234567890.',
            ]);
        }

        $order = DB::transaction(function () use ($items, $data, $phone) {
            // Cek ulang stok dengan penguncian, karena stok bisa berubah sejak masuk keranjang
            $locked = Commodity::whereIn('id', $items->pluck('commodity.id'))
                ->lockForUpdate()->get()->keyBy('id');

            $total = 0;

            foreach ($items as $item) {
                $c = $locked->get($item['commodity']->id);

                if (! $c || ! $c->is_active || $item['quantity'] > $c->stock) {
                    throw ValidationException::withMessages([
                        'cart' => 'Stok ' . ($c->name ?? 'salah satu produk') . ' berubah. Periksa keranjang lagi.',
                    ]);
                }

                $total += $item['quantity'] * $c->selling_price;
            }

            $order = Order::create([
                'access_token'    => Str::random(32),
                'customer_name'   => $data['customer_name'],
                'customer_phone'  => $phone,
                'delivery_method' => $data['delivery_method'],
                'address'         => $data['delivery_method'] === 'delivery' ? ($data['address'] ?? null) : null,
                'note'            => $data['note'] ?? null,
                'estimated_total' => $total,
            ]);

            $order->update(['order_code' => 'BLD-' . str_pad($order->id, 4, '0', STR_PAD_LEFT)]);

            foreach ($items as $item) {
                $c = $locked->get($item['commodity']->id);

                $order->items()->create([
                    'commodity_id'   => $c->id,
                    'commodity_name' => $c->name,
                    'quantity'       => $item['quantity'],
                    'unit'           => $c->unit,
                    'unit_price'     => $c->selling_price,
                    'cost_price'     => $c->cost_price,
                ]);
            }

            return $order;
        });

        $cart->clear();

        return redirect()->route('orders.show', $order->access_token);
    }
}