<?php

namespace App\Services;

use App\Models\TransaksiIuranModel;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class IuranService
{
    private BaseConnection $db;
    private TransaksiIuranModel $model;

    public function __construct()
    {
        $this->db = db_connect();
        $this->model = new TransaksiIuranModel();
    }

    public function simpanBulk(string $tanggal, int $idOperator, array $bayar, array $nominal, array $keterangan = []): int
    {
        if ($bayar === []) {
            throw new RuntimeException('Pilih minimal satu penjual yang membayar iuran.');
        }

        $ids = array_map('intval', array_keys($bayar));
        $penjualAktif = $this->db->table('penjual')
            ->select('id_penjual')
            ->whereIn('id_penjual', $ids)
            ->where('status_aktif', 'Aktif')
            ->where('deleted_at', null)
            ->get()
            ->getResultArray();

        $idValid = array_map(static fn (array $row): int => (int) $row['id_penjual'], $penjualAktif);
        $rows = [];
        $createdAt = date('Y-m-d H:i:s');

        foreach ($ids as $idPenjual) {
            if (! in_array($idPenjual, $idValid, true)) {
                continue;
            }

            $nilai = isset($nominal[$idPenjual]) ? (float) $nominal[$idPenjual] : 0;
            if ($nilai <= 0) {
                throw new RuntimeException('Nominal iuran yang dipilih harus lebih dari nol.');
            }

            $catatan = trim((string) ($keterangan[$idPenjual] ?? ''));
            $rows[] = [
                'id_penjual' => $idPenjual,
                'tanggal' => $tanggal,
                'nominal' => $nilai,
                'keterangan' => $catatan !== '' ? $catatan : null,
                'id_operator' => $idOperator,
                'created_at' => $createdAt,
            ];
        }

        if ($rows === []) {
            throw new RuntimeException('Tidak ada data iuran valid untuk disimpan.');
        }

        $this->db->transStart();
        $this->model->insertBatch($rows);
        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Penyimpanan iuran gagal. Silakan coba kembali.');
        }

        return count($rows);
    }
}
