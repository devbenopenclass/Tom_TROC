<?php
use App\Core\View;
use App\Core\Csrf;
?>
<section class="section">
  <h1 class="page-title">Ma bibliothèque</h1>

  <div class="section__actions">
    <a class="btn btn--primary" href="<?= BASE_URL ?>/library/create">Ajouter un livre</a>
  </div>

  <div class="table">
    <div class="table__row table__head">
      <div>Titre</div><div>Auteur</div><div>Statut</div><div>Actions</div>
    </div>

    <?php foreach (($books ?? []) as $b): ?>
      <div class="table__row">
        <div><?= View::e($b['title'] ?? '') ?></div>
        <div><?= View::e($b['author'] ?? '') ?></div>
        <div><?= View::e($b['status'] ?? '') ?></div>
        <div class="table__actions">
          <a class="btn btn--small" href="<?= BASE_URL ?>/library/edit?id=<?= (int)$b['id'] ?>">Modifier</a>

          <form class="inline" method="post" action="<?= BASE_URL ?>/library/delete" onsubmit="return confirm('Supprimer ce livre ?');">
            <?= Csrf::input(); ?>
            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <button class="btn btn--small btn--danger" type="submit">Supprimer</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if (empty($books)): ?>
      <p class="muted">Aucun livre dans votre bibliothèque pour l’instant.</p>
    <?php endif; ?>
  </div>
</section>
