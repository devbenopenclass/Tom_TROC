<section class="page-head">
  <div>
    <p class="kicker">Profil public</p>
    <h1><?= htmlspecialchars($user['username']) ?></h1>
    <p>Bibliothèque partagée et informations du membre.</p>
  </div>
  <img src="<?= $base ?>/assets/img/figma/icon-mon-compte.svg" alt="Icône profil">
</section>

<section class="card profile-header">
  <img src="<?= $base ?>/assets/img/figma/mask-group-3.png" alt="Avatar utilisateur">
  <div>
    <?php if (!empty($user['bio'])): ?>
      <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
    <?php else: ?>
      <p class="muted">Pas de bio.</p>
    <?php endif; ?>

    <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] !== (int)$user['id']): ?>
      <a class="btn" href="<?= $base ?>/messages/thread?user=<?= (int)$user['id'] ?>">Contacter</a>
    <?php endif; ?>
  </div>
</section>

<section class="card">
  <h2>Livres du profil</h2>
  <?php if (empty($books)): ?>
    <p class="muted">Aucun livre.</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($books as $b): ?>
        <a class="book" href="<?= $base ?>/books/show?id=<?= (int)$b['id'] ?>">
          <div class="thumb">
            <?php if (!empty($b['image'])): ?>
              <img src="<?= htmlspecialchars($b['image']) ?>" alt="">
            <?php else: ?>
              <img src="<?= $base ?>/assets/img/figma/mask-group.png" alt="Couverture par défaut">
            <?php endif; ?>
          </div>
          <div class="meta">
            <strong><?= htmlspecialchars($b['title']) ?></strong>
            <div class="muted"><?= htmlspecialchars($b['author']) ?></div>
            <div class="muted">statut : <?= htmlspecialchars($b['status']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
