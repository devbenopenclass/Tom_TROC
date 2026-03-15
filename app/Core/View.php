<?php
namespace App\Core;

class View
{
  public function render(string $view, array $data = []): void
  {
    extract($data);

    $viewFile = __DIR__ . '/../Views/' . $view . '.php';
    if (!file_exists($viewFile)) {
      throw new \RuntimeException("View not found: {$view}");
    }

    require __DIR__ . '/../Views/layouts/header.php';
    require $viewFile;
    require __DIR__ . '/../Views/layouts/footer.php';
  }
}
