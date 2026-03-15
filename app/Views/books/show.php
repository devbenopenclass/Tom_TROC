<?php
$status = (string)($book['status'] ?? 'available');
$statusClass = 'status-available';
if ($status === 'reserved') $statusClass = 'status-reserved';
if ($status === 'unavailable') $statusClass = 'status-unavailable';
?>

<section class="page-head">
  <div>
    <p class="kicker">Fiche livre</p>
    <h1><?= htmlspecialchars($book['title']) ?></h1>
    <p><?= htmlspecialchars($book['author']) ?></p>
  </div>
  <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($status) ?></span>
</section>

<section class="split">
  <article class="card">
    <div class="thumb" style="height:380px;border-radius:14px;overflow:hidden;">
      <?php if (!empty($book['image'])): ?>
        <img src="<?= htmlspecialchars($book['image']) ?>" alt="">
      <?php else: ?>
        <img src="<?= $base ?>/assets/img/figma/mask-group-1.png" alt="Couverture par défaut">
      <?php endif; ?>
    </div>
  </article>

  <article class="card">
    <h2>À propos du livre</h2>
    <p><?= nl2br(htmlspecialchars($book['description'] ?? 'Aucune description.')) ?></p>

    <p class="mini-label">Propriétaire</p>
    <p><a href="<?= $base ?>/profiles/show?id=<?= (int)$book['user_id'] ?>"><strong><?= htmlspecialchars($book['username']) ?></strong></a></p>

    <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] !== (int)$book['user_id']): ?>
      <p><a class="btn" href="<?= $base ?>/messages/thread?user=<?= (int)$book['user_id'] ?>">Envoyer un message</a></p>
    <?php endif; ?>
  </article>
</section>
