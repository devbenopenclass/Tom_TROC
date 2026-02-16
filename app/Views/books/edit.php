<?php
use App\Core\Csrf;
use App\Core\View;

$img = $book['image'] ?? null;
$cover = $img ? '/assets/uploads/' . $img : '/assets/img/logo@2x.png';
?>
<section class="section">
  <h1 class="page-title">Modifier le livre</h1>

  <div class="book-edit">
    <img class="book-edit__img" src="<?= View::e($cover) ?>" alt="">
    <form class="form" method="post" action="<?= BASE_URL ?>/library/edit?id=<?= (int)$book['id'] ?>" enctype="multipart/form-data">
      <?= Csrf::input(); ?>

      <label class="field">
        <span>Titre</span>
        <input type="text" name="title" value="<?= View::e($book['title'] ?? '') ?>" required>
      </label>

      <label class="field">
        <span>Auteur</span>
        <input type="text" name="author" value="<?= View::e($book['author'] ?? '') ?>" required>
      </label>

      <label class="field">
        <span>Remplacer l’image (optionnel)</span>
        <input type="file" name="image" accept="image/*">
      </label>

      <label class="field">
        <span>Description</span>
        <textarea name="description" rows="6"><?= View::e($book['description'] ?? '') ?></textarea>
      </label>

      <label class="field">
        <span>Disponibilité</span>
        <select name="status">
          <option value="available" <?= ($book['status'] ?? '') === 'available' ? 'selected' : '' ?>>Disponible</option>
          <option value="unavailable" <?= ($book['status'] ?? '') === 'unavailable' ? 'selected' : '' ?>>Indisponible</option>
        </select>
      </label>

      <button class="btn btn--primary" type="submit">Mettre à jour</button>
      <a class="btn btn--outline" href="<?= BASE_URL ?>/library">Annuler</a>
    </form>
  </div>
</section>
