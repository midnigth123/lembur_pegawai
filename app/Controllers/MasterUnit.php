<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class MasterUnit extends BaseController
{
    // 1. Tampilkan Halaman Master Unit Kerja
    public function index()
    {
        $session = session();
        
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('auth'))->with('error', 'Silakan login terlebih dahulu.');
        }

        $db = \Config\Database::connect();

        // Mengambil seluruh data dari tabel master_unit
        $data['units'] = $db->table('master_unit')
                            ->orderBy('nama_unit', 'ASC')
                            ->get()
                            ->getResultArray();

        return view('admin/master_unit', $data);
    }

    // 2. Simpan Unit Kerja Baru
    public function save()
    {
        $db = \Config\Database::connect();
        
        // Simpan teks dalam format HURUF KAPITAL SEMUA agar data seragam konsisten
        $namaUnit = strtoupper(trim($this->request->getPost('nama_unit') ?? ''));

        $db->table('master_unit')->insert([
            'nama_unit' => $namaUnit
        ]);

        session()->setFlashdata('sukses', 'Unit kerja baru berhasil ditambahkan!');
        return redirect()->to(base_url('admin/master_unit'));
    }

    // 3. Update Nama Unit Kerja
    public function update()
    {
        $db = \Config\Database::connect();
        $id = $this->request->getPost('id');
        $namaUnit = strtoupper(trim($this->request->getPost('nama_unit') ?? ''));

        $db->table('master_unit')
           ->where('id', $id)
           ->update(['nama_unit' => $namaUnit]);

        session()->setFlashdata('sukses', 'Nama unit kerja berhasil diperbarui!');
        return redirect()->to(base_url('admin/master_unit'));
    }

    // 4. Hapus Unit Kerja
    public function delete($id)
    {
        $db = \Config\Database::connect();
        
        $db->table('master_unit')->where('id', $id)->delete();
        
        session()->setFlashdata('sukses', 'Unit kerja berhasil dihapus dari sistem!');
        return redirect()->to(base_url('admin/master_unit'));
    }
    public function update_profil_mandiri()
    {
        $session = session();
        
        // 1. Proteksi awal: Pastikan user sudah login
        if (!$session->get('isLoggedIn')) {
            return redirect()->to(base_url('auth'));
        }

        $db = \Config\Database::connect();
        $idUser = $session->get('id_user');

        // 2. Ambil data dari form modal
        $namaLengkap  = trim($this->request->getPost('nama_lengkap') ?? '');
        $passwordBaru = trim($this->request->getPost('password_baru') ?? '');

        $dataUpdate = [
            'nama_lengkap' => $namaLengkap
        ];

        if (!empty($passwordBaru)) {
            $dataUpdate['password'] = password_hash($passwordBaru, PASSWORD_BCRYPT);
        }

        $db->table('users')
           ->where('id', $idUser)
           ->update($dataUpdate);
        $session->set('nama_lengkap', $namaLengkap);

        return redirect()->back()->with('sukses', 'Profil Anda berhasil diperbarui dengan aman!');
    }
}