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
