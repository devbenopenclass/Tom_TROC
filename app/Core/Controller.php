<?php
namespace App\Core;

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
}
