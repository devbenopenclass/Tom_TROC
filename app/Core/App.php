<?php
namespace App\Core;

// Point d'entrée de l'application : s'appuie sur l'autoload PSR-4
// configuré dans bootstrap.php / Composer puis lance le routeur.

class App
{
  // Démarre l'application : enregistre les routes puis dispatche la requête.
  public function run(): void
  {
    $router = new Router();
    $this->registerRoutes($router);
    $router->dispatch();
  }

  // Lit la table de routage depuis config/routes.php et l'injecte dans le routeur.
  // Tout est centralisé dans ce fichier pour éviter les définitions éparpillées.
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
