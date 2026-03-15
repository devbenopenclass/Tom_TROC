<?php
$status = (string)($book['status'] ?? 'available');
$statusClass = 'status-available';
if ($status === 'reserved') $statusClass = 'status-reserved';
if ($status === 'unavailable') $statusClass = 'status-unavailable';

$title = trim((string)($book['title'] ?? 'Livre'));
$author = trim((string)($book['author'] ?? 'Auteur inconnu'));
$owner = trim((string)($book['username'] ?? 'membre de la communaute'));
$description = trim((string)($book['description'] ?? ''));
$image = (string)($book['image'] ?? '');

if ($image !== '' && str_starts_with($image, '/')) {
  $image = $base . $image;
}

if ($description === '') {
  $description = sprintf(
    '"%s" est propose a l\'echange par %s. Ce livre de %s est disponible dans la bibliotheque Tom Troc.',
    $title,
    $owner,
    $author
  );
}
?>

<section class="page-head">
  <div>
    <p class="kicker">Fiche livre</p>
    <h1><?= htmlspecialchars($title) ?></h1>
    <p><?= htmlspecialchars($author) ?></p>
  </div>
  <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($status) ?></span>
</section>

<section class="split">
  <article class="card">
    <div class="thumb" style="height:380px;border-radius:14px;overflow:hidden;">
      <?php if ($image !== ''): ?>
        <img src="<?= htmlspecialchars($image) ?>" alt="">
      <?php else: ?>
        <img src="<?= $base ?>/assets/img/figma/mask-group-1.png" alt="Couverture par défaut">
      <?php endif; ?>
    </div>
  </article>

  <article class="card">
    <h2>A propos de "<?= htmlspecialchars($title) ?>"</h2>
    <p><?= nl2br(htmlspecialchars($description)) ?></p>

    <p class="mini-label">Propriétaire</p>
    <p><a href="<?= $base ?>/profiles/show?id=<?= (int)$book['user_id'] ?>"><strong><?= htmlspecialchars($owner) ?></strong></a></p>

    <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] !== (int)$book['user_id']): ?>
      <p><a class="btn" href="<?= $base ?>/messages/thread?user=<?= (int)$book['user_id'] ?>">Contacter <?= htmlspecialchars($owner) ?></a></p>
    <?php endif; ?>
  </article>
</section>
