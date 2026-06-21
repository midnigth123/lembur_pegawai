<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?> | E-Lembur</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.0/dist/sweetalert2.min.css">

    <style>
    body {
        background-color: #f4f7f6;
        font-family: 'Inter', sans-serif;
        color: #333;
    }

    /* Sidebar Styling - Soft & Clean */
    .sidebar {
        width: 260px;
        height: 100vh;
        position: fixed;
        background: #ffffff;
        border-right: 1px solid #eef0f2;
        z-index: 1000;
    }

    .sidebar-brand {
        padding: 30px 25px;
        font-weight: 700;
        color: #00875A;
        font-size: 1.4rem;
        letter-spacing: -0.5px;
    }

    .nav-link {
        color: #6c757d;
        padding: 15px 25px;
        margin: 5px 15px;
        border-radius: 12px;
        transition: 0.3s;
        font-weight: 500;
        display: flex;
        align-items: center;
    }

    .nav-link:hover {
        background: #f8f9fa;
        color: #00875A;
        text-decoration: none;
    }

    .nav-link.active {
        background: #00875A !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(0, 135, 90, 0.15);
    }

    /* Top Navbar Modern */
    .navbar-custom {
        background: #ffffff;
        padding: 15px 30px;
        border-radius: 16px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.02);
        margin-bottom: 30px;
        border: 1px solid #eef0f2;
        display: flex;
        align-items: center;
        justify-content: flex-end;
    }

    /* Info Jam Box */
    .clock-box {
        background: #f8f9fa;
        padding: 8px 15px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #495057;
        border: 1px solid #e9ecef;
    }

    /* Main Container Content */
    .main-content {
        margin-left: 260px;
        padding: 30px;
    }

    .content-card {
        background: #ffffff;
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.03);
        border: 1px solid #f1f3f4;
    }

    /* CETAKAN PAKSA SWEETALERT2: Mengunci posisi pop-up di tengah layar & berbentuk bulat halus */
    .swal2-container {
        z-index: 99999 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    .swal2-popup {
        border-radius: 20px !important;
        font-family: 'Inter', sans-serif !important;
        padding: 2rem !important;
    }

    .swal2-styled {
        border-radius: 50px !important;
        font-weight: 600 !important;
        padding: 0.6rem 2rem !important;
    }
    </style>
</head>

<body>

    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-clock-rotate-left mr-2"></i> E-Lembur
        </div>

        <div class="nav flex-column mt-4">
            <a href="<?= base_url('admin/lembur') ?>"
                class="nav-link <?= (strpos(current_url(), base_url('admin/lembur')) !== false) ? 'active' : '' ?>">
                <i class="fa-solid fa-table-list mr-3"></i>Rekap Lembur
            </a>
            <?php if (session()->get('role') === 'superadmin') : ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/master_users') ?>">
                    <i class="fas fa-user-tie mr-2"></i>Users
                </a>
            </li>
            <?php endif; ?>
            <?php if (session()->get('role') === 'superadmin') : ?>
            <li class="nav-item">
                <a class="nav-link" href="<?= base_url('admin/master_unit') ?>">
                    <i class="fas fa-book mr-2"></i>Unit
                </a>
            </li>
            <?php endif; ?>
        </div>
    </div>

    <div class="main-content">
        <div class="navbar-custom">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div class="clock-box mr-3" id="liveClock">Memuat...</div>

                <div class="border-left pl-3 d-flex align-items-center">
                    <div class="d-flex flex-column text-right mr-3">
                        <span class="font-weight-bold text-dark" style="line-height: 1.2;">
                            <?= session()->get('nama_lengkap') ?? 'Pengguna'; ?>
                            <a href="#" data-toggle="modal" data-target="#modalEditProfil" class="text-primary ml-1"
                                title="Edit Profil">
                                <i class="fas fa-user-cog small"></i>
                            </a>
                        </span>
                        <small class="text-muted font-weight-600">
                            <i
                                class="fas fa-hospital-alt mr-1 small"></i><?= session()->get('unit_kerja') ?? 'Luar Unit'; ?>
                        </small>
                    </div>

                    <a href="#" class="btn btn-danger" data-toggle="modal" data-target="#logoutModal"
                        style="border-radius: 10px;">
                        <i class="fas fa-sign-out-alt mr-2"></i> Keluar
                    </a>
                </div>
            </div>
        </div>

        <div class="content-card">
            <?= $this->renderSection('content') ?>
        </div>
    </div>

    <div class="modal fade" id="modalEditProfil" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow" style="border-radius: 20px; overflow: hidden;">
                <div class="modal-header bg-dark text-white" style="border: none;">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-user-edit mr-2"></i>Pengaturan Profil Anda
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"
                        style="opacity: 1; outline: none;">
                        <span aria-hidden="true" style="font-size: 1.8rem; line-height: 1;">&times;</span>
                    </button>
                </div>
                <form action="<?= base_url('admin/profil/update_mandiri') ?>" method="post">
                    <?= csrf_field(); ?>
                    <div class="modal-body p-4" style="background-color: #f8f9fa;">

                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-secondary small">NAMA LENGKAP</label>
                            <input type="text" name="nama_lengkap" class="form-control px-3"
                                style="border-radius: 8px; height: 42px; border: 1.5px solid #e2e8f0;"
                                value="<?= session()->get('nama_lengkap'); ?>" required>
                        </div>

                        <div class="form-group mb-1">
                            <label class="font-weight-bold text-secondary small">PASSWORD BARU</label>
                            <input type="password" name="password_baru" class="form-control px-3"
                                style="border-radius: 8px; height: 42px; border: 1.5px solid #e2e8f0;"
                                placeholder="Kosongkan jika tidak ingin mengubah password">
                            <small class="form-text text-muted mt-1" style="font-size: 0.75rem;">
                                *Masukkan Password Baru.
                            </small>
                        </div>

                    </div>
                    <div class="modal-footer border-0 bg-white py-3">
                        <button type="button"
                            class="btn btn-light px-4 py-2 font-weight-bold rounded-pill text-secondary"
                            data-dismiss="modal">Batal</button>
                        <button type="submit"
                            class="btn btn-dark px-4 py-2 font-weight-bold rounded-pill shadow-sm">Simpan
                            Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="logoutModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document" style="max-width: 400px;">
            <div class="modal-content"
                style="border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">

                <div class="modal-body text-center p-4">
                    <div class="bg-light d-inline-flex align-items-center justify-content-center rounded-circle text-warning mb-3"
                        style="width: 70px; height: 70px;">
                        <i class="fa-solid fa-triangle-exclamation fa-2x"></i>
                    </div>

                    <h5 class="font-weight-bold text-dark mb-2">Konfirmasi Keluar</h5>
                    <p class="text-muted small px-3">Apakah Anda yakin ingin keluar dari sistem E-Lembur? Semua sesi
                        aktif Anda akan diakhiri.</p>

                    <div class="d-flex justify-content-center mt-4">
                        <button type="button" class="btn btn-light text-secondary font-weight-bold mx-2"
                            data-dismiss="modal" style="border-radius: 10px; padding: 10px 25px; min-width: 110px;">
                            Batal
                        </button>
                        <a href="<?= base_url('logout') ?>" class="btn btn-danger font-weight-bold mx-2"
                            style="border-radius: 10px; padding: 10px 25px; min-width: 110px; background-color: #dc3545; border: none; box-shadow: 0 4px 10px rgba(220, 53, 69, 0.2);">
                            Keluar <i class="fa-solid fa-right-from-bracket ml-1"></i>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    function updateClock() {
        const now = new Date();
        const time = now.toLocaleTimeString('id-ID');
        const date = now.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });
        document.getElementById('liveClock').innerHTML = `${date} • ${time}`;
    }
    setInterval(updateClock, 1000);
    updateClock();
    </script>

    <?= $this->renderSection('scripts') ?>
</body>

</html>