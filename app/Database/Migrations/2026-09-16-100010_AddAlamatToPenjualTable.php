<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAlamatToPenjualTable extends Migration
{
    public function up()
    {
        $this->forge->addColumn('penjual', [
            'alamat' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('penjual', 'alamat');
    }
}
