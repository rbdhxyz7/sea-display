<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', ['settings' => StoreSetting::current()]);
    }

    public function update(Request $request)
    {
        // Izinkan penulisan 0812..., ubah otomatis ke format 62812...
        $request->merge([
            'whatsapp_number' => Order::normalizePhone((string) $request->input('whatsapp_number'))
                ?? $request->input('whatsapp_number'),
        ]);

        $data = $request->validate([
            'whatsapp_number' => ['required', 'regex:/^628\d{8,11}$/'],
            'bank_name'       => ['nullable', 'string', 'max:50'],
            'account_number'  => ['nullable', 'string', 'max:30'],
            'account_holder'  => ['nullable', 'string', 'max:100'],
            'qris'            => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'required' => ':attribute wajib diisi.',
            'regex'    => ':attribute tidak valid. Contoh: 081234567890.',
            'max'      => ':attribute terlalu panjang atau besar.',
            'image'    => 'File harus berupa gambar.',
            'mimes'    => 'Format gambar harus JPG, PNG, atau WebP.',
            'uploaded' => 'Gambar gagal diunggah. Ukurannya mungkin melebihi batas server.',
        ], [
            'whatsapp_number' => 'Nomor WhatsApp',
            'bank_name'       => 'Nama bank',
            'account_number'  => 'Nomor rekening',
            'account_holder'  => 'Nama pemilik rekening',
            'qris'            => 'Gambar QRIS',
        ]);

        $settings = StoreSetting::current();

        if ($request->hasFile('qris')) {
            // QRIS disimpan apa adanya, tanpa dikecilkan, supaya tetap terbaca pemindai
            $path = $request->file('qris')->store('qris', 'public');

            if ($settings->qris_image_path) {
                Storage::disk('public')->delete($settings->qris_image_path);
            }

            $data['qris_image_path'] = $path;
        }

        unset($data['qris']);
        $settings->update($data);

        return redirect()->route('admin.pengaturan.edit')->with('status', 'Pengaturan toko disimpan.');
    }
}