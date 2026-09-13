<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use RuntimeException;

class IuranKantinSeeder extends Seeder
{
    public function run()
    {
        $operatorPassword = (string) env('seed.operatorPassword');
        $pimpinanPassword = (string) env('seed.pimpinanPassword');

        if ($operatorPassword === '' || $pimpinanPassword === '') {
            throw new RuntimeException('Set seed.operatorPassword dan seed.pimpinanPassword di .env sebelum menjalankan seeder.');
        }

        $now = date('Y-m-d H:i:s');

        $this->db->table('golongan_penjual')->insertBatch([
            ['id_golongan' => 1, 'nama_golongan' => 'Golongan 1', 'nominal_iuran' => 10000, 'created_at' => $now, 'updated_at' => $now],
            ['id_golongan' => 2, 'nama_golongan' => 'Golongan 2', 'nominal_iuran' => 7500, 'created_at' => $now, 'updated_at' => $now],
            ['id_golongan' => 3, 'nama_golongan' => 'Golongan 3', 'nominal_iuran' => 5000, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $this->db->table('setting')->insert([
            'id_setting' => 1,
            'nama_madrasah' => 'MTsN 4 Jombang',
            'updated_at' => $now,
        ]);

        $this->db->table('users')->insertBatch([
            [
                'id_user' => 1,
                'nama' => 'Operator Kantin',
                'username' => 'operator',
                'password' => password_hash($operatorPassword, PASSWORD_DEFAULT),
                'role' => 'Operator',
                'status_aktif' => 'Aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id_user' => 2,
                'nama' => 'Pimpinan Madrasah',
                'username' => 'pimpinan',
                'password' => password_hash($pimpinanPassword, PASSWORD_DEFAULT),
                'role' => 'Pimpinan',
                'status_aktif' => 'Aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
