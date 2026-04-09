<?php // Fiche détail d'un livre : image, description, propriétaire et accès à la messagerie. ?>

<section class="book-show">
  <p class="book-show-breadcrumb">
    <a href="<?= $base ?>/books/exchange">Nos livres</a>
    <span>&gt;</span>
    <span><?= htmlspecialchars($bookView['title']) ?></span>
  </p>

  <div class="book-show-layout">
    <article class="book-show-media">
      <img src="<?= htmlspecialchars($bookView['image']) ?>" alt="">
    </article>

    <article class="book-show-panel">
      <h1><?= htmlspecialchars($bookView['title']) ?></h1>
      <p class="book-show-author">par <?= htmlspecialchars($bookView['author']) ?></p>
      <span class="book-show-divider" aria-hidden="true"></span>

      <p class="book-show-label">Description</p>
      <div class="book-show-copy">
        <?php foreach ($bookView['paragraphs'] as $paragraph): ?>
          <p><?= nl2br(htmlspecialchars(trim($paragraph))) ?></p>
        <?php endforeach; ?>
      </div>

      <p class="book-show-label">Membre</p>
      <a class="book-show-owner" href="<?= $base ?>/profiles/show?id=<?= (int)$bookView['user_id'] ?>">
        <img src="<?= htmlspecialchars($bookView['owner_avatar']) ?>" alt="">
        <strong><?= htmlspecialchars($bookView['owner']) ?></strong>
      </a>

      <?php if ($bookView['can_message_owner']): ?>
        <p class="book-show-cta"><a class="btn" href="<?= $base ?>/messages/thread?user=<?= (int)$bookView['user_id'] ?>&book=<?= (int)$bookView['id'] ?>">Envoyer un message</a></p>
      <?php endif; ?>
    </article>
  </div>
</section>
