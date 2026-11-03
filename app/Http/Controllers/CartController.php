<?php

namespace App\Http\Controllers;

use App\Models\Commodity;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartService $cart)
    {
        $items = $cart->items();

        return view('cart.index', [
            'items' => $items,
            'total' => $cart->total($items),
        ]);
    }

    public function add(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'commodity_id' => ['required', 'exists:commodities,id'],
            'quantity'     => ['nullable', 'numeric', 'min:0.5', 'max:999'],
        ]);

        $commodity = Commodity::where('is_active', true)->findOrFail($data['commodity_id']);

        if ($commodity->stock <= 0) {
            return back()->with('error', "{$commodity->name} sedang habis.");
        }

        $cart->add($commodity, (float) ($data['quantity'] ?? 1));

        return back()->with('status', "{$commodity->name} ditambahkan ke keranjang.");
    }

    public function update(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'quantities'   => ['nullable', 'array'],
            'quantities.*' => ['nullable', 'numeric', 'min:0', 'max:999'],
        ]);

        if ($request->filled('remove')) {
            $cart->remove((int) $request->input('remove'));

            return redirect()->route('cart.index')->with('status', 'Item dihapus dari keranjang.');
        }

        $cart->set($data['quantities'] ?? []);

        if ($request->has('checkout')) {
            return redirect()->route('checkout.create');
        }

        return redirect()->route('cart.index')->with('status', 'Keranjang diperbarui.');
    }
}