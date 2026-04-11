<?php // Profil public d'un membre : avatar, bio et livres visibles par les autres utilisateurs. ?>

<section class="page-head">
  <div>
    <p class="kicker">Profil public</p>
    <h1><?= htmlspecialchars($profileView['username']) ?></h1>
    <p>Profil public et bibliothèque partagée.</p>
  </div>
  <img src="<?= $base ?>/assets/img/figma/icon-mon-compte.svg" alt="">
</section>

<div class="card profile-header">
  <img src="<?= htmlspecialchars($profileView['avatar']) ?>" alt="Avatar utilisateur">
  <div>
    <?php if (!empty($profileView['bio'])): ?>
      <p><?= nl2br(htmlspecialchars($profileView['bio'])) ?></p>
    <?php else: ?>
      <p class="muted">Pas de bio.</p>
    <?php endif; ?>

    <?php if ($profileView['can_contact']): ?>
      <a class="btn" href="<?= $base ?>/messages/thread?user=<?= (int)$profileView['id'] ?>">Contacter</a>
    <?php endif; ?>
  </div>
</div>

<section class="card">
  <div class="account-books__toolbar">
    <h2>Bibliothèque</h2>
    <?php if ($profileView['is_own_profile']): ?>
      <a class="btn account-books__add" href="<?= $base ?>/books/create">Ajouter un livre</a>
    <?php endif; ?>
  </div>
  <?php if (empty($profileView['books'])): ?>
    <p class="muted">Aucun livre dans cette bibliothèque.</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($profileView['books'] as $b): ?>
        <a class="book" href="<?= $base ?>/books/show?id=<?= (int)$b['id'] ?>">
          <div class="thumb">
            <img src="<?= htmlspecialchars($b['image']) ?>" alt="">
          </div>
          <div class="meta">
            <strong><?= htmlspecialchars($b['title']) ?></strong>
            <div class="muted"><?= htmlspecialchars($b['author']) ?></div>
            <div class="muted">Disponibilité : <?= htmlspecialchars($b['status_label']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
