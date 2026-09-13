<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function login()
    {
        $baseUrl = rtrim((string) config('App')->baseURL, '/');

        if (session()->get('is_logged_in')) {
            return redirect()->to($baseUrl . '/dashboard');
        }

        return view('auth_login', [
            'title' => 'Login',
            'baseUrl' => $baseUrl,
        ]);
    }

    public function attempt()
    {
        $baseUrl = rtrim((string) config('App')->baseURL, '/');

        if (session()->get('is_logged_in')) {
            return redirect()->to($baseUrl . '/dashboard');
        }

        // Batasi brute-force login tanpa dependency tambahan. File cache CI4 menjadi
        // backend throttler; bucket di-reset setelah autentikasi berhasil.
        $throttler = service('throttler');
        $throttleKey = 'login-' . hash('sha256', (string) $this->request->getIPAddress());
        if (! $throttler->check($throttleKey, 10, MINUTE)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terlalu banyak percobaan login. Tunggu sebentar lalu coba kembali.');
        }

        $rules = [
            'username' => 'required|min_length[3]|max_length[50]',
            'password' => 'required|min_length[6]|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        $user = (new UserModel())->findActiveByUsername($username);

        if ($user === null || ! password_verify($password, $user['password'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Username atau password tidak sesuai.');
        }

        $throttler->remove($throttleKey);
        session()->regenerate(true);
        session()->set([
            'id_user' => (int) $user['id_user'],
            'nama' => $user['nama'],
            'username' => $user['username'],
            'role' => $user['role'],
            'is_logged_in' => true,
        ]);

        return redirect()->to($baseUrl . '/dashboard');
    }

    public function logout()
    {
        $baseUrl = rtrim((string) config('App')->baseURL, '/');
        session()->destroy();

        return redirect()->to($baseUrl . '/login');
    }
}
