<?php
use App\Core\Csrf;
use App\Core\View;
?>
<section class="admin-wrap">
  <div class="site-shell">
    <div class="admin-head">
      <h1>Administration des livres</h1>
      <a class="admin-head__link" href="<?= BASE_URL ?>/admin/members">Voir les membres</a>
    </div>

    <div class="admin-table">
      <div class="admin-table__head">
        <span>Photo</span>
        <span>Titre</span>
        <span>Auteur</span>
        <span>Membre</span>
        <span>Disponibilité</span>
        <span>Actions admin</span>
      </div>

      <?php foreach (($books ?? []) as $book): ?>
        <?php
          $img = !empty($book['image']) ? '/assets/uploads/' . $book['image'] : '/assets/img/logo.png';
          $isAvailable = ($book['status'] ?? '') === 'available';
        ?>
        <div class="admin-table__row">
          <div class="admin-table__photo">
            <img src="<?= BASE_URL . View::e($img) ?>" alt="">
          </div>
          <div><?= View::e($book['title'] ?? '') ?></div>
          <div><?= View::e($book['author'] ?? '') ?></div>
          <div>
            <strong><?= View::e($book['username'] ?? '') ?></strong><br>
            <span class="admin-table__muted"><?= View::e($book['email'] ?? '') ?></span>
          </div>
          <div>
            <span class="status-pill <?= $isAvailable ? 'status-pill--ok' : 'status-pill--off' ?>">
              <?= $isAvailable ? 'disponible' : 'non dispo.' ?>
            </span>
          </div>
          <div class="admin-table__actions">
            <form method="post" action="<?= BASE_URL ?>/admin/books/status">
              <?= Csrf::input(); ?>
              <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
              <input type="hidden" name="status" value="<?= $isAvailable ? 'unavailable' : 'available' ?>">
              <button type="submit"><?= $isAvailable ? 'Rendre indisponible' : 'Rendre disponible' ?></button>
            </form>

            <form method="post" action="<?= BASE_URL ?>/admin/books/delete" onsubmit="return confirm('Supprimer ce livre ?');">
              <?= Csrf::input(); ?>
              <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
              <button class="admin-table__danger" type="submit">Supprimer</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($books)): ?>
        <div class="admin-table__empty">Aucun livre disponible.</div>
      <?php endif; ?>
    </div>
  </div>
</section>
