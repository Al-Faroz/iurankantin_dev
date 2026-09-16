<?php

namespace App\Controllers;

use App\Models\KategoriPengeluaranModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class KategoriPengeluaran extends BaseController
{
    private KategoriPengeluaranModel $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->model = new KategoriPengeluaranModel();
        $this->baseUrl = rtrim((string) config('App')->baseURL, '/');
    }

    public function index()
    {
        return view('kategori_pengeluaran_index', [
            'title' => 'Master Kategori Pengeluaran',
            'kategori' => $this->model->orderBy('nama_kategori', 'ASC')->findAll(),
        ]);
    }

    public function create()
    {
        return view('kategori_pengeluaran_form', [
            'title' => 'Tambah Kategori Pengeluaran',
            'kategori' => null,
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $saved = $this->model->insert([
            'nama_kategori' => trim((string) $this->request->getPost('nama_kategori')),
        ]);

        if ($saved === false) {
            return redirect()->back()->withInput()->with('error', 'Kategori pengeluaran gagal disimpan. Silakan coba kembali.');
        }

        return redirect()->to($this->baseUrl . '/kategori-pengeluaran')->with('success', 'Kategori pengeluaran berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $kategori = $this->model->find($id);
        if ($kategori === null) {
            throw PageNotFoundException::forPageNotFound('Kategori pengeluaran tidak ditemukan.');
        }

        return view('kategori_pengeluaran_form', [
            'title' => 'Edit Kategori Pengeluaran',
            'kategori' => $kategori,
        ]);
    }

    public function update(int $id)
    {
        if ($this->model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Kategori pengeluaran tidak ditemukan.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $saved = $this->model->update($id, [
            'nama_kategori' => trim((string) $this->request->getPost('nama_kategori')),
        ]);

        if ($saved === false) {
            return redirect()->back()->withInput()->with('error', 'Kategori pengeluaran gagal diperbarui. Silakan coba kembali.');
        }

        return redirect()->to($this->baseUrl . '/kategori-pengeluaran')->with('success', 'Kategori pengeluaran berhasil diperbarui.');
    }

    public function arsipkan(int $id)
    {
        if ($this->model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Kategori pengeluaran tidak ditemukan.');
        }

        $now = date('Y-m-d H:i:s');
        $saved = db_connect()->table('kategori_pengeluaran')
            ->where('id_kategori_keluar', $id)
            ->update(['deleted_at' => $now, 'updated_at' => $now]);

        if ($saved === false) {
            return redirect()->to($this->baseUrl . '/kategori-pengeluaran')
                ->with('error', 'Kategori pengeluaran gagal diarsipkan. Silakan coba kembali.');
        }

        return redirect()->to($this->baseUrl . '/kategori-pengeluaran')
            ->with('success', 'Kategori pengeluaran berhasil diarsipkan dari master data.');
    }

    private function rules(): array
    {
        return [
            'nama_kategori' => 'required|max_length[100]',
        ];
    }
}
