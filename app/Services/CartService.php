<?php

namespace App\Services;

use App\Models\Commodity;
use Illuminate\Support\Collection;

class CartService
{
    private const KEY = 'cart';

    public function raw(): array
    {
        return session(self::KEY, []);
    }

    public function add(Commodity $commodity, float $quantity): void
    {
        $cart = $this->raw();
        $new  = ($cart[$commodity->id] ?? 0) + $quantity;

        $cart[$commodity->id] = round(min($new, (float) $commodity->stock), 2);
        session([self::KEY => $cart]);
    }

    // $quantities: [id => jumlah]. Hanya item yang sudah ada di keranjang yang diubah.
    public function set(array $quantities): void
    {
        $cart = $this->raw();

        foreach ($quantities as $id => $qty) {
            if (! isset($cart[$id])) {
                continue;
            }

            $qty = (float) $qty;

            if ($qty <= 0) {
                unset($cart[$id]);
            } else {
                $cart[$id] = round($qty, 2);
            }
        }

        session([self::KEY => $cart]);
    }

    public function remove(int $id): void
    {
        $cart = $this->raw();
        unset($cart[$id]);
        session([self::KEY => $cart]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    // Item yang sudah habis atau dinonaktifkan dibuang, jumlah dibatasi sesuai stok
    public function items(): Collection
    {
        $cart = $this->raw();

        if (! $cart) {
            return collect();
        }

        $commodities = Commodity::where('is_active', true)
            ->where('stock', '>', 0)
            ->whereIn('id', array_keys($cart))
            ->get()->keyBy('id');

        $clean = [];
        $items = collect();

        foreach ($cart as $id => $qty) {
            $commodity = $commodities->get($id);

            if (! $commodity) {
                continue;
            }

            $qty = round(min((float) $qty, (float) $commodity->stock), 2);

            if ($qty <= 0) {
                continue;
            }

            $clean[$id] = $qty;
            $items->push([
                'commodity' => $commodity,
                'quantity'  => $qty,
                'subtotal'  => $qty * $commodity->selling_price,
            ]);
        }

        session([self::KEY => $clean]);

        return $items;
    }

    public function total(Collection $items): float
    {
        return (float) $items->sum('subtotal');
    }
}