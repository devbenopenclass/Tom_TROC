<?php
use App\Core\View;
?>
<section class="section">
  <h1 class="page-title">Nos livres à l’échange</h1>

  <form class="search" method="get" action="<?= BASE_URL ?>/books">
    <input type="text" name="q" value="<?= View::e($q ?? '') ?>" placeholder="Rechercher par titre...">
    <button class="btn btn--primary" type="submit">Rechercher</button>
  </form>

  <div class="book-grid">
    <?php foreach (($books ?? []) as $b): ?>
      <?php require APP_PATH . '/Views/partials/book-card.php'; ?>
    <?php endforeach; ?>
    <?php if (empty($books)): ?>
      <p class="muted">Aucun livre disponible pour le moment.</p>
    <?php endif; ?>
  </div>
</section>
