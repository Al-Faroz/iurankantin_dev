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

        $this->model->insert([
            'nama_kategori' => trim((string) $this->request->getPost('nama_kategori')),
        ]);

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

        $this->model->update($id, [
            'nama_kategori' => trim((string) $this->request->getPost('nama_kategori')),
        ]);

        return redirect()->to($this->baseUrl . '/kategori-pengeluaran')->with('success', 'Kategori pengeluaran berhasil diperbarui.');
    }

    private function rules(): array
    {
        return [
            'nama_kategori' => 'required|max_length[100]',
        ];
    }
}
