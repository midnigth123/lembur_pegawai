<?= $this->extend('layout/admin_layout') ?>
<?= $this->section('title') ?>Master Manajemen Pengguna<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-2">
    <div class="row">
        <div class="col-md-12">

            <!-- Card Utama -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="card-title mb-0 font-weight-bold text-dark">
                        <i class="fas fa-users text-primary mr-2"></i> Master Data Pengguna Aplikasi
                    </h5>
                    <!-- Tombol Memicu Modal Tambah -->
                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-toggle="modal"
                        data-target="#modalTambah">
                        <i class="fas fa-user-plus mr-1"></i> Tambah Pengguna Baru
                    </button>
                </div>

                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped text-center align-middle" id="tabelUsers"
                            style="width: 100%;">
                            <thead class="bg-light text-secondary font-weight-bold">
                                <tr>
                                    <th style="width: 5%">NO</th>
                                    <th class="text-left pl-3">NAMA LENGKAP</th>
                                    <th>USERNAME</th>
                                    <th>UNIT KERJA</th>
                                    <th>ROLE HAK AKSES</th>
                                    <th>STATUS</th>
                                    <th style="width: 12%">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)) : ?>
                                <?php $no = 1; foreach ($users as $u) : ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td class="text-left pl-3 font-weight-bold text-dark"><?= esc($u['nama_lengkap']) ?>
                                    </td>
                                    <td><code class="text-secondary"><?= esc($u['username']) ?></code></td>
                                    <td><span
                                            class="badge badge-light border px-2 py-1"><?= esc($u['unit_kerja']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($u['role'] == 'superadmin') : ?>
                                        <span class="badge badge-danger">Superadmin</span>
                                        <?php elseif ($u['role'] == 'admin') : ?>
                                        <span class="badge badge-warning text-white">Admin Pusat</span>
                                        <?php elseif ($u['role'] == 'pjsdm') : ?>
                                        <span class="badge badge-info">PJ SDM</span>
                                        <?php else : ?>
                                        <span class="badge badge-primary">Pegawai / Ka Unit</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($u['status'] == 'aktif') : ?>
                                        <span class="badge badge-success px-2 py-1 rounded-pill">Aktif</span>
                                        <?php else : ?>
                                        <span class="badge badge-secondary px-2 py-1 rounded-pill">Non-Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Tombol Edit (Membawa Data via Atribut `data-*`) -->
                                        <button type="button"
                                            class="btn btn-warning btn-sm text-white rounded-circle btn-edit mr-1 shadow-sm"
                                            style="width: 32px; height: 32px; padding: 0;" data-id="<?= $u['id'] ?>"
                                            data-username="<?= esc($u['username']) ?>"
                                            data-nama="<?= esc($u['nama_lengkap']) ?>" data-nip="<?= esc($u['nip']) ?>"
                                            data-unit="<?= esc($u['unit_kerja']) ?>" data-role="<?= $u['role'] ?>"
                                            data-status="<?= $u['status'] ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <!-- Tombol Hapus -->
                                        <button type="button"
                                            data-url="<?= base_url('admin/master_users/delete/' . $u['id']) ?>"
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

<!-- ========================================== -->
<!-- MODAL TAMBAH USER (MURNI BOOTSTRAP 4)      -->
<!-- ========================================== -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-primary text-white" style="border: none;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Tambah Pengguna Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                    style="opacity: 1; outline: none;">
                    <span aria-hidden="true" style="font-size: 1.8rem; line-height: 1;">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('admin/master_users/save') ?>" method="post">
                <?= csrf_field(); ?>
                <div class="modal-body p-4" style="background-color: #f8f9fa;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Nama Lengkap & Gelar</label>
                                <input type="text" name="nama_lengkap" class="form-control px-3"
                                    style="border-radius: 8px; height: 40px;" placeholder="Masukkan nama lengkap"
                                    required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">NIP / Nomor Pegawai</label>
                                <input type="text" name="nip" class="form-control px-3"
                                    style="border-radius: 8px; height: 40px;" placeholder="Isi '-' jika tidak ada">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Unit Kerja</label>
                                <input type="text" name="unit_kerja" class="form-control px-3"
                                    style="border-radius: 8px; height: 40px;" placeholder="Contoh: SIMRS, IGD, KEUANGAN"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Username Login</label>
                                <input type="text" name="username" class="form-control px-3"
                                    style="border-radius: 8px; height: 40px;" placeholder="Contoh: dyan_simrs" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Password</label>
                                <input type="password" name="password" class="form-control px-3"
                                    style="border-radius: 8px; height: 40px;" placeholder="Masukkan password akun"
                                    required>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-1">
                                        <label class="font-weight-bold text-secondary">Hak Akses (Role)</label>
                                        <select name="role" class="form-control px-3"
                                            style="border-radius: 8px; height: 40px; cursor: pointer;" required>
                                            <option value="pegawai">Pegawai / Ka Unit</option>
                                            <option value="pjsdm">PJ SDM</option>
                                            <option value="admin">Admin Pusat</option>
                                            <option value="superadmin">Superadmin</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-1">
                                        <label class="font-weight-bold text-secondary">Status Akun</label>
                                        <select name="status" class="form-control px-3"
                                            style="border-radius: 8px; height: 40px; cursor: pointer;">
                                            <option value="aktif">Aktif</option>
                                            <option value="tidak_aktif">Non-Aktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-white py-3">
                    <button type="button" class="btn btn-light px-4 py-2 font-weight-bold rounded-pill text-secondary"
                        data-dismiss="modal">Batal</button>
                    <button type="submit"
                        class="btn btn-primary px-4 py-2 font-weight-bold rounded-pill shadow-sm">Simpan
                        Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL EDIT USER (MURNI BOOTSTRAP 4)        -->
<!-- ========================================== -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header bg-warning text-white" style="border: none;">
                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Ubah Data Pengguna</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                    style="opacity: 1; outline: none;">
                    <span aria-hidden="true" style="font-size: 1.8rem; line-height: 1;">&times;</span>
                </button>
            </div>
            <form action="<?= base_url('admin/master_users/update') ?>" method="post">
                <?= csrf_field(); ?>
                <input type="hidden" name="id" id="edit-id">
                <div class="modal-body p-4" style="background-color: #f8f9fa;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Nama Lengkap & Gelar</label>
                                <input type="text" name="nama_lengkap" id="edit-nama" class="form-control px-3"
                                    style="border-radius: 8px; height: 40px;" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">NIP</label>
                                <input type="text" name="nip" id="edit-nip" class="form-control px-3"
                                    style="border-radius: 8px; height: 40px;">
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Unit Kerja</label>
                                <input type="text" name="unit_kerja" id="edit-unit" class="form-control px-3"
                                    style="border-radius: 8px; height: 40px;" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Username Login</label>
                                <input type="text" name="username" id="edit-username" class="form-control px-3"
                                    style="border-radius: 8px; height: 40px;" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary">Password Baru <small
                                        class="text-danger">(Kosongkan jika tidak diubah)</small></label>
                                <input type="password" name="password" class="form-control px-3"
                                    style="border-radius: 8px; height: 40px;"
                                    placeholder="Masukkan password baru jika ingin diganti">
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="form-group mb-1">
                                        <label class="font-weight-bold text-secondary">Hak Akses (Role)</label>
                                        <select name="role" id="edit-role" class="form-control px-3"
                                            style="border-radius: 8px; height: 40px; cursor: pointer;" required>
                                            <option value="pegawai">Pegawai / Ka Unit</option>
                                            <option value="pjsdm">PJ SDM</option>
                                            <option value="admin">Admin Pusat</option>
                                            <option value="superadmin">Superadmin</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-group mb-1">
                                        <label class="font-weight-bold text-secondary">Status Akun</label>
                                        <select name="status" id="edit-status" class="form-control px-3"
                                            style="border-radius: 8px; height: 40px; cursor: pointer;">
                                            <option value="aktif">Aktif</option>
                                            <option value="tidak_aktif">Non-Aktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
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
    if ($.fn.DataTable.isDataTable('#tabelUsers')) {
        $('#tabelUsers').DataTable().destroy();
    }
    $('#tabelUsers').DataTable({
        "language": {
            "search": "Cari data:",
            "lengthMenu": "Tampilkan _MENU_ entri",
            "emptyTable": "Belum ada data pengguna terdaftar."
        }
    });

    // 2. SweetAlert2 Notifikasi Sukses
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

    // 3. Event Delegation Pengisian Modal Edit
    $('#tabelUsers').on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        const username = $(this).data('username');
        const nama = $(this).data('nama');
        const nip = $(this).data('nip');
        const unit = $(this).data('unit');
        const role = $(this).data('role');
        const status = $(this).data('status');

        $('#edit-id').val(id);
        $('#edit-username').val(username);
        $('#edit-nama').val(nama);
        $('#edit-nip').val(nip);
        $('#edit-unit').val(unit);
        $('#edit-role').val(role);
        $('#edit-status').val(status);

        $('#modalEdit').modal('show');
    });

    // 4. Konfirmasi Hapus Data dengan SweetAlert2
    $('#tabelUsers').on('click', '.btn-hapus', function(e) {
        e.preventDefault();
        const urlHapus = $(this).data('url');

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Akun ini akan dihapus secara permanen dari sistem!",
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