<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Auth::login');
$routes->get('login', 'Auth::login', ['as' => 'login']);
$routes->post('login', 'Auth::attempt');
$routes->post('logout', 'Auth::logout');

// Verifikasi QR bersifat publik. Controller akan mengarahkan Operator yang sudah login
// ke detail internal penjual, sedangkan pengunjung umum hanya melihat data minimum.
$routes->get('verifikasi/(:segment)', 'Verifikasi::kartu/$1');

$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);

$routes->group('', ['filter' => 'operator'], static function (RouteCollection $routes): void {
    $routes->get('golongan', 'GolonganPenjual::index');
    $routes->get('golongan/tambah', 'GolonganPenjual::create');
    $routes->post('golongan/simpan', 'GolonganPenjual::store');
    $routes->get('golongan/(:num)/edit', 'GolonganPenjual::edit/$1');
    $routes->post('golongan/(:num)/update', 'GolonganPenjual::update/$1');
    $routes->post('golongan/(:num)/arsipkan', 'GolonganPenjual::arsipkan/$1');

    $routes->get('kategori-pengeluaran', 'KategoriPengeluaran::index');
    $routes->get('kategori-pengeluaran/tambah', 'KategoriPengeluaran::create');
    $routes->post('kategori-pengeluaran/simpan', 'KategoriPengeluaran::store');
    $routes->get('kategori-pengeluaran/(:num)/edit', 'KategoriPengeluaran::edit/$1');
    $routes->post('kategori-pengeluaran/(:num)/update', 'KategoriPengeluaran::update/$1');
    $routes->post('kategori-pengeluaran/(:num)/arsipkan', 'KategoriPengeluaran::arsipkan/$1');

    $routes->get('penjual', 'Penjual::index');
    $routes->get('penjual/tambah', 'Penjual::create');
    $routes->post('penjual/simpan', 'Penjual::store');
    $routes->get('penjual/(:num)/edit', 'Penjual::edit/$1');
    $routes->post('penjual/(:num)/update', 'Penjual::update/$1');
    $routes->post('penjual/(:num)/arsipkan', 'Penjual::arsipkan/$1');
    $routes->get('penjual/(:num)', 'Penjual::show/$1');

    $routes->get('iuran', 'Iuran::index');
    $routes->post('iuran/simpan', 'Iuran::store');
    $routes->get('iuran/form-mingguan', 'Iuran::formMingguan');
    $routes->post('iuran/form-mingguan/pdf', 'Iuran::cetakFormMingguan');

    $routes->get('pengeluaran', 'Pengeluaran::index');
    $routes->get('pengeluaran/tambah', 'Pengeluaran::create');
    $routes->post('pengeluaran/simpan', 'Pengeluaran::store');

    $routes->get('setoran', 'Setoran::index');
    $routes->get('setoran/cetak', 'Setoran::cetakForm');
    $routes->post('setoran/cetak-pdf', 'Setoran::cetakPdf');
    $routes->get('setoran/input', 'Setoran::input');
    $routes->post('setoran/simpan', 'Setoran::store');

    $routes->get('kartu', 'KartuAnggota::index');
    $routes->get('kartu/scan', 'KartuAnggota::scan');
    $routes->post('kartu/(:num)/generate', 'KartuAnggota::generate/$1');
    $routes->post('kartu/(:num)/regenerate', 'KartuAnggota::regenerate/$1');
    $routes->get('kartu/(:num)/depan.jpg', 'KartuAnggota::downloadFront/$1');
    $routes->get('kartu/(:num)/lengkap.zip', 'KartuAnggota::downloadComplete/$1');
    $routes->get('kartu/download/semua-depan.zip', 'KartuAnggota::downloadAllFront');
    $routes->get('kartu/download/semua-lengkap.zip', 'KartuAnggota::downloadAllComplete');

    $routes->get('setting', 'Setting::index');
    $routes->post('setting/update', 'Setting::update');

    $routes->get('user', 'User::index');
    $routes->get('user/tambah', 'User::create');
    $routes->post('user/simpan', 'User::store');
    $routes->get('user/(:num)/edit', 'User::edit/$1');
    $routes->post('user/(:num)/update', 'User::update/$1');
});

$routes->group('laporan', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('iuran', 'Laporan::iuran');
    $routes->get('iuran/export', 'Laporan::exportIuran');
    $routes->get('pengeluaran', 'Laporan::pengeluaran');
    $routes->get('pengeluaran/export', 'Laporan::exportPengeluaran');
    $routes->get('setoran', 'Laporan::setoran');
    $routes->get('setoran/export', 'Laporan::exportSetoran');
    $routes->get('rekap-kas', 'Laporan::rekapKas');
    $routes->get('rekap-kas/export', 'Laporan::exportRekapKas');
});
