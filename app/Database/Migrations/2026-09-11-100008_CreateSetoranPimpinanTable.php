<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSetoranPimpinanTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_setoran' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'tanggal_form' => ['type' => 'DATE'],
            'periode_awal' => ['type' => 'DATE'],
            'periode_akhir' => ['type' => 'DATE'],
            'nominal' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'keterangan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'id_operator' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_setoran', true);
        $this->forge->addKey('tanggal_form');
        $this->forge->addForeignKey('id_operator', 'users', 'id_user', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('setoran_pimpinan');
    }

    public function down()
    {
        $this->forge->dropTable('setoran_pimpinan', true);
    }
}
