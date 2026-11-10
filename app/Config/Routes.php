<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ---------------------------------------------------------------- Publik
$routes->get('/', 'Home::index');
$routes->get('tentang', 'Home::about');
$routes->get('listings', 'Listing::index');
$routes->get('listings/(:num)', 'Listing::show/$1');
$routes->get('u/(:num)', 'Profile::show/$1');
$routes->get('impact', 'Impact::index');

// ---------------------------------------------------------------- Tamu (belum login)
$routes->group('', ['filter' => 'guest'], static function ($routes) {
    $routes->get('register', 'Auth::register');
    $routes->post('register', 'Auth::store');
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::attempt');
});

// ---------------------------------------------------------------- Sudah login (semua role)
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    $routes->post('logout', 'Auth::logout');

    $routes->get('profile/edit', 'Profile::edit');
    $routes->post('profile/edit', 'Profile::update');
    $routes->post('profile/verification', 'Profile::requestVerification');

    $routes->get('notifications', 'Notification::index');
    $routes->get('notifications/open/(:num)', 'Notification::open/$1');
    $routes->post('notifications/read-all', 'Notification::readAll');
});

// ---------------------------------------------------------------- Penyetor & pendaur: transaksi
$routes->group('', ['filter' => ['auth', 'role:penyetor,pendaur']], static function ($routes) {
    $routes->get('offers/history', 'Offer::history');
    $routes->get('offers/(:num)', 'Offer::show/$1');
    $routes->post('offers/(:num)/cancel-deal', 'Offer::cancelDeal/$1');
    $routes->post('offers/(:num)/messages', 'Message::store/$1');

    $routes->get('ratings/(:num)', 'Rating::form/$1');
    $routes->post('ratings/(:num)', 'Rating::store/$1');
});

// ---------------------------------------------------------------- Penyetor
$routes->group('', ['filter' => ['auth', 'role:penyetor']], static function ($routes) {
    $routes->get('listings/new', 'Listing::create');
    $routes->post('listings', 'Listing::store');
    $routes->get('listings/(:num)/edit', 'Listing::edit/$1');
    $routes->post('listings/(:num)/edit', 'Listing::update/$1');
    $routes->post('listings/(:num)/delete', 'Listing::delete/$1');
    $routes->post('listings/(:num)/cancel', 'Listing::cancel/$1');
    $routes->post('listings/(:num)/repost', 'Listing::repost/$1');
    $routes->get('my/listings', 'Listing::mine');

    $routes->get('offers/incoming', 'Offer::incoming');
    $routes->post('offers/(:num)/accept', 'Offer::accept/$1');
    $routes->post('offers/(:num)/reject', 'Offer::reject/$1');
    $routes->post('offers/(:num)/counter', 'Offer::counter/$1');
    $routes->post('offers/(:num)/complete', 'Offer::complete/$1');
});

// ---------------------------------------------------------------- Pendaur ulang
$routes->group('', ['filter' => ['auth', 'role:pendaur']], static function ($routes) {
    $routes->post('listings/(:num)/offers', 'Offer::store/$1');
    $routes->get('my/offers', 'Offer::mine');
    $routes->post('offers/(:num)/accept-counter', 'Offer::acceptCounter/$1');
    $routes->post('offers/(:num)/rebid', 'Offer::rebid/$1');
    $routes->post('offers/(:num)/withdraw', 'Offer::withdraw/$1');
});

// ---------------------------------------------------------------- Admin
$routes->group('admin', ['filter' => ['auth', 'role:admin'], 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');

    $routes->get('users', 'Users::index');
    $routes->post('users/(:num)/toggle', 'Users::toggle/$1');

    $routes->get('verification', 'Verification::index');
    $routes->get('verification/(:num)/document', 'Verification::document/$1');
    $routes->post('verification/(:num)/approve', 'Verification::approve/$1');
    $routes->post('verification/(:num)/reject', 'Verification::reject/$1');

    $routes->get('categories', 'Categories::index');
    $routes->get('categories/new', 'Categories::create');
    $routes->post('categories', 'Categories::store');
    $routes->get('categories/(:num)/edit', 'Categories::edit/$1');
    $routes->post('categories/(:num)/edit', 'Categories::update/$1');
    $routes->post('categories/(:num)/toggle', 'Categories::toggle/$1');
    $routes->post('categories/(:num)/delete', 'Categories::delete/$1');
});
