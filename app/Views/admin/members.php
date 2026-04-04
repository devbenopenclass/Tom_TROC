<?php
// Vue d'administration des membres : tableau récapitulatif des comptes inscrits.
use App\Core\View;

$adminTitle = 'Suivi des membres';
$adminDescription = "Consultez les comptes inscrits, leur anciennete et le volume de livres deposes pour garder une vision claire de l'activite communautaire.";
$adminActiveTab = 'members';
$adminSectionEyebrow = 'Communaute';
$adminSectionTitle = 'Membres inscrits sur la plateforme';
$adminSectionMeta = count($members ?? []) . ' membre' . (count($members ?? []) > 1 ? 's' : '');
$adminSearchAction = $base . '/admin/members';
$adminSearchPlaceholder = 'Id, pseudo ou email';
$adminQuery = (string)($query ?? '');
$adminAnchor = (string)($adminAnchor ?? '#admin-panel');

require __DIR__ . '/_intro.php';
?>
    <div class="admin-table admin-table--members">
      <div class="admin-table__head">
        <span>ID</span>
        <span>Pseudo</span>
        <span>Email</span>
        <span>Inscription</span>
        <span>Livres deposés</span>
        <span>Action</span>
      </div>

      <?php foreach (($members ?? []) as $member): ?>
        <article class="admin-table__row">
          <div>#<?= (int)$member['id'] ?></div>
          <div><?= View::e($member['username'] ?? '') ?></div>
          <div><?= View::e($member['email'] ?? '') ?></div>
          <div><?= View::e(date('d/m/Y', strtotime((string)($member['created_at'] ?? 'now')))) ?></div>
          <div><?= (int)($member['books_count'] ?? 0) ?></div>
          <div class="admin-table__actions">
            <form method="post" action="<?= $base ?>/admin/members/delete<?= $adminAnchor ?>">
              <?= \App\Core\Csrf::input(); ?>
              <input type="hidden" name="id" value="<?= (int)$member['id'] ?>">
              <button class="admin-table__danger" type="submit">Supprimer</button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>

      <?php if (empty($members)): ?>
        <div class="admin-table__empty">Aucun membre trouvé.</div>
      <?php endif; ?>
    </div>

<?php require __DIR__ . '/_outro.php'; ?>
