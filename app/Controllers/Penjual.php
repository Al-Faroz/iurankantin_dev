<?php

namespace App\Controllers;

use App\Models\GolonganPenjualModel;
use App\Models\PenjualModel;
use App\Services\LaporanService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Penjual extends BaseController
{
    private PenjualModel $model;
    private GolonganPenjualModel $golonganModel;
    private string $baseUrl;

    public function __construct()
    {
        $this->model = new PenjualModel();
        $this->golonganModel = new GolonganPenjualModel();
        $this->baseUrl = rtrim((string) config('App')->baseURL, '/');
    }

    public function index()
    {
        return view('penjual_index', [
            'title' => 'Master Penjual',
            'penjual' => $this->model->getWithGolongan(),
        ]);
    }

    public function export()
    {
        $rows = $this->model->getWithGolongan();
        $excelRows = [];

        foreach ($rows as $index => $row) {
            $excelRows[] = [
                $index + 1,
                $row['nama_penjual'],
                $row['nama_golongan'] ?? '',
                (float) ($row['nominal_iuran'] ?? 0),
                $row['alamat'] ?? '',
                $row['no_hp'] ?? '',
                $row['lokasi_lapak'] ?? '',
                $row['status_aktif'],
                date('d-m-Y', strtotime((string) $row['tanggal_daftar'])),
                $row['kode_kartu'] ?? '',
            ];
        }

        $binary = (new LaporanService())->excel(
            'Data Penjual',
            ['No', 'Nama Penjual', 'Golongan', 'Nominal Iuran', 'Alamat', 'No. HP', 'Lokasi / Lapak', 'Status', 'Tanggal Bergabung', 'Kode Kartu'],
            $excelRows
        );

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="data-penjual.xlsx"')
            ->setBody($binary);
    }

    public function create()
    {
        return view('penjual_form', [
            'title' => 'Tambah Penjual',
            'penjual' => null,
            'golongan' => $this->golonganModel->orderBy('nominal_iuran', 'DESC')->findAll(),
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $idGolongan = (int) $this->request->getPost('id_golongan');
        if ($this->golonganModel->find($idGolongan) === null) {
            return redirect()->back()->withInput()->with('error', 'Golongan penjual tidak valid.');
        }

        if ($this->model->insert($this->payload()) === false) {
            return redirect()->back()->withInput()->with('error', 'Data penjual gagal disimpan. Silakan coba kembali.');
        }

        return redirect()->to($this->baseUrl . '/penjual')->with('success', 'Data penjual berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $penjual = $this->model->find($id);
        if ($penjual === null) {
            throw PageNotFoundException::forPageNotFound('Penjual tidak ditemukan.');
        }

        return view('penjual_form', [
            'title' => 'Edit Penjual',
            'penjual' => $penjual,
            'golongan' => $this->golonganModel->orderBy('nominal_iuran', 'DESC')->findAll(),
        ]);
    }

    public function update(int $id)
    {
        if ($this->model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Penjual tidak ditemukan.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $idGolongan = (int) $this->request->getPost('id_golongan');
        if ($this->golonganModel->find($idGolongan) === null) {
            return redirect()->back()->withInput()->with('error', 'Golongan penjual tidak valid.');
        }

        if ($this->model->update($id, $this->payload()) === false) {
            return redirect()->back()->withInput()->with('error', 'Data penjual gagal diperbarui. Silakan coba kembali.');
        }

        return redirect()->to($this->baseUrl . '/penjual')->with('success', 'Data penjual berhasil diperbarui.');
    }

    public function show(int $id)
    {
        $penjual = $this->model
            ->select('penjual.*, golongan_penjual.nama_golongan, golongan_penjual.nominal_iuran')
            ->join('golongan_penjual', 'golongan_penjual.id_golongan = penjual.id_golongan', 'left')
            ->where('penjual.id_penjual', $id)
            ->first();

        if ($penjual === null) {
            throw PageNotFoundException::forPageNotFound('Penjual tidak ditemukan.');
        }

        $riwayat = db_connect()->table('transaksi_iuran')
            ->where('id_penjual', $id)
            ->orderBy('tanggal', 'DESC')
            ->orderBy('id_transaksi', 'DESC')
            ->limit(20)
            ->get()
            ->getResultArray();

        return view('penjual_detail', [
            'title' => 'Detail Penjual',
            'penjual' => $penjual,
            'riwayat' => $riwayat,
        ]);
    }

    public function arsipkan(int $id)
    {
        if ($this->model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Penjual tidak ditemukan.');
        }

        $now = date('Y-m-d H:i:s');
        $saved = db_connect()->table('penjual')
            ->where('id_penjual', $id)
            ->update([
                'status_aktif' => 'Nonaktif',
                'deleted_at' => $now,
                'updated_at' => $now,
            ]);

        if ($saved === false) {
            return redirect()->to($this->baseUrl . '/penjual')
                ->with('error', 'Penjual gagal diarsipkan. Silakan coba kembali.');
        }

        return redirect()->to($this->baseUrl . '/penjual')
            ->with('success', 'Penjual berhasil diarsipkan. Riwayat transaksi tetap tersimpan.');
    }

    private function payload(): array
    {
        return [
            'nama_penjual' => trim((string) $this->request->getPost('nama_penjual')),
            'id_golongan' => (int) $this->request->getPost('id_golongan'),
            'no_hp' => trim((string) $this->request->getPost('no_hp')) ?: null,
            'lokasi_lapak' => trim((string) $this->request->getPost('lokasi_lapak')) ?: null,
            'alamat' => trim((string) $this->request->getPost('alamat')) ?: null,
            'status_aktif' => (string) $this->request->getPost('status_aktif'),
            'tanggal_daftar' => (string) $this->request->getPost('tanggal_daftar'),
        ];
    }

    private function rules(): array
    {
        return [
            'nama_penjual' => 'required|max_length[150]',
            'id_golongan' => 'required|integer',
            'no_hp' => 'permit_empty|max_length[20]',
            'lokasi_lapak' => 'permit_empty|max_length[100]',
            'alamat' => 'permit_empty|max_length[255]',
            'status_aktif' => 'required|in_list[Aktif,Nonaktif]',
            'tanggal_daftar' => 'required|valid_date[Y-m-d]',
        ];
    }
}
