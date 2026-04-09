<?php
namespace App\Core;

// Moteur de rendu simple : charge l'entête commun, la vue demandée
// et le pied de page, en injectant les données comme variables locales.
class View
{
  // Échappe une valeur pour un affichage HTML sûr.
  // À utiliser dans les vues sur tout ce qui vient de l'utilisateur.
  public static function e(?string $value): string
  {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
  }

  // Charge et affiche une vue complète avec le layout commun (header + footer).
  // Les clés du tableau $data deviennent des variables directement utilisables dans la vue.
  public function render(string $view, array $data = []): void
  {
    $viewFile = __DIR__ . '/../Views/' . $view . '.php';
    if (!file_exists($viewFile)) {
      throw new \RuntimeException("View not found: {$view}");
    }

    // On assainit les clés avant de faire un extract() pour éviter les injections de variables
    $viewData = $this->sanitizeViewData($data);

    // On isole le contexte dans une closure pour que les variables injectées
    // ne polluent pas l'espace de noms de la classe View.
    (static function (string $__viewFile, array $__viewData): void {
      extract($__viewData, EXTR_SKIP);
      require __DIR__ . '/../Views/layouts/header.php';
      require $__viewFile;
      require __DIR__ . '/../Views/layouts/footer.php';
    })($viewFile, $viewData);
  }

  // Filtre les clés du tableau de données pour ne garder que les noms de variables valides.
  // Cela évite les surprises si une clé contient des caractères spéciaux ou est vide.
  private function sanitizeViewData(array $data): array
  {
    $sanitized = [];

    foreach ($data as $key => $value) {
      // On n'accepte que les clés qui ressemblent à de vrais noms de variables PHP
      if (is_string($key) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key) === 1) {
        $sanitized[$key] = $value;
      }
    }

    return $sanitized;
  }
}
