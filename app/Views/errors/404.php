<?php
// Page d'erreur standard :
// utilisée quand une route, un livre ou un profil n'existe pas.
$errorTitle = trim((string)($title ?? '404'));
$errorMessage = trim((string)($message ?? "La page demandée n'existe pas."));
?>

<section class="error-page">
  <div class="error-page__card">
    <p class="error-page__eyebrow">Erreur 404</p>
    <h1><?= htmlspecialchars($errorTitle) ?></h1>
    <p><?= htmlspecialchars($errorMessage) ?></p>
    <a class="btn" href="<?= $base ?>/">Revenir à l'accueil</a>
  </div>
</section>
