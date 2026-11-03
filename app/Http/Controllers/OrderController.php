<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\StoreSetting;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function show(string $token)
    {
        $order = Order::with('items')->where('access_token', $token)->firstOrFail();

        return view('orders.show', [
            'order'    => $order,
            'settings' => StoreSetting::current(),
        ]);
    }

    public function proof(Request $request, string $token, ImageService $images)
    {
        $order = Order::where('access_token', $token)->firstOrFail();

        if ($order->status !== 'pending_payment') {
            return back()->with('error', 'Pesanan ini sudah tidak menerima bukti pembayaran.');
        }

        $request->validate([
            'proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ], [
            'required' => 'Pilih foto bukti pembayaran dulu.',
            'image'    => 'File harus berupa gambar.',
            'mimes'    => 'Format foto harus JPG, PNG, atau WebP.',
            'max'      => 'Ukuran foto maksimal 8 MB.',
            'uploaded' => 'Foto gagal diunggah. Ukurannya mungkin melebihi batas server.',
        ]);

        // Disk "local" bersifat privat, tidak bisa dibuka lewat URL publik
        $path = $images->store($request->file('proof'), 'payment-proofs', 'local', 'proof', 1600);

        if ($order->payment_proof_path) {
            Storage::disk('local')->delete($order->payment_proof_path);
        }

        $order->update(['payment_proof_path' => $path]);

        return back()->with('status', 'Bukti pembayaran terkirim. Penjual akan memeriksanya.');
    }
        public function lookup()
    {
        return view('orders.lookup');
    }

    public function find(Request $request)
    {
        $data = $request->validate([
            'order_code' => ['required', 'string', 'max:20'],
            'phone'      => ['required', 'string', 'max:20'],
        ], [
            'required' => ':attribute wajib diisi.',
            'max'      => ':attribute terlalu panjang.',
        ], [
            'order_code' => 'Kode pesanan',
            'phone'      => 'Nomor WhatsApp',
        ]);

        $phone = Order::normalizePhone($data['phone']);

        $order = $phone
            ? Order::where('order_code', strtoupper(trim($data['order_code'])))->where('customer_phone', $phone)->first()
            : null;

        // Pesan sama untuk semua kegagalan, supaya kode pesanan tidak bisa ditebak
        if (! $order) {
            return back()->withInput()->withErrors([
                'order_code' => 'Pesanan tidak ditemukan. Periksa kode pesanan dan nomor WhatsApp.',
            ]);
        }

        return redirect()->route('orders.show', $order->access_token);
    }
}