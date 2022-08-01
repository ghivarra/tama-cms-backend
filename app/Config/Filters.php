<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;
use App\Filters\LoggedIn;
use App\Filters\LoggedOut;
use App\Filters\Api;
use App\Filters\AksesFilter;
use App\Filters\Throttle;

define('LOGINPAGE', $_ENV['API_LOGIN_PAGE']);
define('ADMINPAGE', $_ENV['API_ADMIN_PAGE']);

class Filters extends BaseConfig
{
    /**
     * Configures aliases for Filter classes to
     * make reading things nicer and simpler.
     *
     * @var array
     */
    public $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'loggedIn'      => LoggedIn::class,
        'loggedOut'     => LoggedOut::class,
        'throttle'      => Throttle::class,
        'api'           => Api::class,
        'akses'         => AksesFilter::class
    ];

    /**
     * List of filter aliases that are always
     * applied before and after every request.
     *
     * @var array
     */
    public $globals = [
        'before' => [
            'api',
            'akses',
            // 'csrf',
            // 'invalidchars',
        ],
        'after' => [
            'toolbar',
            // 'honeypot',
            // 'secureheaders',
        ],
    ];

    /**
     * List of filter aliases that works on a
     * particular HTTP method (GET, POST, etc.).
     *
     * Example:
     * 'post' => ['foo', 'bar']
     *
     * If you use this, you should disable auto-routing because auto-routing
     * permits any HTTP method to access a controller. Accessing the controller
     * with a method you don’t expect could bypass the filter.
     *
     * @var array
     */
    public $methods = [];

    /**
     * List of filter aliases that should run on any
     * before or after URI patterns.
     *
     * Example:
     * 'isLoggedIn' => ['before' => ['account/*', 'profiles/*']]
     *
     * @var array
     */
    public $filters = [
        'loggedIn' => [
            'before' => [LOGINPAGE, LOGINPAGE . '/*']
        ],
        'loggedOut' => [
            'before' => [ADMINPAGE, ADMINPAGE . '/*']
        ],
        'throttle' => [
            'before' => [LOGINPAGE, LOGINPAGE . '/*']
        ]
    ];
}
