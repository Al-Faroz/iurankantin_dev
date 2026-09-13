<?php

namespace App\Models;

use CodeIgniter\Model;

class GolonganPenjualModel extends Model
{
    protected $table = 'golongan_penjual';
    protected $primaryKey = 'id_golongan';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = true;
    protected $protectFields = true;
    protected $allowedFields = ['nama_golongan', 'nominal_iuran'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';
}
