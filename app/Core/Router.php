<?php
namespace App\Core;

// Routeur minimaliste : enregistre les routes GET/POST
// puis retrouve le bon contrôleur à exécuter.
class Router
{
  private array $routes = ['GET' => [], 'POST' => []];

  // Enregistre une route GET après normalisation du chemin.
  public function get(string $path, string $handler): void
  {
    $this->routes['GET'][$this->normalize($path)] = $handler;
  }

  // Enregistre une route POST après normalisation du chemin.
  public function post(string $path, string $handler): void
  {
    $this->routes['POST'][$this->normalize($path)] = $handler;
  }

  // Uniformise les chemins pour éviter les différences entre
  // "/", "/books" et "/books/".
  private function normalize(string $path): string
  {
    $path = '/' . trim($path, '/');
    return $path === '//' ? '/' : $path;
  }

  // Résout l'URL courante, retire éventuellement le préfixe /tomtroc
  // puis exécute la méthode du contrôleur correspondant.
  public function dispatch(): void
  {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    // Retire l'URL de base du projet si l'application est servie
    // dans un sous-dossier comme /tomtroc.
    $baseUrl = Url::baseUrl();
    if ($baseUrl && str_starts_with($uri, $baseUrl)) {
      $uri = substr($uri, strlen($baseUrl)) ?: '/';
    }

    // Accepte aussi les URLs qui passent encore par index.php.
    if ($uri === '/index.php') {
      $uri = '/';
    } elseif (str_starts_with($uri, '/index.php/')) {
      $uri = substr($uri, strlen('/index.php')) ?: '/';
    }

    $path = $this->normalize($uri);

    $handler = $this->routes[$method][$path] ?? null;
    if (!$handler) {
      http_response_code(404);
      (new View())->render('errors/404', ['path' => $path]);
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
