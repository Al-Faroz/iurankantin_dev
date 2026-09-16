<?php

namespace App\Controllers;

use App\Models\SetoranPimpinanModel;
use App\Models\TransaksiIuranModel;
use App\Models\TransaksiPengeluaranModel;
use App\Services\BuktiTransaksiStorageService;
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
        $filter = $this->filterPeriode();

        $iuran = $db->table('transaksi_iuran')
            ->select(
                'transaksi_iuran.*, penjual.nama_penjual, penjual.lokasi_lapak, '
                . 'COALESCE(transaksi_iuran.nama_golongan_snapshot, golongan_penjual.nama_golongan) AS nama_golongan, '
                . 'COALESCE(transaksi_iuran.nominal_golongan_snapshot, golongan_penjual.nominal_iuran) AS nominal_iuran, '
                . 'users.nama AS nama_operator',
                false
            )
            ->join('penjual', 'penjual.id_penjual = transaksi_iuran.id_penjual')
            ->join('golongan_penjual', 'golongan_penjual.id_golongan = penjual.id_golongan', 'left')
            ->join('users', 'users.id_user = transaksi_iuran.id_operator')
            ->where('transaksi_iuran.tanggal >=', $filter['tanggal_awal'])
            ->where('transaksi_iuran.tanggal <=', $filter['tanggal_akhir'])
            ->orderBy('transaksi_iuran.tanggal', 'DESC')
            ->orderBy('nominal_iuran', 'DESC')
            ->orderBy('penjual.nama_penjual', 'ASC')
            ->get()->getResultArray();

        $pengeluaran = $db->table('transaksi_pengeluaran')
            ->select('transaksi_pengeluaran.*, kategori_pengeluaran.nama_kategori, users.nama AS nama_operator')
            ->join('kategori_pengeluaran', 'kategori_pengeluaran.id_kategori_keluar = transaksi_pengeluaran.id_kategori_keluar')
            ->join('users', 'users.id_user = transaksi_pengeluaran.id_operator')
            ->where('transaksi_pengeluaran.tanggal >=', $filter['tanggal_awal'])
            ->where('transaksi_pengeluaran.tanggal <=', $filter['tanggal_akhir'])
            ->orderBy('tanggal', 'DESC')
            ->orderBy('id_pengeluaran', 'DESC')
            ->get()->getResultArray();

        $setoran = $db->table('setoran_pimpinan')
            ->select('setoran_pimpinan.*, users.nama AS nama_operator')
            ->join('users', 'users.id_user = setoran_pimpinan.id_operator')
            ->where('setoran_pimpinan.tanggal_form >=', $filter['tanggal_awal'])
            ->where('setoran_pimpinan.tanggal_form <=', $filter['tanggal_akhir'])
            ->orderBy('tanggal_form', 'DESC')
            ->orderBy('id_setoran', 'DESC')
            ->get()->getResultArray();

        return view('koreksi_transaksi_index', [
            'title' => 'Koreksi Transaksi',
            'filter' => $filter,
            'iuran' => $iuran,
            'pengeluaran' => $pengeluaran,
            'setoran' => $setoran,
        ]);
    }

    public function hapusIuran(int $id)
    {
        $model = new TransaksiIuranModel();
        if ($model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Transaksi iuran tidak ditemukan.');
        }

        if ($model->delete($id, true) === false) {
            return redirect()->to($this->returnUrl())
                ->with('error', 'Transaksi iuran gagal dihapus. Silakan coba kembali.');
        }

        return redirect()->to($this->returnUrl())->with('success', 'Transaksi iuran yang salah berhasil dihapus permanen.');
    }

    public function hapusPengeluaran(int $id)
    {
        $model = new TransaksiPengeluaranModel();
        $row = $model->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('Transaksi pengeluaran tidak ditemukan.');
        }

        if ($model->delete($id, true) === false) {
            return redirect()->to($this->returnUrl())
                ->with('error', 'Transaksi pengeluaran gagal dihapus. Silakan coba kembali.');
        }

        (new BuktiTransaksiStorageService())->hapusNota($row['bukti_nota'] ?? null);

        return redirect()->to($this->returnUrl())->with('success', 'Transaksi pengeluaran yang salah berhasil dihapus permanen.');
    }

    public function hapusSetoran(int $id)
    {
        $model = new SetoranPimpinanModel();
        $row = $model->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('Transaksi setoran tidak ditemukan.');
        }

        if ($model->delete($id, true) === false) {
            return redirect()->to($this->returnUrl())
                ->with('error', 'Transaksi setoran gagal dihapus. Silakan coba kembali.');
        }

        (new BuktiTransaksiStorageService())->hapusSetoran($row['bukti_setoran'] ?? null);

        return redirect()->to($this->returnUrl())->with('success', 'Transaksi setoran yang salah berhasil dihapus permanen.');
    }

    private function filterPeriode(): array
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta'));
        $awalDefault = $now->modify('first day of this month')->format('Y-m-d');
        $akhirDefault = $now->format('Y-m-d');

        $awal = (string) ($this->request->getGet('tanggal_awal') ?: $awalDefault);
        $akhir = (string) ($this->request->getGet('tanggal_akhir') ?: $akhirDefault);

        if (! $this->isValidDate($awal)) {
            $awal = $awalDefault;
        }
        if (! $this->isValidDate($akhir)) {
            $akhir = $akhirDefault;
        }
        if ($awal > $akhir) {
            [$awal, $akhir] = [$akhir, $awal];
        }

        return ['tanggal_awal' => $awal, 'tanggal_akhir' => $akhir];
    }

    private function returnUrl(): string
    {
        $awal = (string) $this->request->getPost('tanggal_awal');
        $akhir = (string) $this->request->getPost('tanggal_akhir');

        if (! $this->isValidDate($awal) || ! $this->isValidDate($akhir)) {
            return $this->baseUrl . '/koreksi-transaksi';
        }

        return $this->baseUrl . '/koreksi-transaksi?' . http_build_query([
            'tanggal_awal' => $awal,
            'tanggal_akhir' => $akhir,
        ]);
    }

    private function isValidDate(string $value): bool
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value, new \DateTimeZone('Asia/Jakarta'));

        return $date !== false && $date->format('Y-m-d') === $value;
    }
}
