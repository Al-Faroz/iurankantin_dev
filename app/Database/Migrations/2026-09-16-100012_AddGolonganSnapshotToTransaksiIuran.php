<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddGolonganSnapshotToTransaksiIuran extends Migration
{
    private const INDEX_NAME = 'idx_iuran_golongan_snapshot';

    public function up()
    {
        $fields = [];

        if (! $this->db->fieldExists('id_golongan_snapshot', 'transaksi_iuran')) {
            $fields['id_golongan_snapshot'] = [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'id_penjual',
            ];
        }

        if (! $this->db->fieldExists('nama_golongan_snapshot', 'transaksi_iuran')) {
            $fields['nama_golongan_snapshot'] = [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'id_golongan_snapshot',
            ];
        }

        if (! $this->db->fieldExists('nominal_golongan_snapshot', 'transaksi_iuran')) {
            $fields['nominal_golongan_snapshot'] = [
                'type' => 'DECIMAL',
                'constraint' => '12,2',
                'null' => true,
                'after' => 'nama_golongan_snapshot',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('transaksi_iuran', $fields);
        }

        // Data lama tidak mempunyai snapshot. Nilai yang paling aman untuk backfill adalah
        // kondisi Golongan Penjual pada saat migration dijalankan. Setelah fitur aktif,
        // transaksi baru selalu menyimpan snapshot saat input.
        $this->db->query(
            'UPDATE transaksi_iuran AS ti
             INNER JOIN penjual AS p ON p.id_penjual = ti.id_penjual
             LEFT JOIN golongan_penjual AS g ON g.id_golongan = p.id_golongan
             SET ti.id_golongan_snapshot = COALESCE(ti.id_golongan_snapshot, p.id_golongan),
                 ti.nama_golongan_snapshot = COALESCE(ti.nama_golongan_snapshot, g.nama_golongan),
                 ti.nominal_golongan_snapshot = COALESCE(ti.nominal_golongan_snapshot, g.nominal_iuran)
             WHERE ti.id_golongan_snapshot IS NULL
                OR ti.nama_golongan_snapshot IS NULL
                OR ti.nominal_golongan_snapshot IS NULL'
        );

        if (! $this->indexExists()) {
            $this->db->query(
                'ALTER TABLE transaksi_iuran ADD INDEX ' . self::INDEX_NAME . ' (id_golongan_snapshot)'
            );
        }
    }

    public function down()
    {
        if ($this->indexExists()) {
            $this->db->query(
                'ALTER TABLE transaksi_iuran DROP INDEX ' . self::INDEX_NAME
            );
        }

        foreach (['nominal_golongan_snapshot', 'nama_golongan_snapshot', 'id_golongan_snapshot'] as $field) {
            if ($this->db->fieldExists($field, 'transaksi_iuran')) {
                $this->forge->dropColumn('transaksi_iuran', $field);
            }
        }
    }

    private function indexExists(): bool
    {
        $indexes = $this->db->getIndexData('transaksi_iuran');

        return array_key_exists(self::INDEX_NAME, $indexes);
    }
}
