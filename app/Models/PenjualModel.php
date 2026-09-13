<?php

namespace App\Models;

use CodeIgniter\Model;

class PenjualModel extends Model
{
    protected $table = 'penjual';
    protected $primaryKey = 'id_penjual';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'nama_penjual',
        'id_golongan',
        'no_hp',
        'lokasi_lapak',
        'status_aktif',
        'tanggal_daftar',
        'kode_kartu',
        'kode_verifikasi',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    public function getWithGolongan(): array
    {
        return $this->select('penjual.*, golongan_penjual.nama_golongan, golongan_penjual.nominal_iuran')
            ->join('golongan_penjual', 'golongan_penjual.id_golongan = penjual.id_golongan', 'left')
            ->orderBy('golongan_penjual.nominal_iuran', 'DESC')
            ->orderBy('penjual.nama_penjual', 'ASC')
            ->findAll();
    }
}
