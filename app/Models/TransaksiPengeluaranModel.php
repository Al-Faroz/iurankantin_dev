<?php

namespace App\Models;

use CodeIgniter\Model;

class TransaksiPengeluaranModel extends Model
{
    protected $table = 'transaksi_pengeluaran';
    protected $primaryKey = 'id_pengeluaran';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $protectFields = true;
    protected $allowedFields = [
        'tanggal',
        'id_kategori_keluar',
        'nominal',
        'keterangan',
        'bukti_nota',
        'id_operator',
        'created_at',
    ];

    protected $useTimestamps = false;
}
