<?php
/**
 * @var array $lemburs
 * @var array $karyawan_unit
 */
?>
<?= $this->extend('layout/admin_layout') ?>
<?= $this->section('title') ?>Rekap Lembur<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="card shadow-sm border-0 mb-4 bg-white" style="border-radius: 12px;">
    <div class="card-body py-3">
        <form method="get" action="<?= base_url('admin/lembur'); ?>"
            class="form-inline d-flex flex-wrap align-items-center">

            <div class="form-group mb-2 mb-sm-0 mr-3">
                <span class="text-secondary small font-weight-bold text-uppercase tracking-wider">
                    <i class="fas fa-calendar-alt text-primary mr-1"></i> Periode Rekap
                </span>
            </div>

            <div class="input-group input-group-sm mb-2 mb-sm-0 mr-2 shadow-sm"
                style="border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-0 text-muted px-3"><i
                            class="fas fa-filter small"></i></span>
                </div>
                <select name="bulan" class="form-control border-0 bg-light font-weight-500 text-dark pr-4"
                    style="height: 36px; min-width: 140px; cursor: pointer;">
                    <?php for($i=1; $i<=12; $i++): $m = sprintf('%02d', $i); ?>
                    <option value="<?= $m ?>"
                        <?= (isset($_GET['bulan']) && $_GET['bulan'] == $m) ? 'selected' : (date('m') == $m ? 'selected' : '') ?>>
                        <?= date('F', mktime(0, 0, 0, $i, 10)) ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>


            <div class="input-group input-group-sm mb-2 mb-sm-0 mr-2 shadow-sm"
                style="border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-0 text-muted px-3"><i
                            class="fas fa-calendar-check small"></i></span>
                </div>
                <select name="tahun" class="form-control border-0 bg-light font-weight-500 text-dark pr-4"
                    style="height: 36px; min-width: 100px; cursor: pointer;">
                    <?php for($y = date('Y')-2; $y <= date('Y')+1; $y++): ?>
                    <option value="<?= $y ?>"
                        <?= (isset($_GET['tahun']) && $_GET['tahun'] == $y) ? 'selected' : (date('Y') == $y ? 'selected' : '') ?>>
                        <?= $y ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- KALIBRASI: Dropdown Pilihan Karyawan (Hanya tampil untuk Admin Unit / Ka Unit) -->
            <?php if (in_array(session()->get('role'), ['superadmin', 'admin', 'pjsdm'])) : ?>
            <div class="input-group input-group-sm mb-2 mb-sm-0 mr-3 shadow-sm"
                style="border-radius: 20px; overflow: hidden; border: 1px solid #e2e8f0;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-0 text-muted px-3">
                        <i class="fas fa-user small"></i>
                    </span>
                </div>
                <select name="id_user" class="form-control border-0 bg-light font-weight-500 text-dark pr-4"
                    style="height: 36px; min-width: 200px; cursor: pointer;">
                    <option value="">-- Semua Pegawai Unit --</option>
                    <?php if (!empty($karyawan_unit)): ?>
                    <?php foreach ($karyawan_unit as $k) : ?>
                    <option value="<?= $k['id'] ?>"
                        <?= (isset($_GET['id_user']) && $_GET['id_user'] == $k['id']) ? 'selected' : '' ?>>
                        <?= esc($k['nama_lengkap']) ?>
                    </option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <?php endif; ?>

            <button type="submit"
                class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm font-weight-bold d-flex align-items-center mb-2 mb-sm-0"
                style="height: 36px;">
                <i class="fas fa-search small mr-2"></i> Tampilkan
            </button>

        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <!-- KALIBRASI: Header Judul Mengikuti Unit Kerja Session -->
        <h5 class="card-title mb-0 font-weight-bold text-dark">Daftar Lembur
            <?= session()->get('unit_kerja') ? 'Unit ' . session()->get('unit_kerja') : '' ?></h5>
        <div>
            <!-- KALIBRASI: Menyuntikkan id_user aktif ke URL exportExcel agar unduhan file sejalan dengan filter screen -->
            <a href="<?= base_url('admin/lembur/exportExcel?bulan=' . (isset($_GET['bulan']) ? $_GET['bulan'] : date('m')) . '&tahun=' . (isset($_GET['tahun']) ? $_GET['tahun'] : date('Y')) . '&id_user=' . (isset($_GET['id_user']) ? $_GET['id_user'] : '')) ?>"
                class="btn btn-success btn-sm rounded-pill px-3 shadow-sm mr-1">
                <i class="fa-solid fa-file-excel mr-1"></i> Export Excel
            </a>
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-toggle="modal"
                data-target="#modalTambah">
                <i class="fa-solid fa-plus mr-1"></i> Tambah Lembur
            </button>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover table-striped text-center align-middle" id="tabelLembur"
                style="width:100%;">
                <thead class="bg-light text-secondary font-weight-bold">
                    <tr class="align-middle">
                        <th class="text-center" style="width: 5%;">No</th>
                        <th class="text-left pl-3" style="width: 18%;">Nama Pegawai</th>
                        <th class="text-center" style="width: 12%;">Tanggal</th>
                        <th class="text-left pl-3" style="width: 25%;">Uraian</th>
                        <th class="text-center" style="width: 15%;">Bukti</th>
                        <th class="text-left pl-3">Tindak Lanjut</th>
                        <th class="text-center" style="width: 10%;">Target</th>
                        <th class="text-center" style="width: 12%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($lemburs)) : ?>
                    <?php $no = 1; foreach ($lemburs as $l) : ?>
                    <tr class="align-middle">
                        <td class="text-center"><?= $no++; ?></td>

                        <td class="text-left pl-3">
                            <strong><?= esc($l['nama_lengkap'] ?? $l['nama_pegawai'] ?? 'Tidak Diketahui'); ?></strong>
                        </td>

                        <td class="text-center"><?= date('d-m-Y', strtotime($l['tanggal'])); ?></td>
                        <td class="text-left pl-3"><?= esc($l['uraian']); ?></td>

                        <td class="text-center">
                            <?php 
                        if ($l['bukti']): 
                            $arrayBukti = json_decode($l['bukti'], true);
                            if (!is_array($arrayBukti)) {
                                $arrayBukti = [$l['bukti']];
                            }
                            foreach ($arrayBukti as $index => $namaFoto):
                        ?>
                            <button type="button"
                                class="btn btn-sm btn-outline-info btn-preview-bukti d-inline-flex align-items-center justify-content-center m-1"
                                style="width: 32px; height: 32px; border-radius: 8px;" data-bukti="<?= $namaFoto; ?>"
                                title="Lihat Bukti Ke-<?= $index + 1; ?>">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <?php 
                            endforeach;
                        else: 
                        ?>
                            <span class="text-muted font-weight-bold">-</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-left pl-3"><?= esc($l['tindak_lanjut']); ?></td>
                        <td class="text-center font-weight-bold text-primary"><?= esc($l['target']); ?>%</td>

                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-sm text-white rounded-circle btn-edit mr-1"
                                style="width: 32px; height: 32px; padding: 0;" data-id="<?= $l['id'] ?>"
                                data-tanggal="<?= $l['tanggal'] ?>" data-uraian="<?= esc($l['uraian']) ?>"
                                data-tl="<?= esc($l['tindak_lanjut']) ?>" data-target="<?= $l['target'] ?>"
                                title="Edit Data">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm rounded-circle btn-hapus"
                                style="width: 32px; height: 32px; padding: 0;" data-id="<?= $l['id'] ?>"
                                title="Hapus Data">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL TAMBAH, EDIT, & PREVIEW (TETAP SAMA)
     ========================================== -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header text-white py-3" style="background: #00875A; border: none;">
                <h5 class="modal-title d-flex align-items-center font-weight-bold">
                    <i class="fas fa-plus-circle mr-2"></i> Tambah Data Lembur Baru
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                    style="opacity: 1; outline: none;">
                    <span aria-hidden="true" style="font-size: 1.8rem; line-height: 1;">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('admin/lembur/save'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <div class="modal-body p-4" style="background-color: #f8f9fa;">
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-secondary mb-2"><i
                                class="fas fa-calendar-alt text-success mr-2"></i> Tanggal Pelaksanaan</label>
                        <input type="date" name="tanggal" class="form-control px-3"
                            style="border-radius: 8px; height: 40px;" required value="<?= date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-secondary mb-2"><i
                                class="fas fa-briefcase text-success mr-2"></i> Uraian Pekerjaan</label>
                        <textarea name="uraian" class="form-control px-3 py-2" rows="4"
                            style="border-radius: 8px; resize: none;"
                            placeholder="Tuliskan secara detail aktivitas yang dikerjakan saat lembur..."
                            required></textarea>
                    </div>
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-secondary mb-2"><i
                                class="fas fa-tasks text-success mr-2"></i> Tindak Lanjut / Capaian</label>
                        <textarea name="tindak_lanjut" class="form-control px-3 py-2" rows="3"
                            style="border-radius: 8px; resize: none;"
                            placeholder="Hasil nyata atau output yang diperoleh dari pekerjaan ini..."
                            required></textarea>
                    </div>
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-secondary mb-2"><i
                                class="fas fa-bullseye text-success mr-2"></i> Target Capaian (%)</label>
                        <div class="input-group">
                            <input type="number" name="target" class="form-control px-3" min="0" max="100"
                                style="border-top-left-radius: 8px; border-bottom-left-radius: 8px; height: 40px;"
                                placeholder="Contoh: 85" required>
                            <div class="input-group-append">
                                <span class="input-group-text bg-white font-weight-bold text-secondary"
                                    style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-secondary mb-2"><i
                                class="fas fa-paperclip text-success mr-2"></i> Dokumen Bukti Fisik (Bisa Pilih > 1
                            Foto)</label>
                        <input type="file" name="bukti[]" class="form-control-file p-1" accept="image/*" multiple
                            required>
                        <small class="text-muted d-block mt-1"><i class="fas fa-info-circle mr-1"></i> Menyeleksi lebih
                            dari 1 foto sekaligus dapat dilakukan saat menekan tombol pilih.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-white py-3">
                    <button type="button" class="btn btn-light px-4 py-2 font-weight-bold rounded-pill text-secondary"
                        data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 py-2 font-weight-bold rounded-pill shadow-sm"><i
                            class="fas fa-save mr-1"></i> Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header text-white py-3" style="background: #007bff; border: none;">
                <h5 class="modal-title d-flex align-items-center font-weight-bold">
                    <i class="fas fa-edit mr-2"></i> Perbarui Data Lembur
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                    style="opacity: 1; outline: none;">
                    <span aria-hidden="true" style="font-size: 1.8rem; line-height: 1;">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('admin/lembur/update'); ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4" style="background-color: #f8f9fa;">
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-secondary mb-2"><i
                                class="fas fa-calendar-alt text-primary mr-2"></i> Perbarui Tanggal</label>
                        <input type="date" name="tanggal" id="edit_tanggal" class="form-control px-3"
                            style="border-radius: 8px; height: 40px;" required>
                    </div>
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-secondary mb-2"><i
                                class="fas fa-briefcase text-primary mr-2"></i> Perbarui Uraian Pekerjaan</label>
                        <textarea name="uraian" id="edit_uraian" class="form-control px-3 py-2" rows="4"
                            style="border-radius: 8px; resize: none;" required></textarea>
                    </div>
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-secondary mb-2"><i
                                class="fas fa-tasks text-primary mr-2"></i> Perbarui Tindak Lanjut</label>
                        <textarea name="tindak_lanjut" id="edit_tindak_lanjut" class="form-control px-3 py-2" rows="3"
                            style="border-radius: 8px; resize: none;" required></textarea>
                    </div>
                    <div class="form-group mb-4">
                        <label class="font-weight-bold text-secondary mb-2"><i
                                class="fas fa-bullseye text-primary mr-2"></i> Perbarui Target Capaian (%)</label>
                        <div class="input-group">
                            <input type="number" name="target" id="edit_target" class="form-control px-3" min="0"
                                max="100"
                                style="border-top-left-radius: 8px; border-bottom-left-radius: 8px; height: 40px;"
                                required>
                            <div class="input-group-append">
                                <span class="input-group-text bg-white font-weight-bold text-secondary"
                                    style="border-top-right-radius: 8px; border-bottom-right-radius: 8px;">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label class="font-weight-bold text-secondary mb-2"><i
                                class="fas fa-paperclip text-primary mr-2"></i> Ganti Dokumen Bukti Fisik <span
                                class="text-muted font-weight-normal">(Opsional)</span></label>
                        <input type="file" name="bukti[]" class="form-control-file p-1" accept="image/*" multiple>
                        <small class="text-muted d-block mt-1"><i class="fas fa-info-circle mr-1"></i> Biarkan kosong
                            if tidak ingin mengubah kumpulan foto bukti lama Anda.</small>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-white py-3">
                    <button type="button" class="btn btn-light px-4 py-2 font-weight-bold rounded-pill text-secondary"
                        data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 py-2 font-weight-bold rounded-pill shadow-sm"><i
                            class="fas fa-sync mr-1"></i> Perbarui Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPreview" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-info text-white py-3" style="border: none;">
                <h5 class="modal-title d-flex align-items-center font-weight-bold">
                    <i class="fas fa-image mr-2"></i> Bukti Fisik Lembur
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                    style="opacity: 1; outline: none;">
                    <span aria-hidden="true" style="font-size: 1.8rem; line-height: 1;">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-4" style="background-color: #f8f9fa;">
                <img src="" id="framePreview" class="img-fluid rounded shadow-sm border bg-white" alt="Pratinjau Bukti"
                    style="max-height: 420px; object-fit: contain;">
            </div>
            <div class="modal-footer border-0 bg-white py-2">
                <button type="button" class="btn btn-light px-4 py-2 font-weight-bold rounded-pill text-secondary"
                    data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // 1. Inisialisasi DataTables Terkalibrasi Standar Bootstrap 4
    if ($.fn.DataTable.isDataTable('#tabelLembur')) {
        $('#tabelLembur').DataTable().destroy();
    }
    $('#tabelLembur').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": true,
        "language": {
            "emptyTable": "Belum ada data rekap kerja lembur.",
            "search": "Cari data:",
            "lengthMenu": "Tampilkan _MENU_ entri"
        }
    });

    // 2. SweetAlert2 Pop-up Sukses Menggantikan Notifikasi Flashdata
    <?php if (session()->getFlashdata('success') || session()->getFlashdata('sukses')) : ?>
    Swal.fire({
        title: 'Berhasil!',
        text: '<?= session()->getFlashdata('success') ?: session()->getFlashdata('sukses') ?>',
        icon: 'success',
        confirmButtonColor: '#00875A',
        timer: 2500,
        showConfirmButton: false
    });
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')) : ?>
    Swal.fire({
        title: 'Gagal!',
        text: '<?= session()->getFlashdata('error') ?>',
        icon: 'error',
        confirmButtonColor: '#d33',
        timer: 2500,
        showConfirmButton: false
    });
    <?php endif; ?>

    // 3. Event Handling Modal Edit Delegasi JQuery
    $('#tabelLembur').on('click', '.btn-edit', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_tanggal').val($(this).data('tanggal'));
        $('#edit_uraian').val($(this).data('uraian'));
        $('#edit_tindak_lanjut').val($(this).data('tl'));
        $('#edit_target').val($(this).data('target'));

        $('#modalEdit').modal('show');
    });

    // 4. Preview Gambar Dokumen Bukti Fisik
    $('#tabelLembur').on('click', '.btn-preview-bukti', function() {
        var namaFile = $(this).data('bukti');
        var urlGambar = "<?= base_url('uploads/bukti/'); ?>/" + namaFile;
        $('#framePreview').attr('src', urlGambar);
        $('#modalPreview').modal('show');
    });

    // 5. Konfirmasi Penghapusan Data Interaktif SweetAlert2 Tengah Layar
    $('#tabelLembur').on('click', '.btn-hapus', function(e) {
        e.preventDefault();

        var id = $(this).data('id');
        var deleteUrl = "<?= base_url('admin/lembur/delete/') ?>/" + id;

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data lembur ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = deleteUrl;
            }
        });
    });
});
</script>
<?= $this->endSection() ?>