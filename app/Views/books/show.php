<?php use App\Models\Book; ?>

<?php
$status = (string)($book['status'] ?? 'available');
$title = trim((string)($book['title'] ?? 'Livre'));
$author = trim((string)($book['author'] ?? 'Auteur inconnu'));
$owner = trim((string)($book['username'] ?? 'membre de la communaute'));
$description = trim((string)($book['description'] ?? ''));
$image = Book::imagePath($book, '/assets/img/figma/mask-group-1.png');
if (!preg_match('#^https?://#i', $image)) {
  $assetFile = __DIR__ . '/../../../public' . $image;
  $imageVersion = is_file($assetFile) ? (string)filemtime($assetFile) : '1';
  $image = $base . $image . '?v=' . $imageVersion;
}

if ($description === '') {
  $description = sprintf(
    '"%s" est propose a l\'echange par %s. Ce livre de %s est disponible dans la bibliotheque Tom Troc.',
    $title,
    $owner,
    $author
  );
}

$paragraphs = preg_split("/\n\s*\n/", $description) ?: [$description];
?>

<section class="book-show">
  <p class="book-show-breadcrumb">
    <a href="<?= $base ?>/books/exchange">Nos livres</a>
    <span>&gt;</span>
    <span><?= htmlspecialchars($title) ?></span>
  </p>

  <div class="book-show-layout">
    <article class="book-show-media">
      <img src="<?= htmlspecialchars($image) ?>" alt="">
    </article>

    <article class="book-show-panel">
      <h1><?= htmlspecialchars($title) ?></h1>
      <p class="book-show-author">par <?= htmlspecialchars($author) ?></p>
      <span class="book-show-divider" aria-hidden="true"></span>

      <p class="book-show-label">Description</p>
      <div class="book-show-copy">
        <?php foreach ($paragraphs as $paragraph): ?>
          <p><?= nl2br(htmlspecialchars(trim($paragraph))) ?></p>
        <?php endforeach; ?>
      </div>

      <p class="book-show-label">Propriétaire</p>
      <a class="book-show-owner" href="<?= $base ?>/profiles/show?id=<?= (int)$book['user_id'] ?>">
        <img src="<?= $base ?>/assets/img/figma/mask-group-3.png" alt="">
        <strong><?= htmlspecialchars($owner) ?></strong>
      </a>

      <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] !== (int)$book['user_id']): ?>
        <p class="book-show-cta"><a class="btn" href="<?= $base ?>/messages/thread?user=<?= (int)$book['user_id'] ?>">Envoyer un message</a></p>
      <?php endif; ?>
    </article>
  </div>
</section>
