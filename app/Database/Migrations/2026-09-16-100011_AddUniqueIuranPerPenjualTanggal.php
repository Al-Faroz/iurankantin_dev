<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class AddUniqueIuranPerPenjualTanggal extends Migration
{
    private const INDEX_NAME = 'uniq_iuran_penjual_tanggal';

    public function up()
    {
        // Jangan menambahkan unique index sebelum data historis dipastikan bersih.
        // Migration dihentikan dengan pesan jelas agar duplikasi dapat dikoreksi lebih dulu.
        $duplicate = $this->db->query(
            'SELECT id_penjual, tanggal, COUNT(*) AS jumlah
             FROM transaksi_iuran
             GROUP BY id_penjual, tanggal
             HAVING COUNT(*) > 1
             LIMIT 1'
        )->getRowArray();

        if ($duplicate !== null) {
            throw new RuntimeException(
                'Unique index iuran belum dapat dibuat karena ditemukan transaksi ganda untuk Penjual ID '
                . (int) $duplicate['id_penjual'] . ' pada tanggal ' . (string) $duplicate['tanggal']
                . '. Koreksi duplikasi historis terlebih dahulu.'
            );
        }

        if ($this->indexExists()) {
            return;
        }

        $this->db->query(
            'ALTER TABLE transaksi_iuran '
            . 'ADD UNIQUE KEY ' . self::INDEX_NAME . ' (id_penjual, tanggal)'
        );
    }

    public function down()
    {
        if (! $this->indexExists()) {
            return;
        }

        $this->db->query(
            'ALTER TABLE transaksi_iuran DROP INDEX ' . self::INDEX_NAME
        );
    }

    private function indexExists(): bool
    {
        $indexes = $this->db->getIndexData('transaksi_iuran');

        return array_key_exists(self::INDEX_NAME, $indexes);
    }
}
