<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransaksiIuranTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_transaksi' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'id_penjual' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'tanggal' => ['type' => 'DATE'],
            'nominal' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'id_operator' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_transaksi', true);
        $this->forge->addKey('tanggal');
        $this->forge->addForeignKey('id_penjual', 'penjual', 'id_penjual', 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey('id_operator', 'users', 'id_user', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('transaksi_iuran');
    }

    public function down()
    {
        $this->forge->dropTable('transaksi_iuran', true);
    }
}
