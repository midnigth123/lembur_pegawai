<?php

namespace App\Controllers;

use App\Models\LemburModel;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class Lembur extends Controller
{
    protected $lemburModel;

    public function __construct()
    {
        $this->lemburModel = new LemburModel();
    }

    // Menampilkan daftar lembur dengan filter bulan & tahun
    // Menampilkan daftar lembur dengan filter bulan & tahun serta hak akses Multi-Unit (Dinamis)
    public function index()
{
    $session   = session();
    $role      = $session->get('role'); 
    $unitKerja = $session->get('unit_kerja');
    $idUser    = $session->get('id_user');

    if (!$session->get('isLoggedIn')) {
        return redirect()->to(base_url('auth'))->with('error', 'Silakan login terlebih dahulu.');
    }

    $bulan = $this->request->getGet('bulan') ?? date('m');
    $tahun = $this->request->getGet('tahun') ?? date('Y');
    $filterUser = $this->request->getGet('id_user'); 

    $db = \Config\Database::connect();
    $data['karyawan_unit'] = [];

    // Reset Model CodeIgniter agar kueri sebelumnya tidak tersangkut di memori
    $this->lemburModel->builder()->resetQuery();

    // =========================================================================
    // LOGIKA FILTER DISERAGAMKAN DAN DIKALIBRASI ANTI-GAIB
    // =========================================================================
    if ($role === 'superadmin' || $role === 'admin') {
        // 1. SUPERADMIN / ADMIN PUSAT: Ambil semua karyawan lintas unit untuk dropdown (Bebas Role)
        $data['karyawan_unit'] = $db->table('users')
                                    ->orderBy('unit_kerja', 'ASC')
                                    ->orderBy('nama_lengkap', 'ASC')
                                    ->get()
                                    ->getResultArray();

        $this->lemburModel->where('MONTH(tanggal)', $bulan)
                          ->where('YEAR(tanggal)', $tahun);

        // Jika filter nama dipilih, cari berdasarkan id_user tersebut
        if (!empty($filterUser)) {
            $this->lemburModel->where('id_user', $filterUser);
        }
    } 
    elseif ($role === 'pjsdm') {
        // 2. PJ SDM: Ambil semua karyawan yang satu unit kerja (Tanpa dikunci role='pegawai')
        $data['karyawan_unit'] = $db->table('users')
                                    ->where('unit_kerja', $unitKerja) 
                                    ->orderBy('nama_lengkap', 'ASC')
                                    ->get()
                                    ->getResultArray();

        $this->lemburModel->where('MONTH(tanggal)', $bulan)
                          ->where('YEAR(tanggal)', $tahun);

        // STRATEGI FIX: Jika PJSDM memfilter nama orang, langsung kunci ke id_user-nya saja.
        // Jika dropdown dikosongkan (Semua Karyawan), baru kunci berdasarkan unit_kerja global.
        if (!empty($filterUser)) {
            $this->lemburModel->where('id_user', $filterUser);
        } else {
            // Gunakan query builder internal model untuk jaga-jaga beda nama tabel master
            $this->lemburModel->where($this->lemburModel->table . '.unit_kerja', $unitKerja);
        }
    } 
    else {
        // 3. PEGAWAI BIASA: Tetap aman dikunci murni hanya melihat miliknya sendiri
        $this->lemburModel->where('id_user', $idUser)
                          ->where('MONTH(tanggal)', $bulan)
                          ->where('YEAR(tanggal)', $tahun);
    }

    // Ambil data lembur dan gabungkan secara aman dengan tabel users
    $tableName = $this->lemburModel->table; // Deteksi otomatis nama tabel model (lembur / lemburs)
    
    $lemburs = $this->lemburModel
                    ->select($tableName . '.*, users.nama_lengkap as nama_pegawai')
                    ->join('users', 'users.id = ' . $tableName . '.id_user', 'left')
                    ->orderBy('tanggal', 'DESC')
                    ->findAll();

    $data['title']         = ($role === 'superadmin' || $role === 'admin') ? 'Rekap Lembur Seluruh Unit' : 'Rekap Lembur Unit ' . $unitKerja;
    $data['lemburs']       = $lemburs;
    $data['current_bulan'] = $bulan;
    $data['current_tahun'] = $tahun;
    $data['filter_user']   = $filterUser; // Mengirim id yang dipilih agar select option tetap 'selected'

    return view('admin/rekap_bulanan', $data);
}

    // Menampilkan form input (Opsional)
    public function create()
    {
        return view('admin/form_input');
    }

    // Memproses simpan data dari form modal tambah (Multi-upload)
    public function save()
{
    $session = session();
    
    // 1. Amankan sesi login
    if (!$session->get('isLoggedIn')) {
        return redirect()->to(base_url('auth'))->with('error', 'Sesi Anda berakhir, silakan login kembali.');
    }

    $idUserLogin = $session->get('id_user');
    $db = \Config\Database::connect();

    // 2. Ambil data profil user yang sedang login
    $userData = $db->table('users')->where('id', $idUserLogin)->get()->getRowArray();

    if (!$userData) {
        return redirect()->back()->with('error', 'Data pengguna tidak valid.');
    }

    // 3. KALIBRASI DINAMIS: Cari ID Unit Kerja dari tabel master_unit berdasarkan teks unit_kerja user
    $textUnitUser = $userData['unit_kerja']; // Menghasilkan teks, misal: 'SIMRS' atau 'Tata Usaha'
    
    $unitData = $db->table('master_unit')
                   ->where('nama_unit', $textUnitUser)
                   ->get()
                   ->getRowArray();

    // Jika di master_unit tidak ditemukan teks yang cocok, beri default id atau handle error
    if ($unitData) {
        $idUnitKerja = $unitData['id']; // Menghasilkan angka asli: 1 atau 2
    } else {
        // Fallback jika tidak ketemu (misal isi default angka 1 atau return error)
        return redirect()->back()->with('error', 'Unit kerja pengguna belum terdaftar di Master Unit.');
    }

    // 4. Proses Upload File Bukti
    $files = $this->request->getFileMultiple('bukti');
    $namaFilesArr = [];

    if ($files) {
        foreach ($files as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move('uploads/bukti', $newName);
                $namaFilesArr[] = $newName;
            }
        }
    }

    $jsonBukti = !empty($namaFilesArr) ? json_encode($namaFilesArr) : '';

    // 5. INSERT KE DATABASE (Menggunakan ID Asli Pengguna dan ID Asli Unit)
    $db->table('lembur')->insert([
        'id_user'       => $idUserLogin,    // Tersimpan angka ID user (Contoh: 1, 4, 8)
        'unit_kerja'    => $idUnitKerja,    // KALIBRASI: Sekarang yang masuk adalah angka ID Unit (Contoh: 1 atau 2)
        'tanggal'       => $this->request->getPost('tanggal'),
        'uraian'        => $this->request->getPost('uraian'),
        'tindak_lanjut' => $this->request->getPost('tindak_lanjut'),
        'target'        => $this->request->getPost('target'),
        'bukti'         => $jsonBukti
    ]);

    return redirect()->to(base_url('admin/lembur'))->with('success', 'Data lembur berhasil disimpan dengan relasi ID Unit!');
}

    // Memproses update data dari form modal edit (Multi-upload)
    public function update()
    {
        $id = $this->request->getPost('id');

        $dataLama = $this->lemburModel->find($id);
        if (!$dataLama) {
            return redirect()->to('/admin/lembur')->with('error', 'Data tidak ditemukan!');
        }

        // Gunakan bukti lama sebagai default
        $jsonBukti = $dataLama['bukti'];

        $files = $this->request->getFileMultiple('bukti');
        $namaFilesArr = [];

        // Validasi pengaman untuk memastikan file diunggah
        if ($files && isset($files[0]) && $files[0]->isValid()) {
            
            // Proses upload kumpulan file baru
            foreach ($files as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $newName = $file->getRandomName();
                    $file->move('uploads/bukti', $newName);
                    $namaFilesArr[] = $newName;
                }
            }

            // Jika upload file baru berhasil, ganti file lama
            if (!empty($namaFilesArr)) {
                $jsonBukti = json_encode($namaFilesArr);

                // Hapus berkas fisik lama dari penyimpanan server
                if (!empty($dataLama['bukti'])) {
                    $arrayFotoLama = json_decode($dataLama['bukti'], true);
                    
                    if (is_array($arrayFotoLama)) {
                        foreach ($arrayFotoLama as $fotoLama) {
                            if (file_exists('uploads/bukti/' . $fotoLama)) {
                                unlink('uploads/bukti/' . $fotoLama);
                            }
                        }
                    } else {
                        if (file_exists('uploads/bukti/' . $dataLama['bukti'])) {
                            unlink('uploads/bukti/' . $dataLama['bukti']);
                        }
                    }
                }
            }
        }

        // Eksekusi update ke database
        $this->lemburModel->update($id, [
            'tanggal'       => $this->request->getPost('tanggal'),
            'uraian'        => $this->request->getPost('uraian'),
            'tindak_lanjut' => $this->request->getPost('tindak_lanjut'),
            'target'        => $this->request->getPost('target'),
            'bukti'         => $jsonBukti
        ]);

        return redirect()->to('/admin/lembur')->with('success', 'Data lembur berhasil diperbarui!');
    }

    // Memproses hapus data beserta seluruh file fisiknya
    public function delete($id = null)
    {
        if ($id === null) {
            return redirect()->to('/admin/lembur')->with('error', 'ID data tidak valid!');
        }

        $data = $this->lemburModel->find($id);

        if ($data) {
            if (!empty($data['bukti'])) {
                $arrayBukti = json_decode($data['bukti'], true);
                
                if (is_array($arrayBukti)) {
                    foreach ($arrayBukti as $foto) {
                        if (file_exists('uploads/bukti/' . $foto)) {
                            unlink('uploads/bukti/' . $foto);
                        }
                    }
                } else {
                    if (file_exists('uploads/bukti/' . $data['bukti'])) {
                        unlink('uploads/bukti/' . $data['bukti']);
                    }
                }
            }

            $this->lemburModel->delete($id);
            return redirect()->to('/admin/lembur')->with('success', 'Data lembur berhasil dihapus!');
        }

        return redirect()->to('/admin/lembur')->with('error', 'Data gagal dihapus atau tidak ditemukan!');
    }

    // Melakukan export data ke format Excel (.xlsx) berdasarkan filter aktif
