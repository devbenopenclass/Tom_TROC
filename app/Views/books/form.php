<?php use App\Core\Csrf; ?>
<?php use App\Core\Url; ?>
<?php use App\Models\Book; ?>
<?php $isEdit = (($mode ?? '') === 'edit'); ?>
<?php // Formulaire de gestion d'un livre : ajout ou modification selon le mode courant. ?>
<?php
$errorMessage = trim((string)($error ?? ''));
$book = $book ?? [];
$status = (string)($book['status'] ?? 'available');
$imagePath = Url::asset(Book::imagePath($book));
// Les champs simples sont décrits ici pour éviter de répéter le même HTML.
$textFields = [
  ['label' => 'Titre', 'name' => 'title', 'value' => (string)($book['title'] ?? ''), 'required' => true],
  ['label' => 'Auteur', 'name' => 'author', 'value' => (string)($book['author'] ?? ''), 'required' => true],
];
// Les libellés restent centralisés pour garder le formulaire et l'admin cohérents.
$statusOptions = [
  'available' => 'disponible',
  'unavailable' => 'indisponible',
  'reserved' => 'réservé',
];
?>

<section class="edit-page">
  <h1><?= $isEdit ? 'Modifier les informations' : 'Ajouter un livre' ?></h1>

  <section class="edit-panel">
    <?php if ($errorMessage !== ''): ?><p class="error"><?= htmlspecialchars($errorMessage) ?></p><?php endif; ?>

    <form method="post" enctype="multipart/form-data"
          action="<?= $isEdit ? $base . '/books/edit' : $base . '/books/create' ?>"
          class="edit-grid">
      <?= Csrf::input(); ?>

      <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int)($book['id'] ?? 0) ?>">
        <input type="hidden" name="existing_image" value="<?= htmlspecialchars($book['image'] ?? '') ?>">
      <?php endif; ?>

      <div class="edit-photo-col">
        <label for="book-image">Photo</label>
        <div class="photo-box">
          <img src="<?= htmlspecialchars($imagePath) ?>" alt="Photo livre">
        </div>
        <label class="photo-link" for="book-image">Modifier la photo</label>
        <input id="book-image" type="file" name="image" accept="image/*">
      </div>

      <div class="edit-fields-col">
        <?php foreach ($textFields as $field): ?>
          <label for="book-<?= htmlspecialchars($field['name']) ?>"><?= htmlspecialchars($field['label']) ?></label>
          <input
            id="book-<?= htmlspecialchars($field['name']) ?>"
            name="<?= htmlspecialchars($field['name']) ?>"
            value="<?= htmlspecialchars($field['value']) ?>"
            <?= $field['required'] ? 'required' : '' ?>
          >
        <?php endforeach; ?>

        <label for="book-description">Description</label>
        <textarea id="book-description" name="description" rows="11"><?= htmlspecialchars($book['description'] ?? '') ?></textarea>

        <label for="book-status">Disponibilité</label>
        <select id="book-status" name="status">
          <?php foreach ($statusOptions as $value => $label): ?>
            <option value="<?= htmlspecialchars($value) ?>" <?= $status === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
          <?php endforeach; ?>
        </select>

        <button class="btn" type="submit">Valider</button>
      </div>
    </form>
  </section>
</section>
