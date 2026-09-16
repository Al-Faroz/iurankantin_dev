<?php

namespace App\Controllers;

use App\Models\KategoriPengeluaranModel;
use App\Models\TransaksiPengeluaranModel;
use App\Services\BuktiNotaService;
use App\Services\BuktiTransaksiStorageService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\I18n\Time;
use RuntimeException;

class Pengeluaran extends BaseController
{
    private TransaksiPengeluaranModel $model;
    private KategoriPengeluaranModel $kategoriModel;
    private string $baseUrl;

    public function __construct()
    {
        $this->model = new TransaksiPengeluaranModel();
        $this->kategoriModel = new KategoriPengeluaranModel();
        $this->baseUrl = rtrim((string) config('App')->baseURL, '/');
    }

    public function index()
    {
        $rows = $this->model
            ->select('transaksi_pengeluaran.*, kategori_pengeluaran.nama_kategori, users.nama AS nama_operator')
            ->join('kategori_pengeluaran', 'kategori_pengeluaran.id_kategori_keluar = transaksi_pengeluaran.id_kategori_keluar')
            ->join('users', 'users.id_user = transaksi_pengeluaran.id_operator')
            ->orderBy('tanggal', 'DESC')
            ->orderBy('id_pengeluaran', 'DESC')
            ->findAll();

        return view('pengeluaran_index', [
            'title' => 'Pengeluaran',
            'pengeluaran' => $rows,
        ]);
    }

    public function create()
    {
        return view('pengeluaran_form', [
            'title' => 'Input Pengeluaran',
            'tanggalDefault' => Time::now('Asia/Jakarta')->toDateString(),
            'kategori' => $this->kategoriModel->orderBy('nama_kategori', 'ASC')->findAll(),
        ]);
    }

    public function store()
    {
        $rules = [
            'tanggal' => 'required|valid_date[Y-m-d]',
            'id_kategori_keluar' => 'required|integer',
            'nominal' => 'required|numeric|greater_than[0]',
            'keterangan' => 'permit_empty|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $idKategori = (int) $this->request->getPost('id_kategori_keluar');
        if ($this->kategoriModel->find($idKategori) === null) {
            return redirect()->back()->withInput()->with('error', 'Kategori pengeluaran tidak valid.');
        }

        $buktiPath = null;
        $file = $this->request->getFile('bukti_nota');

        try {
            if ($file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE) {
                $buktiPath = (new BuktiNotaService())->simpan($file);
            }

            $saved = $this->model->insert([
                'tanggal' => (string) $this->request->getPost('tanggal'),
                'id_kategori_keluar' => $idKategori,
                'nominal' => (float) $this->request->getPost('nominal'),
                'keterangan' => trim((string) $this->request->getPost('keterangan')) ?: null,
                'bukti_nota' => $buktiPath,
                'id_operator' => (int) session()->get('id_user'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if ($saved === false) {
                throw new RuntimeException('Pengeluaran gagal disimpan ke database. Silakan coba kembali.');
            }
        } catch (RuntimeException $e) {
            if ($buktiPath !== null) {
                (new BuktiTransaksiStorageService())->hapusNota($buktiPath);
            }

            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to($this->baseUrl . '/pengeluaran')->with('success', 'Pengeluaran berhasil disimpan.');
    }

    public function bukti(int $id)
    {
        $row = $this->model->find($id);
        if ($row === null) {
            throw PageNotFoundException::forPageNotFound('Transaksi pengeluaran tidak ditemukan.');
        }

        $fullPath = (new BuktiTransaksiStorageService())->resolveNota($row['bukti_nota'] ?? null);
        if ($fullPath === null) {
            throw PageNotFoundException::forPageNotFound('Bukti nota tidak ditemukan.');
        }

        return $this->imageResponse($fullPath);
    }

    private function imageResponse(string $fullPath)
    {
        $content = file_get_contents($fullPath);
        if ($content === false) {
            throw PageNotFoundException::forPageNotFound('Bukti nota tidak dapat dibaca.');
        }

        $mime = mime_content_type($fullPath) ?: 'image/jpeg';
        $filename = basename($fullPath);

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'private, no-store, max-age=0')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setBody($content);
    }
}
