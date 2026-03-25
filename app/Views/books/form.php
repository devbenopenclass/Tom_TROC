<?php use App\Core\Csrf; ?>
<?php $isEdit = (($mode ?? '') === 'edit'); ?>
<?php // Formulaire de gestion d'un livre : ajout ou modification selon le mode courant. ?>

<section class="edit-page">
  <a class="back-link" href="<?= $base ?>/account">← retour</a>
  <h1><?= $isEdit ? 'Modifier les informations' : 'Ajouter un livre' ?></h1>

  <section class="edit-panel">
    <?php if (!empty($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="post" enctype="multipart/form-data"
          action="<?= $isEdit ? $base . '/books/edit' : $base . '/books/create' ?>"
          class="edit-grid">
      <?= Csrf::input(); ?>

      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)($book['id'] ?? 0) ?>">
        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($book['image'] ?? '') ?>">
      <?php endif; ?>

      <div class="edit-photo-col">
        <label>Photo</label>
        <div class="photo-box">
          <?php if (!empty($book['image'])): ?>
            <img src="<?= htmlspecialchars($book['image']) ?>" alt="Photo livre">
          <?php else: ?>
            <img src="<?= $base ?>/assets/img/figma/mask-group.png" alt="Photo livre">
          <?php endif; ?>
        </div>
        <label class="photo-link" for="book-image">Modifier la photo</label>
        <input id="book-image" type="file" name="image" accept="image/*">
      </div>

      <div class="edit-fields-col">
        <label>Titre</label>
        <input name="title" required value="<?= htmlspecialchars($book['title'] ?? '') ?>">

        <label>Auteur</label>
        <input name="author" required value="<?= htmlspecialchars($book['author'] ?? '') ?>">

        <label>Commentaire</label>
        <textarea name="description" rows="11"><?= htmlspecialchars($book['description'] ?? '') ?></textarea>

        <label>Disponibilité</label>
        <?php $s = $book['status'] ?? 'available'; ?>
        <select name="status">
          <option value="available" <?= $s==='available'?'selected':'' ?>>disponible</option>
          <option value="unavailable" <?= $s==='unavailable'?'selected':'' ?>>indisponible</option>
          <option value="reserved" <?= $s==='reserved'?'selected':'' ?>>réservé</option>
        </select>

        <button class="btn" type="submit">Valider</button>
      </div>
    </form>
  </section>
</section>
