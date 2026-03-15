<?php
namespace App\Core;

class Router
{
  private array $routes = ['GET' => [], 'POST' => []];

  public function get(string $path, string $handler): void
  {
    $this->routes['GET'][$this->normalize($path)] = $handler;
  }

  public function post(string $path, string $handler): void
  {
    $this->routes['POST'][$this->normalize($path)] = $handler;
  }

  private function normalize(string $path): string
  {
    $path = '/' . trim($path, '/');
    return $path === '//' ? '/' : $path;
  }

  public function dispatch(): void
  {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    // Handle configured or auto-detected base_url
    $baseUrl = Url::baseUrl();
    if ($baseUrl && str_starts_with($uri, $baseUrl)) {
      $uri = substr($uri, strlen($baseUrl)) ?: '/';
    }

    // Support front-controller URLs such as /index.php and /index.php/...
    if ($uri === '/index.php') {
      $uri = '/';
    } elseif (str_starts_with($uri, '/index.php/')) {
      $uri = substr($uri, strlen('/index.php')) ?: '/';
    }

    $path = $this->normalize($uri);

    $handler = $this->routes[$method][$path] ?? null;
    if (!$handler) {
      http_response_code(404);
      echo "404 Not Found";
      return;
    }

    [$controllerName, $action] = explode('@', $handler);
    $fqcn = "\\App\\Controllers\\{$controllerName}";

    if (!class_exists($fqcn)) {
      http_response_code(500);
      echo "Controller not found: " . htmlspecialchars($fqcn);
      return;
    }

    $controller = new $fqcn();

    if (!method_exists($controller, $action)) {
      http_response_code(500);
      echo "Action not found: " . htmlspecialchars($action);
      return;
    }

    $controller->$action();
  }
}
