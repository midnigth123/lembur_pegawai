<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Lembur SIMRS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        font-family: 'Nunito', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        height: 100vh;
    }

    .card-login {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(160, 175, 190, 0.2);
        overflow: hidden;
        background: #ffffff;
    }

    .form-control {
        border-radius: 10px;
        padding: 12px 15px;
        height: auto;
        border: 1.5px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.15);
    }

    .btn-login {
        border-radius: 10px;
        padding: 12px;
        font-weight: 700;
        background: #4e73df;
        border: none;
        transition: all 0.3s;
    }

    .btn-login:hover {
        background: #2e59d9;
        transform: translateY(-1px);
        box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
    }

    .input-group-text {
        border-radius: 0 10px 10px 0;
        background-color: transparent;
        border: 1.5px solid #e2e8f0;
        border-left: none;
        cursor: pointer;
    }

    .has-icon .form-control {
        border-radius: 10px 0 0 10px;
    }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card card-login p-4 p-md-5">

                    <div class="text-center mb-4">
                        <div class="bg-light d-inline-flex align-items-center justify-content-center rounded-circle text-primary mb-3"
                            style="width: 65px; height: 65px;">
                            <i class="fa-solid fa-user-lock fa-2x"></i>
                        </div>
                        <h4 class="font-weight-bold text-dark mb-1">E-LEMBUR</h4>
                        <p class="text-muted small">Sistem Rekapitulasi Kerja Lembur Multi-Unit</p>
                    </div>

                    <?php if (session()->getFlashdata('error')) : ?>
                    <div class="alert alert-danger alert-dismissible fade show rounded-lg small" role="alert">
                        <i class="fa-solid fa-circle-exclamation mr-2"></i> <?= session()->getFlashdata('error'); ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <form action="<?= base_url('auth/validasi'); ?>" method="POST">
                        <?= csrf_field(); ?>

                        <div class="form-group mb-4">
                            <label class="text-secondary small font-weight-bold">Username</label>
                            <input type="text" name="username" class="form-control" placeholder="Masukkan username anda"
                                required autocomplete="username" autofocus>
                        </div>

                        <div class="form-group mb-4">
                            <label class="text-secondary small font-weight-bold">Password</label>
                            <div class="input-group has-icon">
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="••••••••" required autocomplete="current-password">
                                <div class="input-group-append" onclick="togglePassword()">
                                    <span class="input-group-text text-muted" id="icon-mata">
                                        <i class="fa-solid fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button type="submit"
                            class="btn btn-primary btn-block btn-login text-uppercase tracking-wide mt-4">
                            Masuk Sistem <i class="fa-solid fa-right-to-bracket ml-1"></i>
                        </button>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    // Fungsi Toggle Intip Password
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const iconMata = document.getElementById('icon-mata').querySelector('i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            iconMata.classList.remove('fa-eye');
            iconMata.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            iconMata.classList.remove('fa-eye-slash');
            iconMata.classList.add('fa-eye');
        }
    }
    </script>
</body>

</html>