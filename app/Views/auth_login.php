<!doctype html>
<html lang="id" class="layout-wide customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="<?= esc($baseUrl) ?>/assets/">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title><?= esc($title) ?> | Aplikasi Iuran Kantin MTsN 4 Jombang</title>
    <meta name="description" content="Aplikasi pencatatan iuran penjual kantin MTsN 4 Jombang">

    <?= $this->include('layout_favicon') ?>
    <link rel="stylesheet" href="<?= esc($baseUrl) ?>/assets/vendor/fonts/iconify-icons.css">
    <link rel="stylesheet" href="<?= esc($baseUrl) ?>/assets/vendor/css/core.css">
    <link rel="stylesheet" href="<?= esc($baseUrl) ?>/assets/css/demo.css">
    <link rel="stylesheet" href="<?= esc($baseUrl) ?>/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css">
    <link rel="stylesheet" href="<?= esc($baseUrl) ?>/assets/vendor/css/pages/page-auth.css">

    <script src="<?= esc($baseUrl) ?>/assets/vendor/js/helpers.js"></script>
    <script src="<?= esc($baseUrl) ?>/assets/js/config.js"></script>
</head>
<body>
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner">
                <div class="card px-sm-6 px-0">
                    <div class="card-body">
                        <div class="app-brand justify-content-center mb-6">
                            <span class="app-brand-link gap-2">
                                <span class="app-brand-logo demo text-primary">
                                    <i class="icon-base bx bx-store-alt fs-2"></i>
                                </span>
                                <span class="app-brand-text demo text-heading fw-bold">Iuran Kantin</span>
                            </span>
                        </div>

                        <h4 class="mb-1">MTsN 4 Jombang</h4>
                        <p class="mb-6">Silakan masuk untuk mengakses aplikasi pencatatan iuran kantin.</p>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger" role="alert">
                                <?= esc(session()->getFlashdata('error')) ?>
                            </div>
                        <?php endif; ?>

                        <?php $errors = session()->getFlashdata('errors') ?? []; ?>
                        <?php if ($errors): ?>
                            <div class="alert alert-danger" role="alert">
                                <ul class="mb-0 ps-3">
                                    <?php foreach ($errors as $message): ?>
                                        <li><?= esc($message) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form class="mb-4" action="<?= esc($baseUrl) ?>/login" method="post" autocomplete="off">
                            <?= csrf_field() ?>

                            <div class="mb-4">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?= esc(old('username')) ?>" placeholder="Masukkan username" autocomplete="username" autofocus required>
                            </div>

                            <div class="mb-6 form-password-toggle">
                                <label class="form-label" for="password">Password</label>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password" class="form-control" name="password" placeholder="••••••••••••" autocomplete="current-password" required>
                                    <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                                </div>
                            </div>

                            <button class="btn btn-primary d-grid w-100" type="submit">
                                <i class="icon-base bx bx-log-in-circle me-2"></i>Masuk
                            </button>
                        </form>

                        <p class="text-center mb-0 small text-body-secondary">
                            Aplikasi Iuran Kantin &middot; MTsN 4 Jombang
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= esc($baseUrl) ?>/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="<?= esc($baseUrl) ?>/assets/vendor/libs/popper/popper.js"></script>
    <script src="<?= esc($baseUrl) ?>/assets/vendor/js/bootstrap.js"></script>
    <script src="<?= esc($baseUrl) ?>/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
    <script src="<?= esc($baseUrl) ?>/assets/vendor/js/menu.js"></script>
    <script src="<?= esc($baseUrl) ?>/assets/js/main.js"></script>
</body>
</html>
