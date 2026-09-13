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
});
