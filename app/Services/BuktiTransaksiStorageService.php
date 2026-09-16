<?php

namespace App\Services;

class BuktiTransaksiStorageService
{
    /** @var list<string> */
    private const NOTA_PREFIXES = [
        'writable/uploads/bukti_nota/',
        'uploads/bukti_nota/',
    ];

    /** @var list<string> */
    private const SETORAN_PREFIXES = [
        'writable/uploads/bukti_setoran/',
        'uploads/bukti_setoran/',
    ];

    public function resolveNota(?string $relativePath): ?string
    {
        return $this->resolve($relativePath, self::NOTA_PREFIXES);
    }

    public function resolveSetoran(?string $relativePath): ?string
    {
        return $this->resolve($relativePath, self::SETORAN_PREFIXES);
    }

    public function hapusNota(?string $relativePath): void
    {
        $this->hapus($this->resolveNota($relativePath));
    }

    public function hapusSetoran(?string $relativePath): void
    {
        $this->hapus($this->resolveSetoran($relativePath));
    }

    /**
     * @param list<string> $allowedPrefixes
     */
    private function resolve(?string $relativePath, array $allowedPrefixes): ?string
    {
        $relativePath = ltrim(trim((string) $relativePath), '/');
        if ($relativePath === '') {
            return null;
        }

        foreach ($allowedPrefixes as $prefix) {
            if (! str_starts_with($relativePath, $prefix)) {
                continue;
            }

            $filename = substr($relativePath, strlen($prefix));

            // Path database seharusnya hanya berisi satu nama file hasil generate server.
            // Tolak separator/path traversal walaupun nilai database berubah tidak semestinya.
            if ($filename === '' || basename($filename) !== $filename || str_contains($filename, '..')) {
                return null;
            }

            $baseDirectory = ROOTPATH . $prefix;
            $candidate = $baseDirectory . $filename;

            if (! is_file($candidate)) {
                return null;
            }

            return $candidate;
        }

        return null;
    }

    private function hapus(?string $fullPath): void
    {
        if ($fullPath !== null && is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
