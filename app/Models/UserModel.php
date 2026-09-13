<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id_user';
    protected $returnType = 'array';
    protected $useAutoIncrement = true;
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'nama',
        'username',
        'password',
        'role',
        'status_aktif',
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function findActiveByUsername(string $username): ?array
    {
        $user = $this->where('username', $username)
            ->where('status_aktif', 'Aktif')
            ->first();

        return $user ?: null;
    }
}
