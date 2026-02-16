<?php
use App\Core\View;
?>
<section class="section">
  <a class="back" href="<?= BASE_URL ?>/books">← Retour</a>

  <h1 class="page-title">Profil de <?= View::e($profile['username'] ?? '') ?></h1>
  <?php if (!empty($profile['bio'])): ?>
    <p><?= nl2br(View::e($profile['bio'])) ?></p>
  <?php endif; ?>

  <h2 class="section__title">Ses livres</h2>
  <div class="book-grid">
    <?php foreach (($books ?? []) as $b): ?>
      <?php
      // adapter au partial (besoin username)
      $b['username'] = $profile['username'] ?? '';
      require APP_PATH . '/Views/partials/book-card.php';
      ?>
    <?php endforeach; ?>
    <?php if (empty($books)): ?>
      <p class="muted">Aucun livre.</p>
    <?php endif; ?>
  </div>
</section>
