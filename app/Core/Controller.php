<?php
namespace App\Core;

// Classe de base dont héritent tous les contrôleurs du projet.
// Elle met à disposition le moteur de vues et quelques raccourcis utiles.
class Controller
{
  protected View $view;

  public function __construct()
  {
    $this->view = new View();
  }

  // Délègue le rendu d'une vue au moteur View en lui passant les données du contrôleur.
  protected function render(string $view, array $data = []): void
  {
    $this->view->render($view, $data);
  }

  // Redirige vers une URL interne en préfixant automatiquement la base du projet.
  protected function redirect(string $path): void
  {
    header('Location: ' . Url::withBase($path));
    exit;
  }

  // Vérifie le token CSRF soumis dans le formulaire.
  // Si le token ne correspond pas, on coupe avec un 419 pour éviter les faux POSTs.
  protected function requireCsrf(): void
  {
    if (!Csrf::verify($_POST['_csrf'] ?? null)) {
      http_response_code(419);
      echo 'CSRF token invalide';
      exit;
    }
  }
}
