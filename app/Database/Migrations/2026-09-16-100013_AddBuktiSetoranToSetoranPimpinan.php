<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBuktiSetoranToSetoranPimpinan extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('bukti_setoran', 'setoran_pimpinan')) {
            return;
        }

        $this->forge->addColumn('setoran_pimpinan', [
            'bukti_setoran' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'keterangan',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('bukti_setoran', 'setoran_pimpinan')) {
            $this->forge->dropColumn('setoran_pimpinan', 'bukti_setoran');
        }
    }
}
