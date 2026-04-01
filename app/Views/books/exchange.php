<?php use App\Core\Url; ?>
<?php use App\Models\Book; ?>
<?php // Catalogue public des livres : recherche et grille complète des ouvrages disponibles sur la plateforme. ?>

<section class="exchange-head">
  <div class="exchange-copy">
    <h1>Nos livres disponibles à l'échange</h1>
  </div>

  <form method="get" action="<?= $base ?>/books/exchange" class="exchange-search">
    <input name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Rechercher un livre">
  </form>
</section>

<?php if (empty($books)): ?>
  <section class="card">
    <p class="muted">Aucun livre disponible pour l'instant.</p>
  </section>
<?php else: ?>
  <section class="grid">
    <?php foreach ($books as $b): ?>
      <?php
      $status = (string)($b['status'] ?? 'available');
      $statusClass = 'status-available';
      $statusLabel = 'disponible';
      $image = Url::asset(Book::imagePath($b));
      if ($status === 'reserved') {
        $statusClass = 'status-reserved';
        $statusLabel = 'réservé';
      } elseif ($status === 'unavailable') {
        $statusClass = 'status-unavailable';
        $statusLabel = 'indisponible';
      }
      ?>
      <a class="book" href="<?= $base ?>/books/show?id=<?= (int)$b['id'] ?>">
        <div class="thumb">
          <img src="<?= htmlspecialchars($image) ?>" alt="">
        </div>
        <div class="meta">
          <strong><?= htmlspecialchars($b['title']) ?></strong>
          <div class="muted"><?= htmlspecialchars($b['author']) ?></div>
          <div class="book-owner">Proposé par : <?= htmlspecialchars($b['username']) ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </section>
<?php endif; ?>
