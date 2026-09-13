<?php

namespace App\Controllers;

use App\Services\IuranService;
use CodeIgniter\I18n\Time;
use RuntimeException;

class Iuran extends BaseController
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('App')->baseURL, '/');
    }

    public function index()
    {
        $penjual = db_connect()->table('penjual')
            ->select('penjual.id_penjual, penjual.nama_penjual, penjual.lokasi_lapak, golongan_penjual.nama_golongan, golongan_penjual.nominal_iuran')
            ->join('golongan_penjual', 'golongan_penjual.id_golongan = penjual.id_golongan')
            ->where('penjual.status_aktif', 'Aktif')
            ->where('penjual.deleted_at', null)
            ->where('golongan_penjual.deleted_at', null)
            ->orderBy('golongan_penjual.nominal_iuran', 'DESC')
            ->orderBy('penjual.nama_penjual', 'ASC')
            ->get()
            ->getResultArray();

        return view('iuran_bulk', [
            'title' => 'Input Iuran Harian',
            'tanggalDefault' => Time::now('Asia/Jakarta')->toDateString(),
            'penjual' => $penjual,
        ]);
    }

    public function store()
    {
        if (! $this->validate(['tanggal' => 'required|valid_date[Y-m-d]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $bayar = $this->request->getPost('bayar');
        $nominal = $this->request->getPost('nominal');
        $keterangan = $this->request->getPost('keterangan');

        try {
            $jumlah = (new IuranService())->simpanBulk(
                (string) $this->request->getPost('tanggal'),
                (int) session()->get('id_user'),
                is_array($bayar) ? $bayar : [],
                is_array($nominal) ? $nominal : [],
                is_array($keterangan) ? $keterangan : []
            );
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to($this->baseUrl . '/iuran')->with('success', $jumlah . ' transaksi iuran berhasil disimpan.');
    }
}
