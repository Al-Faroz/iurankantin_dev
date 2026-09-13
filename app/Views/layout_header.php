<?php
$baseUrl = rtrim((string) config('App')->baseURL, '/');
$role = (string) session()->get('role');
$namaUser = (string) session()->get('nama');
$segment1 = request()->getUri()->getSegment(1);
?>
<!doctype html>
<html lang="id" class="layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default" data-assets-path="<?= esc($baseUrl) ?>/assets/">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <title><?= esc($title ?? 'Aplikasi Iuran Kantin') ?> | MTsN 4 Jombang</title>
    <meta name="description" content="Aplikasi pencatatan iuran penjual kantin MTsN 4 Jombang">

    <?= $this->include('layout_favicon') ?>
    <link rel="stylesheet" href="<?= esc($baseUrl) ?>/assets/vendor/fonts/iconify-icons.css">
    <link rel="stylesheet" href="<?= esc($baseUrl) ?>/assets/vendor/css/core.css">
    <link rel="stylesheet" href="<?= esc($baseUrl) ?>/assets/css/demo.css">
    <link rel="stylesheet" href="<?= esc($baseUrl) ?>/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css">
    <?php if (! empty($useDataTables)): ?>
        <link rel="stylesheet" href="<?= esc($baseUrl) ?>/assets/vendor/libs/datatables/datatables.min.css">
    <?php endif; ?>

    <script src="<?= esc($baseUrl) ?>/assets/vendor/js/helpers.js"></script>
    <script src="<?= esc($baseUrl) ?>/assets/js/config.js"></script>
</head>
<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand demo">
                <a href="<?= esc($baseUrl) ?>/dashboard" class="app-brand-link">
                    <span class="app-brand-logo demo text-primary"><i class="icon-base bx bx-store-alt fs-2"></i></span>
                    <span class="app-brand-text demo menu-text fw-bold ms-2">Iuran Kantin</span>
                </a>
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
                    <i class="icon-base bx bx-chevron-left"></i>
                </a>
            </div>

            <div class="menu-divider mt-0"></div>
            <div class="menu-inner-shadow"></div>

            <ul class="menu-inner py-1">
                <li class="menu-item <?= $segment1 === 'dashboard' ? 'active' : '' ?>">
                    <a href="<?= esc($baseUrl) ?>/dashboard" class="menu-link">
                        <i class="menu-icon icon-base bx bx-home-smile"></i><div>Dashboard</div>
                    </a>
                </li>

                <?php if ($role === 'Operator'): ?>
                    <li class="menu-header small text-uppercase"><span class="menu-header-text">Master</span></li>
                    <li class="menu-item <?= $segment1 === 'penjual' ? 'active' : '' ?>"><a href="<?= esc($baseUrl) ?>/penjual" class="menu-link"><i class="menu-icon icon-base bx bx-store"></i><div>Penjual</div></a></li>
                    <li class="menu-item <?= $segment1 === 'golongan' ? 'active' : '' ?>"><a href="<?= esc($baseUrl) ?>/golongan" class="menu-link"><i class="menu-icon icon-base bx bx-category"></i><div>Golongan Penjual</div></a></li>
                    <li class="menu-item <?= $segment1 === 'kategori-pengeluaran' ? 'active' : '' ?>"><a href="<?= esc($baseUrl) ?>/kategori-pengeluaran" class="menu-link"><i class="menu-icon icon-base bx bx-purchase-tag"></i><div>Kategori Pengeluaran</div></a></li>

                    <li class="menu-header small text-uppercase"><span class="menu-header-text">Transaksi</span></li>
                    <li class="menu-item <?= $segment1 === 'iuran' ? 'active' : '' ?>"><a href="<?= esc($baseUrl) ?>/iuran" class="menu-link"><i class="menu-icon icon-base bx bx-wallet"></i><div>Input Iuran</div></a></li>
                    <li class="menu-item <?= $segment1 === 'pengeluaran' ? 'active' : '' ?>"><a href="<?= esc($baseUrl) ?>/pengeluaran" class="menu-link"><i class="menu-icon icon-base bx bx-receipt"></i><div>Pengeluaran</div></a></li>
                    <li class="menu-item <?= $segment1 === 'setoran' ? 'active' : '' ?>"><a href="<?= esc($baseUrl) ?>/setoran" class="menu-link"><i class="menu-icon icon-base bx bx-transfer"></i><div>Setoran Pimpinan</div></a></li>
                    <li class="menu-item <?= $segment1 === 'koreksi-transaksi' ? 'active' : '' ?>"><a href="<?= esc($baseUrl) ?>/koreksi-transaksi" class="menu-link"><i class="menu-icon icon-base bx bx-edit-alt"></i><div>Koreksi Transaksi</div></a></li>

                    <li class="menu-header small text-uppercase"><span class="menu-header-text">Kartu Kantin</span></li>
                    <li class="menu-item <?= $segment1 === 'kartu' ? 'active' : '' ?>"><a href="<?= esc($baseUrl) ?>/kartu" class="menu-link"><i class="menu-icon icon-base bx bx-id-card"></i><div>Kartu Anggota</div></a></li>
                    <li class="menu-item"><a href="<?= esc($baseUrl) ?>/kartu/scan" class="menu-link"><i class="menu-icon icon-base bx bx-qr-scan"></i><div>Scan Kartu</div></a></li>
                <?php endif; ?>

                <li class="menu-header small text-uppercase"><span class="menu-header-text">Laporan</span></li>
                <li class="menu-item <?= $segment1 === 'laporan' ? 'active' : '' ?>"><a href="<?= esc($baseUrl) ?>/laporan/iuran" class="menu-link"><i class="menu-icon icon-base bx bx-line-chart"></i><div>Laporan</div></a></li>

                <?php if ($role === 'Operator'): ?>
                    <li class="menu-header small text-uppercase"><span class="menu-header-text">Sistem</span></li>
                    <li class="menu-item <?= $segment1 === 'setting' ? 'active' : '' ?>"><a href="<?= esc($baseUrl) ?>/setting" class="menu-link"><i class="menu-icon icon-base bx bx-cog"></i><div>Setting</div></a></li>
                    <li class="menu-item <?= $segment1 === 'user' ? 'active' : '' ?>"><a href="<?= esc($baseUrl) ?>/user" class="menu-link"><i class="menu-icon icon-base bx bx-user"></i><div>User</div></a></li>
                <?php endif; ?>
            </ul>
        </aside>

        <div class="layout-page">
            <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
                    <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)"><i class="icon-base bx bx-menu icon-md"></i></a>
                </div>

                <div class="navbar-nav-right d-flex align-items-center justify-content-between w-100" id="navbar-collapse">
                    <div>
                        <div class="fw-semibold text-heading"><?= esc($title ?? 'Dashboard') ?></div>
                        <small class="text-body-secondary">Aplikasi Iuran Kantin MTsN 4 Jombang</small>
                    </div>

                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="icon-base bx bx-user me-1"></i><?= esc($namaUser) ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><span class="dropdown-item-text small text-body-secondary">Role: <?= esc($role) ?></span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="<?= esc($baseUrl) ?>/logout" method="post" class="px-2">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="dropdown-item text-danger rounded"><i class="icon-base bx bx-power-off me-2"></i>Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
