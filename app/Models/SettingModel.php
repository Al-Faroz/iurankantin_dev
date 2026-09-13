<?php

namespace App\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table = 'setting';
    protected $primaryKey = 'id_setting';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'nama_madrasah',
        'alamat_madrasah',
        'logo',
        'background_kartu_depan',
        'background_kartu_belakang',
        'updated_at',
    ];

    protected $useTimestamps = false;

    public function getCurrent(): array
    {
        return $this->find(1) ?? [
            'id_setting' => 1,
            'nama_madrasah' => 'MTsN 4 Jombang',
            'alamat_madrasah' => null,
            'logo' => null,
            'background_kartu_depan' => null,
            'background_kartu_belakang' => null,
        ];
    }
}
