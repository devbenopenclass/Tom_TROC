<?php
namespace App\Core;

// Moteur de rendu très simple : charge l'entête, la vue demandée
// et le pied de page en injectant les données fournies.
class View
{
  // Echappe une valeur pour un affichage HTML sûr dans les vues.
  public static function e(?string $value): string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }

  // Rend une vue complète avec layout commun.
  // Les données du contrôleur deviennent des variables locales dans la vue.
  public function render(string $view, array $data = []): void
  {
    $viewFile = __DIR__ . '/../Views/' . $view . '.php';
    if (!file_exists($viewFile)) {
      throw new \RuntimeException("View not found: {$view}");
    }

    $viewData = $this->sanitizeViewData($data);

    (static function (string $__viewFile, array $__viewData): void {
      extract($__viewData, EXTR_SKIP);
      require __DIR__ . '/../Views/layouts/header.php';
      require $__viewFile;
      require __DIR__ . '/../Views/layouts/footer.php';
    })($viewFile, $viewData);
  }

  private function sanitizeViewData(array $data): array
  {
    $sanitized = [];

    foreach ($data as $key => $value) {
      if (is_string($key) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key) === 1) {
        $sanitized[$key] = $value;
      }
    }

    return $sanitized;
  }
}
