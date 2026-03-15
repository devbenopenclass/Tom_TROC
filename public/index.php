<?php
declare(strict_types=1);

bootErrorHandling();
defineBasePaths();
defineBaseUrl();
registerAutoloader();

use App\Core\Router;
use App\Core\Session;

Session::start();

$router = new Router();
$routes = require CONFIG_PATH . '/routes.php';
$router->register($routes);

$method = is_string($_SERVER['REQUEST_METHOD'] ?? null) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$uri = is_string($_SERVER['REQUEST_URI'] ?? null) ? $_SERVER['REQUEST_URI'] : '/';
$router->dispatch($method, $uri);

function bootErrorHandling(): void
{
    $debug = getenv('APP_DEBUG') === '1';
    error_reporting(E_ALL);
    ini_set('display_errors', $debug ? '1' : '0');
}

function defineBasePaths(): void
{
    define('BASE_PATH', dirname(__DIR__));
    define('APP_PATH', BASE_PATH . '/app');
    define('CONFIG_PATH', BASE_PATH . '/config');
    define('STORAGE_PATH', BASE_PATH . '/storage');
}

function defineBaseUrl(): void
{
    $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $scriptDir = str_replace('\\', '/', dirname($scriptName));
    $baseUrl = rtrim($scriptDir, '/');

    define('BASE_URL', $baseUrl === '/' ? '' : $baseUrl);
}

function registerAutoloader(): void
{
    spl_autoload_register(static function (string $class): void {
        $prefix = 'App\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $file = APP_PATH . '/' . str_replace('\\', '/', $relative) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}
