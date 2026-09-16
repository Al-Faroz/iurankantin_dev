<?php

namespace App\Filters;

use App\Services\AuthSessionService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $loginUrl = rtrim((string) config('App')->baseURL, '/') . '/login';

        if (! session()->get('is_logged_in')) {
            return redirect()->to($loginUrl)
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Jangan hanya mempercayai data role/status yang tersimpan di session.
        // Akun yang dinonaktifkan atau diubah rolenya harus berlaku pada request berikutnya.
        if ((new AuthSessionService())->revalidate() === null) {
            session()->destroy();

            return redirect()->to($loginUrl)
                ->with('error', 'Sesi berakhir atau akun sudah tidak aktif. Silakan login kembali.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada proses setelah response.
    }
}
