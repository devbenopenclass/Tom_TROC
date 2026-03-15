<section class="page-head">
  <div>
    <p class="kicker">Catalogue</p>
    <h1>Nos livres à l'échange</h1>
    <p>Explore la bibliothèque de la communauté.</p>
  </div>
  <img src="<?= $base ?>/assets/img/figma/vector.svg" alt="Décor">
</section>

<section class="card">
  <form method="get" action="<?= $base ?>/books/exchange" class="form form-inline form-wide">
    <div>
      <label class="mini-label">Recherche</label>
      <input name="q" value="<?= htmlspecialchars($q ?? '') ?>" placeholder="Titre, auteur...">
    </div>
    <button class="btn" type="submit">Rechercher</button>
  </form>
</section>

<?php if (empty($books)): ?>
  <section class="card">
    <p class="muted">Aucun livre disponible pour l'instant.</p>
  </section>
<?php else: ?>
  <section class="grid">
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
          <div class="muted">par <?= htmlspecialchars($b['username']) ?></div>
        </div>
      </a>
    <?php endforeach; ?>
  </section>
<?php endif; ?>
