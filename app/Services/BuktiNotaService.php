<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;

class BuktiNotaService
{
    private const TARGET_BYTES = 500000;

    public function simpan(UploadedFile $file): string
    {
        if (! $file->isValid()) {
            throw new RuntimeException('File bukti nota tidak valid.');
        }

        $allowedMimes = ['image/jpeg', 'image/png'];
        if (! in_array($file->getMimeType(), $allowedMimes, true)) {
            throw new RuntimeException('Bukti nota harus berupa JPG/JPEG atau PNG.');
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new RuntimeException('Ukuran file bukti nota maksimal 10 MB sebelum kompresi.');
        }

        $directory = ROOTPATH . 'uploads/bukti_nota';
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Folder upload bukti nota tidak dapat dibuat.');
        }

        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->getTempName());
        $image->scaleDown(width: 1800, height: 1800);

        $filename = 'nota_' . date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.jpg';
        $targetPath = $directory . DIRECTORY_SEPARATOR . $filename;

        $quality = 82;
        do {
            $encoded = $image->toJpeg($quality, progressive: true, strip: true);
            $encoded->save($targetPath);
            $size = filesize($targetPath) ?: 0;
            $quality -= 8;
        } while ($size > self::TARGET_BYTES && $quality >= 34);

        if (($size ?? 0) > self::TARGET_BYTES) {
            @unlink($targetPath);
            throw new RuntimeException('Foto nota masih lebih dari 500 KB setelah kompresi. Gunakan foto dengan resolusi lebih kecil.');
        }

        return 'uploads/bukti_nota/' . $filename;
    }
}
