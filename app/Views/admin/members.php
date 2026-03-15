<?php
use App\Core\View;
?>
<section class="admin-wrap">
  <div class="site-shell">
    <div class="admin-head">
      <h1>Membres</h1>
      <a class="admin-head__link" href="<?= BASE_URL ?>/admin/books">Gérer les livres</a>
    </div>

    <div class="admin-table admin-table--members">
      <div class="admin-table__head">
        <span>ID</span>
        <span>Pseudo</span>
        <span>Email</span>
        <span>Inscription</span>
        <span>Livres déposés</span>
      </div>

      <?php foreach (($members ?? []) as $member): ?>
        <div class="admin-table__row">
          <div>#<?= (int)$member['id'] ?></div>
          <div><?= View::e($member['username'] ?? '') ?></div>
          <div><?= View::e($member['email'] ?? '') ?></div>
          <div><?= View::e(date('d/m/Y', strtotime((string)($member['created_at'] ?? 'now')))) ?></div>
          <div><?= (int)($member['books_count'] ?? 0) ?></div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($members)): ?>
        <div class="admin-table__empty">Aucun membre trouvé.</div>
      <?php endif; ?>
    </div>
  </div>
</section>
