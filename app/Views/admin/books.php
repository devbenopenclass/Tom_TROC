<?php
// Vue d'administration des livres : liste, statut et actions de modération.
use App\Core\Csrf;
use App\Core\Url;
use App\Core\View;
use App\Models\Book;

$adminTitle = 'Administration des livres';
$adminDescription = "Gerez les ouvrages publies, controlez leur disponibilite et gardez une vue claire sur les membres qui alimentent la plateforme.";
$adminActiveTab = 'books';
$adminSectionEyebrow = 'Catalogue supervise';
$adminSectionTitle = 'Livres publies par les membres';
$adminSectionMeta = count($books ?? []) . ' livre' . (count($books ?? []) > 1 ? 's' : '') . ' visible' . (count($books ?? []) > 1 ? 's' : '');
$adminSearchAction = $base . '/admin/books';
$adminSearchPlaceholder = 'Titre, auteur, pseudo ou email';
$adminQuery = (string)($query ?? '');
$adminAnchor = (string)($adminAnchor ?? '#admin-panel');

require __DIR__ . '/_intro.php';
?>
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
          $img = Url::asset(Book::imagePath($book));
          $isAvailable = ($book['status'] ?? '') === 'available';
        ?>
        <article class="admin-table__row">
          <div class="admin-table__photo">
            <div class="admin-book-thumb">
              <span class="admin-book-badge <?= $isAvailable ? 'admin-book-badge--ok' : 'admin-book-badge--off' ?>">
                <?= $isAvailable ? 'disponible' : 'non dispo.' ?>
              </span>
              <img src="<?= View::e($img) ?>" alt="Couverture de <?= View::e($book['title'] ?? 'ce livre') ?>">
            </div>
          </div>
          <div><?= View::e($book['title'] ?? '') ?></div>
          <div><?= View::e($book['author'] ?? '') ?></div>
          <div>
            <strong><?= View::e($book['username'] ?? '') ?></strong><br>
            <span class="admin-table__muted"><?= View::e($book['email'] ?? '') ?></span>
          </div>
          <div><?= $isAvailable ? 'Disponible' : 'Non dispo' ?></div>
          <div class="admin-table__actions">
            <form method="post" action="<?= $base ?>/admin/books/status">
              <?= Csrf::input(); ?>
              <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
              <input type="hidden" name="status" value="<?= $isAvailable ? 'unavailable' : 'available' ?>">
              <button class="<?= $isAvailable ? '' : 'admin-table__warning' ?>" type="submit"><?= $isAvailable ? 'Rendre indisponible' : 'Rendre disponible' ?></button>
            </form>

            <form method="post" action="<?= $base ?>/admin/books/delete">
              <?= Csrf::input(); ?>
              <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
              <button class="admin-table__danger" type="submit">Supprimer</button>
            </form>
          </div>
        </article>
      <?php endforeach; ?>

      <?php if (empty($books)): ?>
        <div class="admin-table__empty">Aucun livre disponible.</div>
      <?php endif; ?>
    </div>

<?php require __DIR__ . '/_outro.php'; ?>
