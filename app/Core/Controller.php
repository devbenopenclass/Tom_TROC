<?php
namespace App\Core;

// Classe de base commune à tous les contrôleurs.
// Elle instancie la vue et fournit un raccourci de rendu.
class Controller
{
  protected View $view;

  public function __construct()
  {
    $this->view = new View();
  }

  protected function render(string $view, array $data = []): void
  {
    $this->view->render($view, $data);
  }

  protected function redirect(string $path): void
  {
    header('Location: ' . Url::withBase($path));
    exit;
  }

  protected function requireCsrf(): void
  {
    if (!Csrf::verify($_POST['_csrf'] ?? null)) {
      http_response_code(419);
      echo 'CSRF token invalide';
      exit;
    }
  }
}
