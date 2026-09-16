<?php

namespace App\Filters;

use App\Services\AuthSessionService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class OperatorOnlyFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $baseUrl = rtrim((string) config('App')->baseURL, '/');

        if (! session()->get('is_logged_in')) {
            return redirect()->to($baseUrl . '/login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = (new AuthSessionService())->revalidate();
        if ($user === null) {
            session()->destroy();

            return redirect()->to($baseUrl . '/login')
                ->with('error', 'Sesi berakhir atau akun sudah tidak aktif. Silakan login kembali.');
        }

        if (($user['role'] ?? '') !== 'Operator') {
            return redirect()->to($baseUrl . '/dashboard')
                ->with('error', 'Akses ini hanya tersedia untuk Operator.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada proses setelah response.
    }
}
