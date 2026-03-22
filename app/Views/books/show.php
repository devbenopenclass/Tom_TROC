<?php use App\Core\Auth; ?>
<?php use App\Models\Book; ?>
<?php use App\Models\User; ?>

<?php
$status = (string)($book['status'] ?? 'available');
$title = trim((string)($book['title'] ?? 'Livre'));
$author = trim((string)($book['author'] ?? 'Auteur inconnu'));
$owner = trim((string)($book['username'] ?? 'membre de la communaute'));
$description = \App\Models\Book::detailDescription($book);
$image = Book::imagePath($book, '/assets/img/figma/mask-group-1.png');
if (!preg_match('#^https?://#i', $image)) {
  $assetFile = __DIR__ . '/../../../public' . $image;
  $imageVersion = is_file($assetFile) ? (string)filemtime($assetFile) : '1';
  $image = $base . $image . '?v=' . $imageVersion;
}
$ownerAvatar = User::avatarPath($book);
$ownerAvatarFile = __DIR__ . '/../../../public' . $ownerAvatar;
$ownerAvatarVersion = is_file($ownerAvatarFile) ? (string)filemtime($ownerAvatarFile) : '1';
$ownerAvatar = $base . $ownerAvatar . '?v=' . $ownerAvatarVersion;
$canMessageOwner = Auth::check();

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
        <img src="<?= htmlspecialchars($ownerAvatar) ?>" alt="">
        <strong><?= htmlspecialchars($owner) ?></strong>
      </a>

      <?php if ($canMessageOwner): ?>
        <p class="book-show-cta"><a class="btn" href="<?= $base ?>/messages/thread?user=<?= (int)$book['user_id'] ?>&book=<?= (int)$book['id'] ?>">Envoyer un message</a></p>
      <?php endif; ?>
    </article>
  </div>
</section>
