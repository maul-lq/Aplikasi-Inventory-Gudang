<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Login::index');
$routes->get('/kategori/hapus(:any)', 'Kategori::index');
$routes->delete('/kategori/hapus(:num)', 'Kategori::hapus/$1');
$routes->get('/barang/hapus(:any)', 'Barang::index');
$routes->delete('/barang/hapus(:num)', 'Barang::hapus/$1');
// Utility routes (explicit, minimal)
$routes->get('/utility', 'Utility::index');
$routes->get('/utility/index', 'Utility::index');
$routes->get('/utility/doBackup', 'Utility::doBackup');
$routes->get('/utility/download/(:any)', 'Utility::download/$1');
$routes->post('/utility/delete', 'Utility::delete');
