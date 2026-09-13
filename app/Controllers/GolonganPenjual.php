<?php

namespace App\Controllers;

use App\Models\GolonganPenjualModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class GolonganPenjual extends BaseController
{
    private GolonganPenjualModel $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->model = new GolonganPenjualModel();
        $this->baseUrl = rtrim((string) config('App')->baseURL, '/');
    }

    public function index()
    {
        return view('golongan_index', [
            'title' => 'Master Golongan Penjual',
            'golongan' => $this->model->orderBy('nominal_iuran', 'DESC')->findAll(),
        ]);
    }

    public function create()
    {
        return view('golongan_form', [
            'title' => 'Tambah Golongan Penjual',
            'golongan' => null,
        ]);
    }

    public function store()
    {
        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->insert([
            'nama_golongan' => trim((string) $this->request->getPost('nama_golongan')),
            'nominal_iuran' => (float) $this->request->getPost('nominal_iuran'),
        ]);

        return redirect()->to($this->baseUrl . '/golongan')->with('success', 'Golongan berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $golongan = $this->model->find($id);
        if ($golongan === null) {
            throw PageNotFoundException::forPageNotFound('Golongan tidak ditemukan.');
        }

        return view('golongan_form', [
            'title' => 'Edit Golongan Penjual',
            'golongan' => $golongan,
        ]);
    }

    public function update(int $id)
    {
        if ($this->model->find($id) === null) {
            throw PageNotFoundException::forPageNotFound('Golongan tidak ditemukan.');
        }

        if (! $this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, [
            'nama_golongan' => trim((string) $this->request->getPost('nama_golongan')),
            'nominal_iuran' => (float) $this->request->getPost('nominal_iuran'),
        ]);

        return redirect()->to($this->baseUrl . '/golongan')->with('success', 'Golongan berhasil diperbarui.');
    }

    private function rules(): array
    {
        return [
            'nama_golongan' => 'required|max_length[50]',
            'nominal_iuran' => 'required|numeric|greater_than[0]',
        ];
    }
}
