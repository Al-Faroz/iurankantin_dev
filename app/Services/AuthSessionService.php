<?php

namespace App\Services;

class AuthSessionService
{
    /**
     * Memastikan sesi login masih sesuai dengan akun yang aktif di database.
     * Perubahan nama/username/role disinkronkan tanpa menunggu sesi berakhir.
     */
    public function revalidate(): ?array
    {
        if (! session()->get('is_logged_in')) {
            return null;
        }

        $idUser = (int) session()->get('id_user');
        if ($idUser <= 0) {
            return null;
        }

        $user = db_connect()->table('users')
            ->select('id_user, nama, username, role, status_aktif')
            ->where('id_user', $idUser)
            ->where('status_aktif', 'Aktif')
            ->get()
            ->getRowArray();

        if ($user === null) {
            return null;
        }

        session()->set([
            'id_user' => (int) $user['id_user'],
            'nama' => (string) $user['nama'],
            'username' => (string) $user['username'],
            'role' => (string) $user['role'],
            'is_logged_in' => true,
        ]);

        return $user;
    }
}
