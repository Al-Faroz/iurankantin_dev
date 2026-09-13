<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Auth::login');
$routes->get('login', 'Auth::login', ['as' => 'login']);
$routes->post('login', 'Auth::attempt');
$routes->post('logout', 'Auth::logout');

$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);

$routes->group('', ['filter' => 'operator'], static function (RouteCollection $routes): void {
    $routes->get('golongan', 'GolonganPenjual::index');
    $routes->get('golongan/tambah', 'GolonganPenjual::create');
    $routes->post('golongan/simpan', 'GolonganPenjual::store');
    $routes->get('golongan/(:num)/edit', 'GolonganPenjual::edit/$1');
    $routes->post('golongan/(:num)/update', 'GolonganPenjual::update/$1');

    $routes->get('kategori-pengeluaran', 'KategoriPengeluaran::index');
    $routes->get('kategori-pengeluaran/tambah', 'KategoriPengeluaran::create');
    $routes->post('kategori-pengeluaran/simpan', 'KategoriPengeluaran::store');
    $routes->get('kategori-pengeluaran/(:num)/edit', 'KategoriPengeluaran::edit/$1');
    $routes->post('kategori-pengeluaran/(:num)/update', 'KategoriPengeluaran::update/$1');

    $routes->get('penjual', 'Penjual::index');
    $routes->get('penjual/tambah', 'Penjual::create');
    $routes->post('penjual/simpan', 'Penjual::store');
    $routes->get('penjual/(:num)/edit', 'Penjual::edit/$1');
    $routes->post('penjual/(:num)/update', 'Penjual::update/$1');
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

    $routes->get('setting', 'Setting::index');
    $routes->post('setting/update', 'Setting::update');
});
