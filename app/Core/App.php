<?php
namespace App\Core;

require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/View.php';
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Auth.php';

spl_autoload_register(function (string $class) {
  if (str_starts_with($class, 'App\\')) {
    $path = __DIR__ . '/../' . str_replace('App\\', '', $class) . '.php';
    $path = str_replace('\\', '/', $path);
    if (file_exists($path)) require_once $path;
  }
});

class App
{
  public function run(): void
  {
    $router = new Router();
    $this->registerRoutes($router);
    $router->dispatch();
  }

  private function registerRoutes(Router $router): void
  {
    $routes = require __DIR__ . '/../../config/routes.php';

    foreach (($routes['GET'] ?? []) as $path => $handler) {
      $router->get((string) $path, (string) $handler);
    }

    foreach (($routes['POST'] ?? []) as $path => $handler) {
      $router->post((string) $path, (string) $handler);
    }
  }
}
