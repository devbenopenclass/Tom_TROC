<?php
use App\Core\Csrf;
?>
<section class="section">
  <h1 class="page-title">Ajouter un livre</h1>

  <form class="form" method="post" action="<?= BASE_URL ?>/library/create" enctype="multipart/form-data">
    <?= Csrf::input(); ?>

    <label class="field">
      <span>Titre</span>
      <input type="text" name="title" required>
    </label>

    <label class="field">
      <span>Auteur</span>
      <input type="text" name="author" required>
    </label>

    <label class="field">
      <span>Image (optionnel)</span>
      <input type="file" name="image" accept="image/*">
    </label>

    <label class="field">
      <span>Description</span>
      <textarea name="description" rows="6"></textarea>
    </label>

    <label class="field">
      <span>Disponibilité</span>
      <select name="status">
        <option value="available">Disponible</option>
        <option value="unavailable">Indisponible</option>
      </select>
    </label>

    <button class="btn btn--primary" type="submit">Enregistrer</button>
    <a class="btn btn--outline" href="<?= BASE_URL ?>/library">Annuler</a>
  </form>
</section>
