<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_setting' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_madrasah' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'alamat_madrasah' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'logo' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'background_kartu_depan' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'background_kartu_belakang' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_setting', true);
        $this->forge->createTable('setting');
    }

    public function down()
    {
        $this->forge->dropTable('setting', true);
    }
}
