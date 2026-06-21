<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// --- 1. ROUTE PUBLIC / AUTENTIKASI ---
$routes->get('auth', 'Auth::index');
$routes->post('auth/validasi', 'Auth::validasi');
$routes->get('auth/logout', 'Auth::logout');
$routes->get('/', 'Auth::index');
$routes->get('logout', 'Auth::logout');


// --- 2. ROUTE GROUP ADMIN (MULTI-ROLE & PROTEKSI) ---
$routes->group('admin', ['namespace' => 'App\Controllers'], function($routes) {
    
    $routes->post('profil/update_mandiri', 'MasterUnit::update_profil_mandiri');

    // [AKSES UNIVERSAL]: Bisa diakses oleh semua role
    $routes->get('lembur', 'Lembur::index');
    $routes->get('lembur/create', 'Lembur::create');
    $routes->post('lembur/save', 'Lembur::save');
    $routes->post('lembur/update', 'Lembur::update');
    $routes->get('lembur/delete/(:num)', 'Lembur::delete/$1');
    $routes->get('lembur/exportExcel', 'Lembur::exportExcel');

    $routes->get('master_users', 'User::index', ['filter' => 'roleAdminOnly']);
    $routes->post('master_users/save', 'User::save', ['filter' => 'roleAdminOnly']);
    $routes->post('master_users/update', 'User::update', ['filter' => 'roleAdminOnly']);
    $routes->get('master_users/delete/(:num)', 'User::delete/$1', ['filter' => 'roleAdminOnly']);

    $routes->get('master_unit', 'MasterUnit::index', ['filter' => 'roleAdminOnly']);
    $routes->post('master_unit/save', 'MasterUnit::save', ['filter' => 'roleAdminOnly']);
    $routes->post('master_unit/update', 'MasterUnit::update', ['filter' => 'roleAdminOnly']);
    $routes->get('master_unit/delete/(:num)', 'MasterUnit::delete/$1', ['filter' => 'roleAdminOnly']);
    
});