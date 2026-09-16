<?php

namespace App\Models;

use CodeIgniter\Model;

class SetoranPimpinanModel extends Model
{
    protected $table = 'setoran_pimpinan';
    protected $primaryKey = 'id_setoran';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'tanggal_form',
        'periode_awal',
        'periode_akhir',
        'nominal',
        'keterangan',
        'bukti_setoran',
        'id_operator',
        'created_at',
    ];

    protected $useTimestamps = false;
}
