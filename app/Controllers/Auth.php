<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Auth extends BaseController
{
    public function index()
    {
        // Jika user sudah terlanjur login, lempar langsung ke halaman dashboard/lembur
        if (session()->get('isLoggedIn')) {
            return redirect()->to(base_url('admin/lembur'));
        }
        return view('auth/login');
    }

    public function validasi()
    {
        // 1. Ambil input form + bersihkan dari spasi gaib
        $username = trim($this->request->getPost('username') ?? '');
        $password = trim($this->request->getPost('password') ?? '');

        if (empty($username) || empty($password)) {
            return redirect()->back()->withInput()->with('error', 'Username dan Password tidak boleh kosong.');
        }

        // 2. Ambil data user dari database
        $db   = \Config\Database::connect();
        $user = $db->table('users')
                   ->where('username', $username)
                   ->get()
                   ->getRowArray();

        if ($user) {
            
            // 3. VERIFIKASI MULTI-LAYER PASSWORD (Bisa Hash & Bisa Teks Biasa) ⚙️🔐
            $passwordCocok = false;
            $dbPassword    = trim($user['password']);

            // Jalur A: Cek menggunakan standar Bcrypt bawaan PHP (Untuk password yang SUDAH di-hash)
            if (password_verify($password, $dbPassword)) {
                $passwordCocok = true;
            } 
            // Jalur B: Cek teks murni langsung (Untuk akun lama yang BELUM di-hash)
            elseif ($password === $dbPassword) {
                $passwordCocok = true;
            }

            // 4. Evaluasi Hasil Pengecekan
            if ($passwordCocok) {
                
                // 5. SET SESSION SAKTI
                $sessionData = [
                    'id_user'      => $user['id'],
                    'username'     => $user['username'],
                    'nama_lengkap' => $user['nama_lengkap'],
                    'nip'          => $user['nip'],
                    'unit_kerja'   => $user['unit_kerja'], 
                    'role'         => $user['role'] ?? 'pegawai',
                    'isLoggedIn'   => true
                ];
                
                session()->remove(array_keys($sessionData));
                session()->set($sessionData);

                // Sukses login, lempar langsung ke halaman lembur!
                return redirect()->to(base_url('admin/lembur'));

            } else {
                return redirect()->back()->withInput()->with('error', 'Password yang Anda masukkan salah.');
            }
        } else {
            return redirect()->back()->withInput()->with('error', 'Username tidak terdaftar di sistem.');
        }
    }

    public function logout()
    {
        // Hancurkan semua session aktif untuk keluar sistem
        session()->destroy();
        return redirect()->to(base_url('auth'));
    }
}