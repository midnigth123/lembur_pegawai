<?= $this->extend('layout/admin_layout') ?>
<?= $this->section('title') ?>Master Unit Kerja<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-2">
    <div class="row">
        <div class="col-md-12">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 font-weight-bold text-dark">
                        <i class="fas fa-hospital-alt text-primary mr-2"></i> Master Daftar Unit Kerja
                    </h5>
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-toggle="modal"
                        data-target="#modalTambah">
                        <i class="fas fa-plus mr-1"></i> Tambah Unit Baru
                    </button>
                </div>

                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped text-center align-middle" id="tabelUnit"
                            style="width: 100%;">
                            <thead class="bg-light text-secondary font-weight-bold">
                                <tr>
                                    <th style="width: 10%">ID UNIT</th>
                                    <th class="text-left pl-4">NAMA UNIT KERJA</th>
                                    <th style="width: 15%">STATUS</th>
                                    <th style="width: 20%">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($units)) : ?>
                                <?php foreach ($units as $u) : ?>
                                <tr>
                                    <td><span class="badge badge-secondary px-2 py-1">#<?= $u['id'] ?></span></td>
                                    <td class="font-weight-bold text-left pl-4 text-dark"><?= esc($u['nama_unit']) ?>
                                    </td>
                                    <td>
                                        <?php if ($u['status'] === 'aktif') : ?>
                                        <span class="badge badge-success px-2 py-1 rounded-pill">Aktif</span>
                                        <?php else : ?>
                                        <span class="badge badge-secondary px-2 py-1 rounded-pill">Tidak Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button"
                                            class="btn btn-warning btn-sm text-white rounded-circle btn-edit mr-1 shadow-sm"
                                            style="width: 32px; height: 32px; padding: 0;" data-id="<?= $u['id'] ?>"
                                            data-nama="<?= esc($u['nama_unit']) ?>" data-status="<?= $u['status'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button"
                                            data-url="<?= base_url('admin/master_unit/delete/' . $u['id']) ?>"
                                            class="btn btn-danger btn-sm rounded-circle shadow-sm btn-hapus"
                                            style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-trash"></i>
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

        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-primary text-white" style="border: none;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2"></i>Tambah Unit Kerja</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                    style="opacity: 1; outline: none;">
                    <span aria-hidden="true" style="font-size: 1.8rem; line-height: 1;">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('admin/master_unit/save') ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body p-4" style="background-color: #f8f9fa;">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Nama Unit Kerja</label>
                        <input type="text" name="nama_unit" class="form-control px-3"
                            style="border-radius: 8px; height: 40px;" placeholder="Contoh: LOGISTIK ATK" required>
                    </div>
                    <div class="form-group mb-1">
                        <label class="font-weight-bold text-secondary">Status Operasional</label>
                        <select name="status" class="form-control px-3"
                            style="border-radius: 8px; height: 40px; cursor: pointer;">
                            <option value="aktif">Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-white py-3">
                    <button type="button" class="btn btn-light px-4 py-2 font-weight-bold rounded-pill text-secondary"
                        data-dismiss="modal">Batal</button>
                    <button type="submit"
                        class="btn btn-primary px-4 py-2 font-weight-bold rounded-pill shadow-sm">Simpan Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-warning text-white" style="border: none;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Ubah Nama Unit Kerja</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                    style="opacity: 1; outline: none;">
                    <span aria-hidden="true" style="font-size: 1.8rem; line-height: 1;">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('admin/master_unit/update') ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body p-4" style="background-color: #f8f9fa;">
                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-secondary">Nama Unit Kerja Baru</label>
                        <input type="text" name="nama_unit" id="edit-nama" class="form-control px-3"
                            style="border-radius: 8px; height: 40px;" required>
                    </div>
                    <div class="form-group mb-1">
                        <label class="font-weight-bold text-secondary">Status Operasional</label>
                        <select name="status" id="edit-status" class="form-control px-3"
                            style="border-radius: 8px; height: 40px; cursor: pointer;">
                            <option value="aktif">Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-white py-3">
                    <button type="button" class="btn btn-light px-4 py-2 font-weight-bold rounded-pill text-secondary"
                        data-dismiss="modal">Batal</button>
                    <button type="submit"
                        class="btn btn-warning text-white rounded-pill px-4 py-2 font-weight-bold shadow-sm">Simpan
                        Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    // 1. Inisialisasi DataTables
    if ($.fn.DataTable.isDataTable('#tabelUnit')) {
        $('#tabelUnit').DataTable().destroy();
    }
    $('#tabelUnit').DataTable({
        "language": {
            "search": "Cari data:",
            "lengthMenu": "Tampilkan _MENU_ entri",
            "emptyTable": "Belum ada data unit kerja terdaftar."
        }
    });

    // 2. Notifikasi Sukses SweetAlert2
    <?php if (session()->getFlashdata('sukses')) : ?>
    Swal.fire({
        title: 'Berhasil!',
        text: '<?= session()->getFlashdata('sukses') ?>',
        icon: 'success',
        confirmButtonColor: '#00875A',
        timer: 2500,
        showConfirmButton: false
    });
    <?php endif; ?>

    // 3. Event Delegation Pengisian Modal Edit (Terintegrasi data-status)
    $('#tabelUnit').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const status = $(this).data('status');

        $('#edit-id').val(id);
        $('#edit-nama').val(nama);
        $('#edit-status').val(status); // Set dropdown otomatis memilih status asli database

        $('#modalEdit').modal('show');
    });

    // 4. Konfirmasi Penghapusan Data
    $('#tabelUnit').on('click', '.btn-hapus', function(e) {
        e.preventDefault();
        const urlHapus = $(this).data('url');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Unit kerja ini akan dihapus permanen dari sistem master!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = urlHapus;
            }
        });
    });
});
</script>
<?= $this->endSection() ?>