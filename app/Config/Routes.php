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
$routes->match(['get', 'options'], 'website', 'WebsiteController::get');

// add autentikasi
$routes->group($_ENV['API_LOGIN_PAGE'], function($routes) {

    $routes->match(['post', 'options'], '/', 'AutentikasiController::try');
    $routes->match(['get', 'options'], 'cek-akses', 'AutentikasiController::check');

    $routes->group('lupa-password', function($routes) {
        $routes->match(['post', 'options'], 'post', 'AutentikasiController::forgotPassword');
    });

    $routes->group('ubah-password', function($routes) {
        $routes->match(['post', 'options'], 'data', 'AutentikasiController::changePasswordData');
        $routes->match(['post', 'options'], 'post', 'AutentikasiController::changePasswordPost');
    });
});

// admin
$routes->group($_ENV['API_ADMIN_PAGE'], function($routes) {
    $routes->match(['get', 'options'], 'logout', 'LIT\\SessionController::logout');
    $routes->match(['get', 'options'], 'admin-info', 'LIT\\SessionController::getAdminInfo');
    $routes->match(['get', 'options'], 'menu-list', 'LIT\\SessionController::getAllowedMenu');

    $routes->group('akun', function($routes) {
        $routes->match(['post', 'options'], 'update', 'LIT\\AkunController::update');
        $routes->match(['post', 'options'], 'rubah-password', 'LIT\\AkunController::changePassword');
        $routes->match(['post', 'options'], 'konfirmasi-otp', 'LIT\\AkunController::confirmation');
    });

    $routes->group('modul', function($routes) {
        $routes->match(['get', 'options'], 'select/(:num)', 'LIT\\ModulController::select/$1');
        $routes->match(['get', 'options'], 'datatable', 'LIT\\ModulController::datatable');
        $routes->match(['get', 'options'], 'all', 'LIT\\ModulController::all');
        $routes->match(['post', 'options'], 'create', 'LIT\\ModulController::create');
        $routes->match(['post', 'options'], 'update', 'LIT\\ModulController::update');
        $routes->match(['post', 'options'], 'update-status', 'LIT\\ModulController::updateStatus');
        $routes->match(['post', 'options'], 'delete', 'LIT\\ModulController::delete');
    });

    $routes->group('menu', function($routes) {
        $routes->match(['get', 'options'], 'select/(:num)', 'LIT\\MenuController::select/$1');
        $routes->match(['get', 'options'], 'all', 'LIT\\MenuController::all');
        $routes->match(['get', 'options'], 'get', 'LIT\\MenuController::get');
        $routes->match(['post', 'options'], 'create-parent', 'LIT\\MenuController::createParent');
        $routes->match(['post', 'options'], 'create-child', 'LIT\\MenuController::createChild');
        $routes->match(['post', 'options'], 'update-parent', 'LIT\\MenuController::updateParent');
        $routes->match(['post', 'options'], 'update-child', 'LIT\\MenuController::updateChild');
        $routes->match(['post', 'options'], 'update-urutan', 'LIT\\MenuController::updateUrutan');
        $routes->match(['post', 'options'], 'update-status', 'LIT\\MenuController::updateStatus');
        $routes->match(['post', 'options'], 'delete', 'LIT\\MenuController::delete');
    });

    $routes->group('role', function($routes) {
        $routes->match(['get', 'options'], 'select/(:num)', 'LIT\\RoleController::select/$1');
        $routes->match(['get', 'options'], 'datatable', 'LIT\\RoleController::datatable');
        $routes->match(['post', 'options'], 'create', 'LIT\\RoleController::create');
        $routes->match(['post', 'options'], 'update', 'LIT\\RoleController::update');
        $routes->match(['post', 'options'], 'update-status', 'LIT\\RoleController::updateStatus');
        $routes->match(['post', 'options'], 'delete', 'LIT\\RoleController::delete');
    });

    $routes->group('website', function($routes) {
        $routes->match(['post', 'options'], 'update', 'LIT\\PengaturanController::update');
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
