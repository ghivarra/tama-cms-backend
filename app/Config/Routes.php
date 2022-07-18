<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
//$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');
$routes->add('website', 'WebsiteController::get');

// add autentikasi
$routes->group('autentikasi', function($routes) {

    $routes->add('/', 'AutentikasiController::try');
    $routes->add('cek-akses', 'AutentikasiController::check');

    $routes->group('lupa-password', function($routes) {
        $routes->add('post', 'AutentikasiController::forgotPassword');
    });

    $routes->group('ubah-password', function($routes) {
        $routes->add('data', 'AutentikasiController::changePasswordData');
        $routes->add('post', 'AutentikasiController::changePasswordPost');
    });
});

// admin
$routes->group('sertifikasi', function($routes) {
    $routes->add('logout', 'LIT\\SessionController::logout');
    $routes->add('admin-info', 'LIT\\SessionController::getAdminInfo');
    $routes->add('menu-list', 'LIT\\SessionController::getAllowedMenu');

    $routes->group('modul', function($routes) {
        $routes->add('select/(:num)', 'LIT\\ModulController::select/$1', ['as' => 'Modul']);
        $routes->add('get', 'LIT\\ModulController::get');
        $routes->add('create', 'LIT\\ModulController::create');
        $routes->add('update', 'LIT\\ModulController::update');
        $routes->add('delete', 'LIT\\ModulController::delete');
    });

    $routes->group('akun', function($routes) {
        $routes->add('update', 'LIT\\AkunController::update');
        $routes->add('rubah-password', 'LIT\\AkunController::changePassword');
        $routes->add('konfirmasi-otp', 'LIT\\AkunController::confirmation');
    });
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
