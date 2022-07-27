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
    $routes->match(['get', 'options'], 'logout', 'Admin\\SessionController::logout');
    $routes->match(['get', 'options'], 'admin-info', 'Admin\\SessionController::getAdminInfo');
    $routes->match(['get', 'options'], 'menu-list', 'Admin\\SessionController::getAllowedMenu');

    $routes->group('akun', function($routes) {
        $routes->match(['post', 'options'], 'update', 'Admin\\AkunController::update');
        $routes->match(['post', 'options'], 'rubah-password', 'Admin\\AkunController::changePassword');
        $routes->match(['post', 'options'], 'konfirmasi-otp', 'Admin\\AkunController::confirmation');
    });

    $routes->group('modul', function($routes) {
        $routes->match(['get', 'options'], 'select/(:num)', 'Admin\\ModulController::select/$1');
        $routes->match(['get', 'options'], 'datatable', 'Admin\\ModulController::datatable');
        $routes->match(['get', 'options'], 'all', 'Admin\\ModulController::all');
        $routes->match(['post', 'options'], 'create', 'Admin\\ModulController::create');
        $routes->match(['post', 'options'], 'update', 'Admin\\ModulController::update');
        $routes->match(['post', 'options'], 'update-status', 'Admin\\ModulController::updateStatus');
        $routes->match(['post', 'options'], 'delete', 'Admin\\ModulController::delete');
    });

    $routes->group('menu', function($routes) {
        $routes->match(['get', 'options'], 'select/(:num)', 'Admin\\MenuController::select/$1');
        $routes->match(['get', 'options'], 'all', 'Admin\\MenuController::all');
        $routes->match(['get', 'options'], 'get', 'Admin\\MenuController::get');
        $routes->match(['post', 'options'], 'create-parent', 'Admin\\MenuController::createParent');
        $routes->match(['post', 'options'], 'create-child', 'Admin\\MenuController::createChild');
        $routes->match(['post', 'options'], 'update-parent', 'Admin\\MenuController::updateParent');
        $routes->match(['post', 'options'], 'update-child', 'Admin\\MenuController::updateChild');
        $routes->match(['post', 'options'], 'update-urutan', 'Admin\\MenuController::updateUrutan');
        $routes->match(['post', 'options'], 'update-status', 'Admin\\MenuController::updateStatus');
        $routes->match(['post', 'options'], 'delete', 'Admin\\MenuController::delete');
    });

    $routes->group('role', function($routes) {
        $routes->match(['get', 'options'], 'select/(:num)', 'Admin\\RoleController::select/$1');
        $routes->match(['get', 'options'], 'datatable', 'Admin\\RoleController::datatable');
        $routes->match(['get', 'options'], 'all', 'Admin\\RoleController::all');
        $routes->match(['post', 'options'], 'create', 'Admin\\RoleController::create');
        $routes->match(['post', 'options'], 'update', 'Admin\\RoleController::update');
        $routes->match(['post', 'options'], 'update-status', 'Admin\\RoleController::updateStatus');
        $routes->match(['post', 'options'], 'delete', 'Admin\\RoleController::delete');
    });

    $routes->group('admin', function($routes) {
        $routes->match(['get', 'options'], 'select/(:num)', 'Admin\\AdminController::select/$1');
        $routes->match(['get', 'options'], 'datatable', 'Admin\\AdminController::datatable');
        $routes->match(['post', 'options'], 'create', 'Admin\\AdminController::create');
        $routes->match(['post', 'options'], 'update', 'Admin\\AdminController::update');
        $routes->match(['post', 'options'], 'update-status', 'Admin\\AdminController::updateStatus');
        $routes->match(['post', 'options'], 'delete', 'Admin\\AdminController::delete');
    });

    $routes->group('website', function($routes) {
        $routes->match(['post', 'options'], 'update', 'Admin\\PengaturanController::update');
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
