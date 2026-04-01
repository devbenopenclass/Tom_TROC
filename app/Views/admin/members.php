<?php
use App\Core\Csrf;
use App\Core\View;
use App\Models\User;
?>
<section class="admin-wrap">
  <div class="site-shell">
    <div class="admin-head">
      <div>
        <p class="admin-head__eyebrow">Espace admin</p>
        <h1>Gestion des membres</h1>
        <p class="admin-head__intro">Supprimez un compte membre si nécessaire et restaurez-le pendant 30 jours.</p>
      </div>
      <div class="admin-head__actions">
        <a class="admin-head__link" href="<?= $base ?>/admin/books">Retour aux livres</a>
      </div>
    </div>

    <div class="admin-members-block">
      <div class="admin-members-block__head">
        <p class="admin-head__eyebrow">Corbeille 30 jours</p>
        <h2>Membres inscrits</h2>
        <p>La suppression masque le compte, ses livres et ses échanges. La restauration reste possible pendant 30 jours.</p>
      </div>

      <div class="admin-table admin-table--members">
        <div class="admin-table__head">
          <span>ID</span>
          <span>Pseudo</span>
          <span>Email</span>
          <span>Livres déposés</span>
          <span>Action</span>
        </div>

        <?php foreach (($members ?? []) as $member): ?>
          <?php
          $memberId = (int)($member['id'] ?? 0);
          $isProtectedAdmin = User::isAdmin($memberId);
          $isDeletedMember = User::isDeleted($member);
          $daysLeft = User::restoreDaysLeft($member);
          ?>
          <div class="admin-table__row">
            <div>#<?= $memberId ?></div>
            <div><?= View::e($member['username'] ?? '') ?></div>
            <div><?= View::e($member['email'] ?? '') ?></div>
            <div><?= (int)($member['books_count'] ?? 0) ?></div>
            <div class="admin-table__actions">
              <?php if ($isProtectedAdmin): ?>
                <span class="status-pill status-pill--ok">admin protégé</span>
              <?php elseif ($isDeletedMember): ?>
                <span class="status-pill status-pill--off">supprimé</span>
                <?php if ($daysLeft > 0): ?>
                  <form method="post" action="<?= $base ?>/admin/members/restore">
                    <?= Csrf::input(); ?>
                    <input type="hidden" name="id" value="<?= $memberId ?>">
                    <button type="submit">Restaurer (<?= $daysLeft ?> j)</button>
                  </form>
                <?php endif; ?>
              <?php else: ?>
                <form method="post" action="<?= $base ?>/admin/members/delete" onsubmit="return confirm('Supprimer ce membre, masquer son compte et ses livres pendant 30 jours ?');">
                  <?= Csrf::input(); ?>
                  <input type="hidden" name="id" value="<?= $memberId ?>">
                  <button class="admin-table__danger" type="submit">Supprimer le membre</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if (empty($members)): ?>
          <div class="admin-table__empty">Aucun membre trouvé.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
