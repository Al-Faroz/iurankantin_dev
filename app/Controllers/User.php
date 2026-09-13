<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class User extends BaseController
{
    private UserModel $model;
    private string $baseUrl;

    public function __construct()
    {
        $this->model = new UserModel();
        $this->baseUrl = rtrim((string) config('App')->baseURL, '/');
    }

    public function index()
    {
        return view('user_index', [
            'title' => 'Manajemen User',
            'users' => $this->model->orderBy('role', 'ASC')->orderBy('nama', 'ASC')->findAll(),
        ]);
    }

    public function create()
    {
        return view('user_form', [
            'title' => 'Tambah User',
            'user' => null,
        ]);
    }

    public function store()
    {
        $rules = $this->rules(true);
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = trim((string) $this->request->getPost('username'));
        if ($this->model->where('username', $username)->first() !== null) {
            return redirect()->back()->withInput()->with('error', 'Username sudah digunakan.');
        }

        $this->model->insert([
            'nama' => trim((string) $this->request->getPost('nama')),
            'username' => $username,
            'password' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => (string) $this->request->getPost('role'),
            'status_aktif' => (string) $this->request->getPost('status_aktif'),
        ]);

        return redirect()->to($this->baseUrl . '/user')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(int $id)
    {
        $user = $this->model->find($id);
        if ($user === null) {
            throw PageNotFoundException::forPageNotFound('User tidak ditemukan.');
        }

        return view('user_form', [
            'title' => 'Edit User',
            'user' => $user,
        ]);
    }

    public function update(int $id)
    {
        $user = $this->model->find($id);
        if ($user === null) {
            throw PageNotFoundException::forPageNotFound('User tidak ditemukan.');
        }

        if (! $this->validate($this->rules(false))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $username = trim((string) $this->request->getPost('username'));
        $duplicate = $this->model->where('username', $username)->where('id_user !=', $id)->first();
        if ($duplicate !== null) {
            return redirect()->back()->withInput()->with('error', 'Username sudah digunakan.');
        }

        $role = (string) $this->request->getPost('role');
        $status = (string) $this->request->getPost('status_aktif');
        if ($id === (int) session()->get('id_user') && ($role !== 'Operator' || $status !== 'Aktif')) {
            return redirect()->back()->withInput()->with('error', 'Akun Operator yang sedang digunakan tidak boleh dinonaktifkan atau diubah menjadi Pimpinan.');
        }

        $data = [
            'nama' => trim((string) $this->request->getPost('nama')),
            'username' => $username,
            'role' => $role,
            'status_aktif' => $status,
        ];

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $this->model->update($id, $data);

        if ($id === (int) session()->get('id_user')) {
            session()->set([
                'nama' => $data['nama'],
                'username' => $data['username'],
            ]);
        }

        return redirect()->to($this->baseUrl . '/user')->with('success', 'User berhasil diperbarui.');
    }

    private function rules(bool $passwordRequired): array
    {
        return [
            'nama' => 'required|max_length[100]',
            'username' => 'required|min_length[3]|max_length[50]|alpha_numeric_punct',
            'password' => ($passwordRequired ? 'required|' : 'permit_empty|') . 'min_length[6]|max_length[255]',
            'role' => 'required|in_list[Operator,Pimpinan]',
            'status_aktif' => 'required|in_list[Aktif,Nonaktif]',
        ];
    }
}
