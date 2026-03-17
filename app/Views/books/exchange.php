<?php use App\Models\Book; ?>

<section class="exchange-head">
  <div class="exchange-copy">
    <h1>Nos livres à l'échange</h1>
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
      $image = Book::imagePath($b);
      if (!preg_match('#^https?://#i', $image)) {
        $assetFile = __DIR__ . '/../../../public' . $image;
        $imageVersion = is_file($assetFile) ? (string)filemtime($assetFile) : '1';
        $image = $base . $image . '?v=' . $imageVersion;
      }
      if ($status === 'reserved') {
        $statusClass = 'status-reserved';
        $statusLabel = 'reserve';
      } elseif ($status === 'unavailable') {
        $statusClass = 'status-unavailable';
        $statusLabel = 'non dispo.';
      }
      ?>
      <a class="book" href="<?= $base ?>/books/show?id=<?= (int)$b['id'] ?>">
        <div class="thumb">
          <img src="<?= htmlspecialchars($image) ?>" alt="">
        </div>
        <div class="meta">
          <strong><?= htmlspecialchars($b['title']) ?></strong>
          <div class="muted"><?= htmlspecialchars($b['author']) ?></div>
          <div class="book-owner">Vendu par : <?= htmlspecialchars($b['username']) ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </section>
<?php endif; ?>
