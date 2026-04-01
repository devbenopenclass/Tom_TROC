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
