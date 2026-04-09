<?php
namespace App\Core;

// Routeur minimaliste : on enregistre les routes GET et POST,
// puis on trouve et on exécute le bon contrôleur pour chaque requête.
class Router
{
  // Table de routage indexée par méthode HTTP puis par chemin normalisé
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

  // Uniformise les chemins pour éviter les différences entre "/" et "/books/".
  private function normalize(string $path): string
  {
    $path = '/' . trim($path, '/');
    return $path === '//' ? '/' : $path;
  }

  // Résout l'URL de la requête courante et exécute le contrôleur associé.
  public function dispatch(): void
  {
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    // Si l'appli tourne dans un sous-dossier comme /tomtroc, on retire ce préfixe
    $baseUrl = Url::baseUrl();
    if ($baseUrl && str_starts_with($uri, $baseUrl)) {
      $uri = substr($uri, strlen($baseUrl)) ?: '/';
    }

    // On normalise aussi les URLs qui passent encore par index.php directement
    if ($uri === '/index.php') {
      $uri = '/';
    } elseif (str_starts_with($uri, '/index.php/')) {
      $uri = substr($uri, strlen('/index.php')) ?: '/';
    }

    $path = $this->normalize($uri);

    // Aucune route trouvée : on affiche la page 404
    $handler = $this->routes[$method][$path] ?? null;
    if (!$handler) {
      http_response_code(404);
      (new View())->render('errors/404');
      return;
    }

    // Le handler est de la forme "NomDuControleur@methode"
    [$controllerName, $action] = explode('@', $handler);
    $fqcn = "\\App\\Controllers\\{$controllerName}";

    // Le contrôleur n'existe pas : erreur de configuration
    if (!class_exists($fqcn)) {
      http_response_code(500);
      echo "Controller not found: " . htmlspecialchars($fqcn);
      return;
    }

    $controller = new $fqcn();

    // La méthode n'existe pas sur ce contrôleur : erreur de configuration
    if (!method_exists($controller, $action)) {
      http_response_code(500);
      echo "Action not found: " . htmlspecialchars($action);
      return;
    }

    $controller->$action();
  }
}