public function exportExcel()
{
    // =========================================================================
    // 1. AMBIL DATA SESSION & FILTER INDIVIDU DARI URL
    // =========================================================================
    $session   = session();
    $role      = $session->get('role');
    $unitKerja = $session->get('unit_kerja');

    // Pengaman: Wajib login
    if (!$session->get('isLoggedIn')) {
        return redirect()->to(base_url('auth'))->with('error', 'Silakan login terlebih dahulu.');
    }

    $bulan      = $this->request->getGet('bulan') ?? date('m');
    $tahun      = $this->request->getGet('tahun') ?? date('Y');
    $filterUser = $this->request->getGet('id_user'); // ID pegawai yang dipilih oleh Admin / PJSDM

    // Array nama bulan Indonesia
    $namaBulanIndo = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
        '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
        '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    $teksBulan = $namaBulanIndo[$bulan] ?? date('F');

    $db = \Config\Database::connect();

    // =========================================================================
    // 2. DETEKSI IDENTITAS KARYAWAN (KALIBRASI MULTI-ROLE: DISETARAKAN DENGAN INDEX)
    // =========================================================================
    // MODIFIKASI: Menambahkan 'superadmin' dan 'pjsdm' ke dalam daftar role yang boleh memfilter orang lain
    if (in_array($role, ['superadmin', 'admin', 'pjsdm', 'ka_unit']) && !empty($filterUser)) {
        // Jika Atasan/Admin yang download dan sedang memilih 1 pegawai tertentu
        $pegawai = $db->table('users')->where('id', $filterUser)->get()->getRowArray();
        
        $namaPegawaiCetak = $pegawai ? $pegawai['nama_lengkap'] : '....................................................';
        $nipPegawaiCetak  = $pegawai ? $pegawai['nip'] : '-';
        $unitKerjaCetak   = $pegawai ? $pegawai['unit_kerja'] : $unitKerja;
        $idUserCetak      = $filterUser;
    } else {
        // Jika pegawai itu sendiri yang download atau Admin mengklik export tanpa memilih personal (Semua Karyawan)
        $namaPegawaiCetak = $session->get('nama_lengkap');
        $nipPegawaiCetak  = $session->get('nip') ?? '-';
        $unitKerjaCetak   = $unitKerja;
        $idUserCetak      = $session->get('id_user');
    }

    // =========================================================================
    // 3. AMBIL DATA KEPALA UNIT (Sisi Kiri Bawah) Berdasarkan Unit Kerja Terkait
    // =========================================================================
    $masterKepalaModel = new \App\Models\MasterKepalaModel();
    $atasan = $masterKepalaModel->where('unit_kerja', $unitKerjaCetak)
                                ->where('status', 'aktif')
                                ->first();

    $namaAtasan = $atasan ? $atasan['nama_kepala'] : '....................................................';
    $nipAtasan  = $atasan ? $atasan['nip_kepala'] : '-';

    // =========================================================================
    // 4. QUERY FILTER DATA LEMBUR (DIKUNCI HANYA 1 USER AGAR SIAP PRINT)
    // =========================================================================
    $lemburs = $this->lemburModel
                    ->where('id_user', $idUserCetak) // Mengunci data milik 1 pegawai target cetak
                    ->where('MONTH(tanggal)', $bulan)
                    ->where('YEAR(tanggal)', $tahun)
                    ->orderBy('tanggal', 'ASC')
                    ->findAll();

    // =========================================================================
    // 5. PROSES SPREADSHEET & STYLING
    // =========================================================================
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Rekap Lembur');

    $styleHeaderTabel = [
        'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 11],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
        ],
    ];

    $styleBorderIsi = [
        'borders' => [
            'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
        ],
        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER]
    ];

    // --- MEMBUAT KOP LAPORAN ---
    $sheet->setCellValue('A1', 'LAPORAN HASIL KERJA LEMBUR');
    $sheet->mergeCells('A1:F1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A2', 'BULAN : ' . strtoupper($teksBulan) . ' TAHUN ' . $tahun);
    $sheet->mergeCells('A2:F2');
    $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Metadata Karyawan Dinamis Bersih
    $sheet->setCellValue('A4', 'NAMA');      $sheet->setCellValue('B4', ': ' . $namaPegawaiCetak);
    $sheet->setCellValue('A5', 'NIP');       $sheet->setCellValue('B5', ': ' . $nipPegawaiCetak);
    $sheet->setCellValue('A6', 'UNIT KERJA'); $sheet->setCellValue('B6', ': ' . $unitKerjaCetak);
    $sheet->getStyle('A4:A6')->getFont()->setBold(true);

    // HEADERS TABEL (Baris ke-8)
    $headers = ['NO', 'TANGGAL', 'URAIAN PEKERJAAN', 'BUKTI FISIK', 'Tindak Lanjut', 'TARGET'];
    $kolomChar = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($kolomChar . '8', $header);
        $sheet->getStyle($kolomChar . '8')->applyFromArray($styleHeaderTabel);
        $kolomChar++;
    }
    $sheet->getRowDimension('8')->setRowHeight(35);

    // --- PENGISIAN DATA DINAMIS & EMBED GAMBAR ---
    $barisMulai = 9;
    $no = 1;

    foreach ($lemburs as $l) {
        $arrayBukti = !empty($l['bukti']) ? json_decode($l['bukti'], true) : [];
        $arrayBukti = is_array($arrayBukti) ? array_filter($arrayBukti) : [];
        $jumlahFoto = count($arrayBukti);

        $barisDibutuhkan = ($jumlahFoto > 1) ? $jumlahFoto : 1;
        $barisAkhir = $barisMulai + $barisDibutuhkan - 1;

        $sheet->setCellValue('A' . $barisMulai, $no++);
        $sheet->setCellValue('B' . $barisMulai, date('d/m/Y', strtotime($l['tanggal'])));
        $sheet->setCellValue('C' . $barisMulai, $l['uraian']);
        $sheet->setCellValue('E' . $barisMulai, $l['tindak_lanjut']);
        $sheet->setCellValue('F' . $barisMulai, $l['target'] . '%');

        if ($barisDibutuhkan > 1) {
            $sheet->mergeCells("A{$barisMulai}:A{$barisAkhir}");
            $sheet->mergeCells("B{$barisMulai}:B{$barisAkhir}");
            $sheet->mergeCells("C{$barisMulai}:C{$barisAkhir}");
            $sheet->mergeCells("E{$barisMulai}:E{$barisAkhir}");
            $sheet->mergeCells("F{$barisMulai}:F{$barisAkhir}");
        }

        $sheet->getStyle("A{$barisMulai}:F{$barisAkhir}")->applyFromArray($styleBorderIsi);
        $sheet->getStyle("A{$barisMulai}:A{$barisAkhir}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("B{$barisMulai}:B{$barisAkhir}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("C{$barisMulai}:C{$barisAkhir}")->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("E{$barisMulai}:E{$barisAkhir}")->getAlignment()->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("F{$barisMulai}:F{$barisAkhir}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        if (!empty($arrayBukti)) {
            $indexFoto = 0;
            for ($b = $barisMulai; $b <= $barisAkhir; $b++) {
                $foto = $arrayBukti[$indexFoto] ?? null;
                if ($foto) {
                    $pathFoto = FCPATH . 'uploads/bukti/' . $foto;
                    if (file_exists($pathFoto) && !is_dir($pathFoto)) {
                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setName('Bukti Lembur');
                        $drawing->setDescription('Foto Bukti');
                        $drawing->setPath($pathFoto);
                        $drawing->setWidth(400); 
                        $drawing->setCoordinates('D' . $b);
                        $drawing->setOffsetX(12); 
                        $drawing->setOffsetY(10);
                        $drawing->setWorksheet($sheet);
                    }
                }
                $sheet->getRowDimension($b)->setRowHeight(170);
                $indexFoto++;
            }
        } else {
            $sheet->getRowDimension($barisMulai)->setRowHeight(40);
        }

        $barisMulai = $barisAkhir + 1;
    }

    // --- BAGIAN TANDA TANGAN (SEJAJAR & RAPI) ---
    $barisTtd = $barisMulai + 2; 

    // SISI KIRI: ATASAN / KEPALA UNIT
    $sheet->setCellValue('A' . $barisTtd, 'Mengetahui,');
    $sheet->mergeCells("A{$barisTtd}:C{$barisTtd}");
    $sheet->getStyle('A' . $barisTtd)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('A' . ($barisTtd + 1), 'Ka Unit Kerja');
    $sheet->mergeCells("A" . ($barisTtd + 1) . ":C" . ($barisTtd + 1));
    $sheet->getStyle('A' . ($barisTtd + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A' . $barisTtd . ':A' . ($barisTtd + 1))->getFont()->setBold(true);

    // SISI KANAN: TANGGAL & PEGAWAI YANG BERSANGKUTAN
    $tanggalIndo = date('d') . ' ' . $teksBulan . ' ' . date('Y');
    $sheet->setCellValue('E' . $barisTtd, 'Padang, ' . $tanggalIndo);
    $sheet->mergeCells("E{$barisTtd}:F{$barisTtd}");
    $sheet->getStyle('E' . $barisTtd)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $sheet->setCellValue('E' . ($barisTtd + 1), 'Pegawai yang bersangkutan');
    $sheet->mergeCells("E" . ($barisTtd + 1) . ":F" . ($barisTtd + 1));
    $sheet->getStyle('E' . ($barisTtd + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E' . $barisTtd . ':E' . ($barisTtd + 1))->getFont()->setBold(true);

    $barisNama = $barisTtd + 6;

    // Nama & NIP Atasan Terkait (Dinamis Kiri)
    $sheet->setCellValue('A' . $barisNama, '( ' . $namaAtasan . ' )');
    $sheet->setCellValue('A' . ($barisNama + 1), 'NIP. ' . $nipAtasan);
    $sheet->mergeCells("A{$barisNama}:C{$barisNama}");
    $sheet->mergeCells("A" . ($barisNama + 1) . ":C" . ($barisNama + 1));
    $sheet->getStyle('A' . $barisNama . ':A' . ($barisNama + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A' . $barisNama)->getFont()->setBold(true);

    // Nama & NIP Karyawan Terkait (Dinamis Kanan)
    $sheet->setCellValue('E' . $barisNama, '( ' . $namaPegawaiCetak . ' )');
    $sheet->setCellValue('E' . ($barisNama + 1), 'NIP. ' . $nipPegawaiCetak);
    $sheet->mergeCells("E{$barisNama}:F{$barisNama}");
    $sheet->mergeCells("E" . ($barisNama + 1) . ":F" . ($barisNama + 1));
    $sheet->getStyle('E' . $barisNama . ':E' . ($barisNama + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E' . $barisNama)->getFont()->setBold(true);

    // LEBAR KOLOM STANDARD
    $sheet->getColumnDimension('A')->setWidth(6);
    $sheet->getColumnDimension('B')->setWidth(15);
    $sheet->getColumnDimension('C')->setWidth(35);
    $sheet->getColumnDimension('D')->setWidth(60); 
    $sheet->getColumnDimension('E')->setWidth(45);
    $sheet->getColumnDimension('F')->setWidth(15);

    // --- PROSES STREAM DOWNLOAD ---
    $namaClean = str_replace([' ', ',', '.'], '_', strtoupper($namaPegawaiCetak));
    $namaFileDownload = 'LAPORAN_LEMBUR_' . strtoupper($unitKerjaCetak) . '_' . $namaClean . '_' . strtoupper($teksBulan) . '_' . $tahun . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $namaFileDownload . '"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
}