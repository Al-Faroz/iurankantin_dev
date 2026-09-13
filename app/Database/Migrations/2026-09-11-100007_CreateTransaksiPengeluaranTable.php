<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransaksiPengeluaranTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_pengeluaran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tanggal' => ['type' => 'DATE'],
            'id_kategori_keluar' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'nominal' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'bukti_nota' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'id_operator' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_pengeluaran', true);
        $this->forge->addKey('tanggal');
        $this->forge->addForeignKey('id_kategori_keluar', 'kategori_pengeluaran', 'id_kategori_keluar', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('id_operator', 'users', 'id_user', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('transaksi_pengeluaran');
    }

    public function down()
    {
        $this->forge->dropTable('transaksi_pengeluaran', true);
    }
}
