<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class BrandingUploadService
{
    public function simpan(UploadedFile $file, string $prefix): string
    {
        if (! $file->isValid()) {
            throw new RuntimeException('File branding tidak valid.');
        }

        $mimeMap = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];

        $mime = $file->getMimeType();
        if (! isset($mimeMap[$mime])) {
            throw new RuntimeException('File branding harus berupa JPG/JPEG atau PNG.');
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new RuntimeException('Ukuran file branding maksimal 5 MB.');
        }

        $directory = ROOTPATH . 'uploads/branding';
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw new RuntimeException('Folder upload branding tidak dapat dibuat.');
        }

        $filename = preg_replace('/[^a-z0-9_-]/i', '', $prefix)
            . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.' . $mimeMap[$mime];

        $file->move($directory, $filename, true);

        return 'uploads/branding/' . $filename;
    }

    public function hapusLama(?string $path): void
    {
        if ($path === null || $path === '' || ! str_starts_with($path, 'uploads/branding/')) {
            return;
        }

        $fullPath = ROOTPATH . $path;
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
