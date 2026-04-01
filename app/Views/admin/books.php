<?php
// Vue d'administration des livres : liste, statut et actions de modération.
use App\Core\Csrf;
use App\Core\Url;
use App\Core\View;
use App\Models\Book;
?>
<section class="admin-wrap">
  <div class="site-shell">
    <div class="admin-head">
      <div>
        <p class="admin-head__eyebrow">Espace admin</p>
        <h1>Administration des livres</h1>
        <p class="admin-head__intro">Gérez ici la liste des livres, leur disponibilité et les actions de modération.</p>
      </div>
      <div class="admin-head__actions">
        <a class="admin-head__link" href="<?= $base ?>/admin/members">Liste des membres</a>
      </div>
    </div>

    <!-- Cartes de synthèse : elles expliquent les actions disponibles avant le tableau. -->
    <div class="admin-summary">
      <article class="admin-summary__card">
        <span class="admin-summary__tag">Catalogue</span>
        <strong>Liste des livres</strong>
        <p>Consultez tous les livres publiés avec leur propriétaire et leur état.</p>
      </article>
      <article class="admin-summary__card" id="status-actions">
        <span class="admin-summary__tag">Disponibilité</span>
        <strong>Mise à jour du statut d'un livre</strong>
        <p>Rendez un livre disponible ou indisponible directement depuis sa ligne.</p>
      </article>
      <article class="admin-summary__card" id="delete-actions">
        <span class="admin-summary__tag">Modération</span>
        <strong>Suppression d'un livre</strong>
        <p>Retirez un livre du catalogue si son contenu doit être modéré.</p>
      </article>
    </div>

    <!-- Tableau principal de modération : une ligne = un livre. -->
    <div class="admin-table-wrap" id="books-list">
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
          // L'admin réutilise la même logique d'image que le catalogue public.
          $img = Url::asset(Book::imagePath($book, '/assets/img/logo.png'));
          $isAvailable = ($book['status'] ?? '') === 'available';
        ?>
        <div class="admin-table__row">
          <div class="admin-table__photo">
            <img src="<?= View::e($img) ?>" alt="">
          </div>
          <div><?= View::e($book['title'] ?? '') ?></div>
          <div><?= View::e($book['author'] ?? '') ?></div>
          <div>
            <strong><?= View::e($book['username'] ?? '') ?></strong><br>
            <span class="admin-table__muted"><?= View::e($book['email'] ?? '') ?></span>
          </div>
          <div>
            <span class="status-pill <?= $isAvailable ? 'status-pill--ok' : 'status-pill--off' ?>">
              <?= $isAvailable ? 'disponible' : 'indisponible' ?>
            </span>
          </div>
          <div class="admin-table__actions">
            <a class="admin-table__link" href="<?= $base ?>/books/edit?id=<?= (int)$book['id'] ?>">Modifier</a>

            <form method="post" action="<?= $base ?>/admin/books/status">
              <?= Csrf::input(); ?>
              <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
              <input type="hidden" name="status" value="<?= $isAvailable ? 'unavailable' : 'available' ?>">
              <button type="submit"><?= $isAvailable ? 'Rendre indisponible' : 'Rendre disponible' ?></button>
            </form>

            <form method="post" action="<?= $base ?>/admin/books/delete" onsubmit="return confirm('Supprimer ce livre ?');">
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
  </div>
</section>
