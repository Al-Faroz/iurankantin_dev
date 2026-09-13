<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateGolonganPenjualTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_golongan' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_golongan' => ['type' => 'VARCHAR', 'constraint' => 50],
            'nominal_iuran' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_golongan', true);
        $this->forge->createTable('golongan_penjual');
    }

    public function down()
    {
        $this->forge->dropTable('golongan_penjual', true);
    }
}
