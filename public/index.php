<?php
declare(strict_types=1);

// Front Controller - point d'entrée unique
$debug = getenv('APP_DEBUG') === '1';
error_reporting(E_ALL);
ini_set('display_errors', $debug ? '1' : '0');

// Base paths
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('CONFIG_PATH', BASE_PATH . '/config');
define('STORAGE_PATH', BASE_PATH . '/storage');

// Base URL (support installation en sous-dossier, ex: /tomtroc_mvc/public)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
define('BASE_URL', rtrim($scriptDir, '/'));

// Autoload simple (sans Composer)
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    $baseDir = APP_PATH . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

require CONFIG_PATH . '/config.php';

use App\Core\Router;
use App\Core\Session;

Session::start();

$router = new Router();

$router->get('/', 'HomeController@index');

$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

$router->get('/books', 'BookController@index');
$router->get('/books/show', 'BookController@show');

$router->get('/profile', 'UserController@show');

$router->get('/account', 'AccountController@index');
$router->post('/account', 'AccountController@update');

$router->get('/library', 'BookController@myBooks');
$router->get('/library/create', 'BookController@createForm');
$router->post('/library/create', 'BookController@store');
$router->get('/library/edit', 'BookController@editForm');
$router->post('/library/edit', 'BookController@update');
$router->post('/library/delete', 'BookController@delete');

$router->get('/messages', 'MessageController@index');
$router->get('/messages/thread', 'MessageController@thread');
$router->post('/messages/send', 'MessageController@send');

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
