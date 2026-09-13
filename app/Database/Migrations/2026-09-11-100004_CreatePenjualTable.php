<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePenjualTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_penjual' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_penjual' => ['type' => 'VARCHAR', 'constraint' => 150],
            'id_golongan' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'no_hp' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'lokasi_lapak' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status_aktif' => ['type' => 'ENUM', 'constraint' => ['Aktif', 'Nonaktif'], 'default' => 'Aktif'],
            'tanggal_daftar' => ['type' => 'DATE'],
            'kode_kartu' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'kode_verifikasi' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_penjual', true);
        $this->forge->addUniqueKey('kode_kartu');
        $this->forge->addUniqueKey('kode_verifikasi');
        $this->forge->addForeignKey('id_golongan', 'golongan_penjual', 'id_golongan', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('penjual');
    }

    public function down()
    {
        $this->forge->dropTable('penjual', true);
    }
}
