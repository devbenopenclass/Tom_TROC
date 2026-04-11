<?php // Catalogue public des livres : recherche et grille complète des ouvrages disponibles sur la plateforme. ?>

<section class="exchange-head">
  <div class="exchange-copy">
    <h1>Nos livres disponibles à l'échange</h1>
  </div>

  <form method="get" action="<?= $base ?>/books/exchange" class="exchange-search">
    <label class="sr-only" for="exchange-search">Rechercher un livre</label>
    <input id="exchange-search" name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Rechercher un livre">
  </form>
</section>

<?php if (empty($books)): ?>
  <section class="card">
    <p class="muted">Aucun livre disponible pour l'instant.</p>
  </section>
<?php else: ?>
  <div class="grid">
    <?php foreach ($books as $b): ?>
      <a class="book" href="<?= $base ?>/books/show?id=<?= (int)$b['id'] ?>">
        <div class="thumb">
          <span class="book-status <?= htmlspecialchars($b['status_class']) ?>">
            <?= htmlspecialchars($b['status_label']) ?>
          </span>
          <img src="<?= htmlspecialchars($b['image']) ?>" alt="">
        </div>
        <div class="meta">
          <strong><?= htmlspecialchars($b['title']) ?></strong>
          <div class="muted"><?= htmlspecialchars($b['author']) ?></div>
          <div class="book-owner">Proposé par : <?= htmlspecialchars($b['owner']) ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
