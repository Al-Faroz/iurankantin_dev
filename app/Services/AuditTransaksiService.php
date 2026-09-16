<?php

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;
use Throwable;

class AuditTransaksiService
{
    private string $directory;

    public function __construct(?string $directory = null)
    {
        $this->directory = rtrim($directory ?? WRITEPATH . 'logs', DIRECTORY_SEPARATOR);
    }

    public function catat(string $aksi, string $jenis, string|int|null $idTransaksi, array $data = []): void
    {
        try {
            if (! is_dir($this->directory) && ! mkdir($this->directory, 0755, true) && ! is_dir($this->directory)) {
                throw new \RuntimeException('Folder audit transaksi tidak dapat dibuat.');
            }

            $now = new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
            $session = session();
            $request = service('request');

            $entry = [
                'waktu' => $now->format('Y-m-d H:i:s'),
                'aksi' => strtoupper($aksi),
                'jenis' => strtolower($jenis),
                'id_transaksi' => $idTransaksi,
                'operator' => [
                    'id_user' => (int) ($session->get('id_user') ?? 0),
                    'nama' => (string) ($session->get('nama') ?? ''),
                    'username' => (string) ($session->get('username') ?? ''),
                ],
                'ip' => method_exists($request, 'getIPAddress') ? $request->getIPAddress() : null,
                'data' => $data,
            ];

            $json = json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
            if ($json === false) {
                throw new \RuntimeException('Data audit transaksi tidak dapat diubah menjadi JSON.');
            }

            $file = $this->directory . DIRECTORY_SEPARATOR . 'audit-transaksi-' . $now->format('Y-m') . '.log';
            if (@file_put_contents($file, $json . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
                throw new \RuntimeException('File audit transaksi tidak dapat ditulis.');
            }
        } catch (Throwable $e) {
            log_message('error', 'Audit transaksi gagal ditulis: {message}', ['message' => $e->getMessage()]);
        }
    }
}
