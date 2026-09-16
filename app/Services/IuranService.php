<?php

namespace App\Services;

use App\Models\TransaksiIuranModel;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;
use Throwable;

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

        $ids = array_values(array_unique(array_filter(
            array_map('intval', array_keys($bayar)),
            static fn (int $id): bool => $id > 0
        )));

        if ($ids === []) {
            throw new RuntimeException('Tidak ada data penjual yang valid untuk disimpan.');
        }

        $penjualAktif = $this->db->table('penjual')
            ->select('id_penjual')
            ->whereIn('id_penjual', $ids)
            ->where('status_aktif', 'Aktif')
            ->where('deleted_at', null)
            ->get()
            ->getResultArray();

        $idValid = array_map(static fn (array $row): int => (int) $row['id_penjual'], $penjualAktif);

        // Aturan bisnis: satu Penjual hanya boleh memiliki satu transaksi iuran
        // pada tanggal yang sama. Pemeriksaan aplikasi memberi pesan yang ramah,
        // sedangkan unique index database menjadi perlindungan terakhir terhadap race condition.
        $duplikat = $this->db->table('transaksi_iuran')
            ->select('transaksi_iuran.id_penjual, penjual.nama_penjual')
            ->join('penjual', 'penjual.id_penjual = transaksi_iuran.id_penjual')
            ->where('transaksi_iuran.tanggal', $tanggal)
            ->whereIn('transaksi_iuran.id_penjual', $ids)
            ->orderBy('penjual.nama_penjual', 'ASC')
            ->get()
            ->getResultArray();

        if ($duplikat !== []) {
            $nama = array_map(
                static fn (array $row): string => (string) $row['nama_penjual'],
                array_slice($duplikat, 0, 5)
            );
            $tambahan = count($duplikat) > 5 ? ' dan ' . (count($duplikat) - 5) . ' penjual lainnya' : '';

            throw new RuntimeException(
                'Iuran tanggal ' . date('d-m-Y', strtotime($tanggal))
                . ' sudah tercatat untuk: ' . implode(', ', $nama) . $tambahan
                . '. Hapus transaksi lama melalui Koreksi Transaksi jika memang salah input.'
            );
        }

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

        $this->db->transBegin();

        try {
            if ($this->model->insertBatch($rows) === false || ! $this->db->transStatus()) {
                throw new RuntimeException('Penyimpanan iuran gagal. Silakan coba kembali.');
            }

            $this->db->transCommit();
        } catch (Throwable $e) {
            $this->db->transRollback();

            if ($e instanceof RuntimeException) {
                throw $e;
            }

            // Unique index dapat menangkap request bersamaan yang lolos pemeriksaan awal.
            throw new RuntimeException(
                'Penyimpanan iuran gagal. Pastikan Penjual yang dipilih belum memiliki iuran pada tanggal tersebut.'
            );
        }

        return count($rows);
    }
}
