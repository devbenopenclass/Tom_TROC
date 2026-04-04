<?php use App\Core\Auth; ?>
<?php use App\Core\Url; ?>
<?php use App\Models\Book; ?>
<?php use App\Models\User; ?>
<?php // Fiche détail d'un livre : image, description, propriétaire et accès à la messagerie. ?>

<?php
$title = trim((string)($book['title'] ?? 'Livre'));
$author = trim((string)($book['author'] ?? 'Auteur inconnu'));
$owner = trim((string)($book['username'] ?? 'membre de la communauté'));
$description = \App\Models\Book::detailDescription($book);
$image = Url::asset(Book::detailImagePath($book, '/assets/img/figma/mask-group-1.png'));
$ownerAvatar = Url::asset(User::avatarPath($book));
$canMessageOwner = Auth::check();

// Les doubles sauts de ligne créent des paragraphes séparés dans la fiche détail.
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

      <p class="book-show-label">Membre</p>
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
