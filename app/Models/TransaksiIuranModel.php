<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiIuranModel extends Model
{
    protected $table = 'transaksi_iuran';
    protected $primaryKey = 'id_transaksi';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'id_penjual',
        'tanggal',
        'nominal',
        'keterangan',
        'id_operator',
        'created_at',
    ];

    protected $useTimestamps = false;
}
