<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class User extends BaseController
{
    // 1. Tampilkan Halaman Utama / List Data Pengguna
    public function index()
    {
        $session = session();
        
        // Proteksi login
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('auth'))->with('error', 'Silakan login terlebih dahulu.');
        }

        $db = \Config\Database::connect();

        // Ambil data seluruh pengguna dari tabel users
        // KALIBRASI: Key array diubah menjadi 'users' agar klop dengan foreach di view baru
        $data['users'] = $db->table('users')
                            ->orderBy('nama_lengkap', 'ASC')
                            ->get()
                            ->getResultArray();

        // Mengarah ke file view baru: app/Views/admin/master_users.php
        return view('admin/master_users', $data);
    }

    // 2. Proses Simpan Data Pengguna Baru (Create)
    // 2. Proses Simpan Data Pengguna Baru (Create)
    public function save()
    {
        $db = \Config\Database::connect();
        
        // KALIBRASI KEAMANAN: Mengubah password teks biasa menjadi HASH BCRYPT sebelum masuk ke DB
        $passwordInput = $this->request->getPost('password');
        $passwordHash  = password_hash($passwordInput, PASSWORD_BCRYPT);

        $db->table('users')->insert([
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'nip'          => $this->request->getPost('nip') ?: '-',
            'unit_kerja'   => $this->request->getPost('unit_kerja'),
            'username'     => $this->request->getPost('username'),
            'password'     => $passwordHash, // <-- Sudah dalam bentuk kode hash aman
            'role'         => $this->request->getPost('role'),
            'status'       => $this->request->getPost('status'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        session()->setFlashdata('sukses', 'Data Pengguna Baru berhasil ditambahkan dengan password ter-hash!');
        return redirect()->to(base_url('admin/master_users'));
    }

    // 3. Proses Update Perubahan Data Pengguna (Update)
    public function update()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost('id');
        
        $dataUpdate = [
            'nama_lengkap' => $this->request->getPost('nama_lengkap'),
            'nip'          => $this->request->getPost('nip') ?: '-',
            'unit_kerja'   => $this->request->getPost('unit_kerja'),
            'username'     => $this->request->getPost('username'),
            'role'         => $this->request->getPost('role'),
            'status'       => $this->request->getPost('status'),
        ];

        // LOGIKA PROTEKSI: Cek apakah admin mengetikkan password baru di form edit
        $passwordBaru = $this->request->getPost('password');
        if (!empty($passwordBaru)) {
            // KALIBRASI KEAMANAN: Jika password diganti, wajib di-hash juga sebelum disimpan
            $dataUpdate['password'] = password_hash($passwordBaru, PASSWORD_BCRYPT);
        }

        $db->table('users')->where('id', $id)->update($dataUpdate);

        session()->setFlashdata('sukses', 'Data Pengguna berhasil diperbarui!');
        return redirect()->to(base_url('admin/master_users'));
    }

    // 4. Proses Hapus Data Pengguna (Delete)
    public function delete($id)
    {
        $db = \Config\Database::connect();
        
        // Jalankan perintah hapus
        $db->table('users')->where('id', $id)->delete();
        
        session()->setFlashdata('sukses', 'Data Pengguna berhasil dihapus permanen!');
        return redirect()->to(base_url('admin/master_users'));
    }
}