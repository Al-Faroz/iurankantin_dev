<?php

namespace App\Controllers;

use App\Models\SetoranPimpinanModel;
use App\Models\TransaksiIuranModel;
use App\Models\TransaksiPengeluaranModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class KoreksiTransaksi extends BaseController
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('App')->baseURL, '/');
    }

    public function index()
    {
        $db = db_connect();

        return view('koreksi_transaksi_index', [
            'title' => 'Koreksi Transaksi',
            'iuran' => $db->table('transaksi_iuran')
                ->select('transaksi_iuran.*, penjual.nama_penjual, users.nama AS nama_operator')
                ->join('penjual', 'penjual.id_penjual = transaksi_iuran.id_penjual')
                ->join('users', 'users.id_user = transaksi_iuran.id_operator')
                ->orderBy('tanggal', 'DESC')
                ->orderBy('id_transaksi', 'DESC')
                ->limit(200)
                ->get()->getResultArray(),
            'pengeluaran' => $db->table('transaksi_pengeluaran')
                ->select('transaksi_pengeluaran.*, kategori_pengeluaran.nama_kategori, users.nama AS nama_operator')
                ->join('kategori_pengeluaran', 'kategori_pengeluaran.id_kategori_keluar = transaksi_pengeluaran.id_kategori_keluar')
                ->join('users', 'users.id_user = transaksi_pengeluaran.id_operator')
                ->orderBy('tanggal', 'DESC')
                ->orderBy('id_pengeluaran', 'DESC')
                ->limit(200)
                ->get()->getResultArray(),
            'setoran' => $db->table('setoran_pimpinan')
                ->select('setoran_pimpinan.*, users.nama AS nama_operator')
                ->join('users', 'users.id_user = setoran_pimpinan.id_operator')
                ->orderBy('tanggal_form', 'DESC')
                ->orderBy('id_setoran', 'DESC')
                ->limit(200)
                ->get()->getResultArray(),
        ]);
    }

    public function hapusIuran(int $id)
    {
        $model = new TransaksiIuranModel();
        if ($model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Transaksi iuran tidak ditemukan.');
        }

        $model->delete($id, true);

        return redirect()->to($this->baseUrl . '/koreksi-transaksi')->with('success', 'Transaksi iuran yang salah berhasil dihapus permanen.');
    }

    public function hapusPengeluaran(int $id)
    {
        $model = new TransaksiPengeluaranModel();
        $row = $model->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('Transaksi pengeluaran tidak ditemukan.');
        }

        $model->delete($id, true);

        $path = (string) ($row['bukti_nota'] ?? '');
        if ($path !== '' && str_starts_with($path, 'uploads/bukti_nota/')) {
            $fullPath = ROOTPATH . $path;
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        return redirect()->to($this->baseUrl . '/koreksi-transaksi')->with('success', 'Transaksi pengeluaran yang salah berhasil dihapus permanen.');
    }

    public function hapusSetoran(int $id)
    {
        $model = new SetoranPimpinanModel();
        if ($model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Transaksi setoran tidak ditemukan.');
        }

        $model->delete($id, true);

        return redirect()->to($this->baseUrl . '/koreksi-transaksi')->with('success', 'Transaksi setoran yang salah berhasil dihapus permanen.');
    }
}
