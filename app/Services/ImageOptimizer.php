<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageOptimizer
{
    /**
     * Mengoptimasi gambar dan menyimpannya ke storage public.
     * Mengubah ke WEBP, meresize jika kebesaran, dan mengcompress.
     *
     * @param UploadedFile $file
     * @param string $folder Direktori relatif ke storage/app/public/
     * @param int $maxWidth Maksimal lebar gambar (px)
     * @param int $quality Kualitas WEBP (0-100)
     * @return string Path menuju gambar relatif terhadap disk 'public'
     */
    public static function optimizeAndSave(UploadedFile $file, string $folder, int $maxWidth = 1200, int $quality = 80): string
    {
        // 1. Inisialisasi Intervention Image Manager dengan GD driver
        $manager = new ImageManager(new Driver());

        // 2. Baca file image
        $image = $manager->read($file->getRealPath());

        // 3. Resize jika lebarnya melebihi maxWidth (scale down proporsional)
        if ($image->width() > $maxWidth) {
            $image->scaleDown(width: $maxWidth);
        }

        // 4. Generate random filename dengan ekstensi .webp
        $filename = uniqid('img_', true) . '.webp';
        
        // 5. Pastikan folder tersebut ada
        if (!Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }
        
        // 6. Path absolut untuk di-save oleh Intervention
        $absolutePath = Storage::disk('public')->path($folder . '/' . $filename);

        // 7. Render jadi WEBP dan Simpan
        $image->toWebp($quality)->save($absolutePath);

        // 8. Return relative path untuk disimpan di database
        return $folder . '/' . $filename;
    }
}
