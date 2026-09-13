<!doctype html>
<html lang="id" class="layout-wide customizer-hide" dir="ltr" data-theme="theme-default" data-assets-path="<?= esc($baseUrl) ?>/assets/">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title><?= esc($title) ?> | MTsN 4 Jombang</title>
    <?= $this->include('layout_favicon') ?>
    <link rel="stylesheet" href="<?= esc($baseUrl) ?>/assets/vendor/fonts/iconify-icons.css">
    <link rel="stylesheet" href="<?= esc($baseUrl) ?>/assets/vendor/css/core.css">
    <link rel="stylesheet" href="<?= esc($baseUrl) ?>/assets/css/demo.css">
    <script src="<?= esc($baseUrl) ?>/assets/vendor/js/helpers.js"></script>
    <script src="<?= esc($baseUrl) ?>/assets/js/config.js"></script>
</head>
<body>
<div class="container-xxl py-5 min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card w-100" style="max-width:520px">
        <div class="card-body text-center p-5">
            <?php if ($penjual): ?>
                <div class="avatar avatar-xl mx-auto mb-4">
                    <span class="avatar-initial rounded-circle bg-label-<?= $penjual['status_aktif'] === 'Aktif' ? 'success' : 'secondary' ?>">
                        <i class="icon-base bx bx-check-shield fs-1"></i>
                    </span>
                </div>
                <h4 class="mb-2">Kartu Anggota Terverifikasi</h4>
                <p class="text-body-secondary mb-5">Data ini hanya digunakan untuk memastikan penjual terdaftar resmi di kantin madrasah.</p>

                <div class="border rounded p-4 text-start">
                    <div class="mb-3"><small class="text-body-secondary d-block">Nama Penjual</small><strong><?= esc($penjual['nama_penjual']) ?></strong></div>
                    <div class="mb-3"><small class="text-body-secondary d-block">Golongan</small><strong><?= esc($penjual['nama_golongan'] ?? '-') ?></strong></div>
                    <div><small class="text-body-secondary d-block">Status</small><span class="badge bg-label-<?= $penjual['status_aktif'] === 'Aktif' ? 'success' : 'secondary' ?>"><?= esc($penjual['status_aktif']) ?></span></div>
                </div>
            <?php else: ?>
                <div class="avatar avatar-xl mx-auto mb-4">
                    <span class="avatar-initial rounded-circle bg-label-danger"><i class="icon-base bx bx-x-circle fs-1"></i></span>
                </div>
                <h4 class="mb-2">Kartu Tidak Valid</h4>
                <p class="text-body-secondary mb-0">Kode verifikasi tidak dikenali atau kartu sudah tidak terdaftar.</p>
            <?php endif; ?>

            <div class="mt-5 small text-body-secondary">Aplikasi Iuran Kantin &middot; MTsN 4 Jombang</div>
        </div>
    </div>
</div>
<script src="<?= esc($baseUrl) ?>/assets/vendor/libs/jquery/jquery.js"></script>
<script src="<?= esc($baseUrl) ?>/assets/vendor/libs/popper/popper.js"></script>
<script src="<?= esc($baseUrl) ?>/assets/vendor/js/bootstrap.js"></script>
</body>
</html>
