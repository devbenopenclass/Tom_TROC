<?php
// Vue d'administration des membres : tableau récapitulatif des comptes inscrits.
use App\Core\View;
?>
<section class="admin-wrap">
  <div class="site-shell">
    <div class="admin-head">
      <div>
        <p class="admin-head__eyebrow">Espace admin</p>
        <h1>Liste des membres</h1>
        <p class="admin-head__intro">Consultez l’ensemble des comptes inscrits et le nombre de livres déposés.</p>
      </div>
    </div>

    <!-- Résumé rapide de ce que l'on consulte sur cette page. -->
    <div class="admin-summary admin-summary--members">
      <article class="admin-summary__card">
        <span class="admin-summary__tag">Comptes</span>
        <strong>Liste des membres</strong>
        <p>Retrouvez les utilisateurs inscrits avec leur date d’inscription.</p>
      </article>
      <article class="admin-summary__card">
        <span class="admin-summary__tag">Activité</span>
        <strong>Bibliothèques déposées</strong>
        <p>Comparez rapidement le nombre de livres publiés par chaque membre.</p>
      </article>
    </div>

    <!-- Tableau des comptes membres et de leur activité. -->
    <div class="admin-table-wrap">
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
  </div>
</section>
