<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Commodity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Services\StockService;

class CommodityController extends Controller
{
    public function create()
    {
        return view('admin.commodities.form', [
            'commodity'  => new Commodity(['unit' => 'kg', 'low_stock_threshold' => 10]),
            'categories' => Category::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->storePhoto($request->file('photo'));
        }

        // Stok awal tidak diisi langsung, tapi dicatat sebagai saldo awal
        $openingStock = (float) $data['stock'];
        $data['stock'] = 0;

        $commodity = Commodity::create($data);

        if ($openingStock > 0) {
            app(StockService::class)->record(
                $commodity, 'opening', $openingStock, $commodity->cost_price, 'Saldo awal', null, auth()->id()
            );
        }

        return redirect()->route('admin.dashboard')->with('status', 'Komoditas berhasil ditambahkan.');
    }

    public function edit(Commodity $commodity)
    {
        return view('admin.commodities.form', [
            'commodity'  => $commodity,
            'categories' => Category::orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Commodity $commodity): RedirectResponse
    {
        $data = $this->validated($request, $commodity);

        unset($data['stock']); // stok hanya berubah lewat Catat Stok

        if ($request->hasFile('photo')) {
            $newPath = $this->storePhoto($request->file('photo'));

            if ($commodity->photo_path) {
                Storage::disk('public')->delete($commodity->photo_path);
            }

            $data['photo_path'] = $newPath;
        }

        $commodity->update($data);

        return redirect()->route('admin.dashboard')->with('status', 'Perubahan berhasil disimpan.');
    }

    // "Hapus" = sembunyikan dari katalog, supaya riwayat tidak rusak
    public function destroy(Commodity $commodity): RedirectResponse
    {
        $commodity->update(['is_active' => false]);

        return redirect()->route('admin.dashboard')->with('status', 'Komoditas dihapus dari katalog.');
    }

    private function validated(Request $request, ?Commodity $commodity = null): array
    {
        $data = $request->validate([
            'name'                => ['required', 'string', 'max:100', Rule::unique('commodities', 'name')->ignore($commodity?->id)],
            'category_id'         => ['required', 'exists:categories,id'],
            'cost_price'          => ['required', 'numeric', 'min:0', 'max:99999999'],
            'selling_price'       => ['required', 'numeric', 'min:0', 'max:99999999'],
            'stock'               => ['required', 'numeric', 'min:0', 'max:99999'],
            'unit'                => ['required', 'string', 'max:10'],
            'low_stock_threshold' => ['required', 'numeric', 'min:0', 'max:99999'],
            'photo'               => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ], [
            'required'      => ':attribute wajib diisi.',
            'numeric'       => ':attribute harus berupa angka.',
            'min.numeric'   => ':attribute tidak boleh kurang dari :min.',
            'max.numeric'   => ':attribute terlalu besar.',
            'max.string'    => ':attribute terlalu panjang.',
            'unique'        => ':attribute sudah dipakai komoditas lain.',
            'exists'        => ':attribute tidak valid.',
            'image'         => 'File harus berupa gambar.',
            'mimes'         => 'Format foto harus JPG, PNG, atau WebP.',
            'photo.max'     => 'Ukuran foto maksimal 8 MB.',
            'uploaded'      => 'Foto gagal diunggah. Ukurannya mungkin melebihi batas server.',
        ], [
            'name'                => 'Nama komoditas',
            'category_id'         => 'Kategori',
            'cost_price'          => 'Harga modal',
            'selling_price'       => 'Harga jual',
            'stock'               => 'Stok',
            'unit'                => 'Satuan',
            'low_stock_threshold' => 'Batas stok menipis',
            'photo'               => 'Foto',
        ]);

        unset($data['photo']);

        return $data;
    }

    // Perkecil ke maksimal 1200 px, putar sesuai arah foto ponsel, simpan sebagai JPG
    private function storePhoto(UploadedFile $file): string
    {
        $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));

        if (! $image) {
            throw ValidationException::withMessages(['photo' => 'Foto tidak bisa diproses. Coba foto lain.']);
        }

        if (function_exists('exif_read_data') && $file->getMimeType() === 'image/jpeg') {
            $orientation = @exif_read_data($file->getRealPath())['Orientation'] ?? 1;

            $rotated = match ($orientation) {
                3       => imagerotate($image, 180, 0),
                6       => imagerotate($image, -90, 0),
                8       => imagerotate($image, 90, 0),
                default => $image,
            };

            if ($rotated) {
                $image = $rotated;
            }
        }

        $width  = imagesx($image);
        $height = imagesy($image);
        $max    = 1200;

        if ($width > $max || $height > $max) {
            $scale = $max / max($width, $height);
            $image = imagescale($image, (int) round($width * $scale), (int) round($height * $scale));
        }

        ob_start();
        imagejpeg($image, null, 82);
        $jpeg = ob_get_clean();

        $path = 'commodities/' . Str::random(24) . '.jpg';
        Storage::disk('public')->put($path, $jpeg);

        return $path;
    }
}