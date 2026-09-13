<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateKategoriPengeluaranTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_kategori_keluar' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'nama_kategori' => ['type' => 'VARCHAR', 'constraint' => 100],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id_kategori_keluar', true);
        $this->forge->createTable('kategori_pengeluaran');
    }

    public function down()
    {
        $this->forge->dropTable('kategori_pengeluaran', true);
    }
}
