<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Auth::login');
$routes->get('login', 'Auth::login', ['as' => 'login']);
$routes->post('login', 'Auth::attempt', ['filter' => 'csrf']);
$routes->post('logout', 'Auth::logout', ['filter' => 'csrf']);

$routes->get('dashboard', 'Dashboard::index', ['filter' => 'auth']);
