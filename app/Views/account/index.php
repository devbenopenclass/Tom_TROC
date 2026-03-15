<section class="page-head">
  <div>
    <p class="kicker">Espace membre</p>
    <h1>Mon compte</h1>
    <p>Gère ton profil et ta bibliothèque.</p>
  </div>
  <img src="<?= $base ?>/assets/img/figma/icon-mon-compte.svg" alt="Icône compte">
</section>

<section class="card profile-header">
  <img src="<?= $base ?>/assets/img/figma/mask-group-2.png" alt="Avatar">
  <div>
    <h2 style="margin-bottom:6px;"><?= htmlspecialchars($me['username'] ?? '') ?></h2>
    <p class="muted" style="margin-bottom:8px;"><?= htmlspecialchars($me['email'] ?? '') ?></p>
    <a class="btn btn-outline" href="<?= $base ?>/account/profile">Modifier mon profil</a>
  </div>
</section>

<section class="card">
  <div class="row" style="margin-bottom:10px;">
    <h2 style="margin:0;">Ma bibliothèque</h2>
    <a class="btn" href="<?= $base ?>/books/create">Ajouter un livre</a>
  </div>

  <?php if (empty($books)): ?>
    <p class="muted">Vous n'avez pas encore ajouté de livre.</p>
  <?php else: ?>
    <ul class="list">
      <?php foreach ($books as $b): ?>
        <li class="list-item">
          <div>
            <a href="<?= $base ?>/books/show?id=<?= (int)$b['id'] ?>"><strong><?= htmlspecialchars($b['title']) ?></strong></a>
            <div class="muted"><?= htmlspecialchars($b['author']) ?> — statut : <?= htmlspecialchars($b['status']) ?></div>
          </div>
          <div class="actions">
            <a href="<?= $base ?>/books/edit?id=<?= (int)$b['id'] ?>">Modifier</a>
            <form action="<?= $base ?>/books/delete" method="post" class="inline">
              <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
              <button class="linkbtn danger" type="submit" onclick="return confirm('Supprimer ?')">Supprimer</button>
            </form>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>
