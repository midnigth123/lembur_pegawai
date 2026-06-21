<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Cek apakah user sudah login, jika belum tendang ke halaman login
        if (!session()->get('isLoggedIn')) {
        return redirect()->to(base_url('auth'))->with('error', 'Silakan login terlebih dahulu.');
        }

        // KUNCI KETAT: Jika BUKAN superadmin, tendang!
        if (session()->get('role') !== 'superadmin') {
            return redirect()->to(base_url('admin/lembur'))->with('error', 'Akses ditolak! Halaman tersebut khusus untuk Superadmin.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Kosongkan saja
    }
}