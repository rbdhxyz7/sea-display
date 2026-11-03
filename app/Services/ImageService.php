<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ImageService
{
    // Perkecil, putar sesuai arah foto ponsel, simpan sebagai JPG di disk yang dipilih
    public function store(UploadedFile $file, string $dir, string $disk, string $field, int $max = 1600): string
    {
        $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));

        if (! $image) {
            throw ValidationException::withMessages([$field => 'Foto tidak bisa diproses. Coba foto lain.']);
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

        if ($width > $max || $height > $max) {
            $scale = $max / max($width, $height);
            $image = imagescale($image, (int) round($width * $scale), (int) round($height * $scale));
        }

        // Latar putih, supaya gambar PNG transparan tidak berubah hitam saat jadi JPG
        $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        ob_start();
        imagejpeg($canvas, null, 85);
        $jpeg = ob_get_clean();

        $path = $dir . '/' . Str::random(24) . '.jpg';
        Storage::disk($disk)->put($path, $jpeg);

        return $path;
    }
}