<?php

namespace App\Controllers;

class Verifikasi extends BaseController
{
    public function kartu(string $token)
    {
        $baseUrl = rtrim((string) config('App')->baseURL, '/');
        $penjual = null;

        if (preg_match('/^[a-f0-9]{64}$/i', $token) === 1) {
            $penjual = db_connect()->table('penjual')
                ->select('penjual.id_penjual, penjual.nama_penjual, penjual.status_aktif, golongan_penjual.nama_golongan')
                ->join('golongan_penjual', 'golongan_penjual.id_golongan = penjual.id_golongan', 'left')
                ->where('penjual.kode_verifikasi', $token)
                ->where('penjual.deleted_at', null)
                ->get()
                ->getRowArray();
        }

        if ($penjual !== null && session()->get('is_logged_in') && session()->get('role') === 'Operator') {
            return redirect()->to($baseUrl . '/penjual/' . (int) $penjual['id_penjual']);
        }

        return view('verifikasi_publik', [
            'title' => 'Verifikasi Kartu Anggota Kantin',
            'baseUrl' => $baseUrl,
            'penjual' => $penjual,
        ]);
    }
}
