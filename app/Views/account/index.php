<?php use App\Models\Book; ?>
<?php use App\Models\User; ?>

<?php
$avatar = User::avatarPath($me, '/assets/img/figma/mask-group-2.png');
$avatarFile = __DIR__ . '/../../../public' . $avatar;
$avatarVersion = is_file($avatarFile) ? (string)filemtime($avatarFile) : '1';
$avatar = $base . $avatar . '?v=' . $avatarVersion;
$memberSince = '1 an';
if (!empty($me['created_at'])) {
  $years = max(1, (int)date('Y') - (int)date('Y', strtotime((string)$me['created_at'])));
  $memberSince = $years . ' an' . ($years > 1 ? 's' : '');
}
?>

<section class="account-admin">
  <div class="account-admin__shell">
    <h1 class="account-admin__title">Mon compte</h1>

    <div class="account-admin__top">
      <article class="account-card account-card--profile">
        <div class="account-profile__avatar-wrap">
          <img class="account-profile__avatar" src="<?= htmlspecialchars($avatar) ?>" alt="Avatar">
          <a class="account-profile__edit" href="<?= $base ?>/account/profile">modifier</a>
        </div>

        <div class="account-profile__identity">
          <h1 class="account-profile__name"><?= htmlspecialchars($me['username'] ?? '') ?></h1>
          <p class="account-profile__since">Membre depuis <?= htmlspecialchars($memberSince) ?></p>
          <p class="account-profile__library">Bibliothèque</p>
          <p class="account-profile__count"><?= count($books) ?> livres</p>
        </div>
      </article>

      <article class="account-card">
        <h2 class="account-form__title">Vos informations personnelles</h2>
        <form method="post" action="<?= $base ?>/account/profile" class="account-form">
          <label class="account-form__field">
            <span>Adresse email</span>
            <input value="<?= htmlspecialchars($me['email'] ?? '') ?>" readonly>
          </label>

          <label class="account-form__field">
            <span>Mot de passe</span>
            <input type="password" value="troc" readonly>
          </label>

          <label class="account-form__field">
            <span>Pseudo</span>
            <input name="username" value="<?= htmlspecialchars($me['username'] ?? '') ?>" required>
          </label>

          <input type="hidden" name="bio" value="<?= htmlspecialchars($me['bio'] ?? '') ?>">
          <button class="btn btn-outline account-form__submit" type="submit">Enregistrer</button>
        </form>
      </article>
    </div>

    <section class="account-books">
      <div class="account-books__head">
        <span>Photo</span>
        <span>Titre</span>
        <span>Auteur</span>
        <span>Description</span>
        <span>Disponibilité</span>
        <span>Action</span>
      </div>

      <?php if (empty($books)): ?>
        <div class="account-books__empty">Vous n'avez pas encore ajouté de livre.</div>
      <?php else: ?>
        <?php foreach ($books as $b): ?>
          <?php
          $cover = Book::imagePath($b);
          if (!preg_match('#^https?://#i', $cover)) {
            $coverFile = __DIR__ . '/../../../public' . $cover;
            $coverVersion = is_file($coverFile) ? (string)filemtime($coverFile) : '1';
            $cover = $base . $cover . '?v=' . $coverVersion;
          }
          $isAvailable = ($b['status'] ?? '') === 'available';
          $desc = trim((string)($b['description'] ?? ''));
          if ($desc === '') {
            $desc = 'Aucune description.';
          }
          if (mb_strlen($desc) > 110) {
            $desc = mb_substr($desc, 0, 110) . '...';
          }
          ?>
          <div class="account-books__row">
            <div class="account-books__photo">
              <img src="<?= htmlspecialchars($cover) ?>" alt="">
            </div>
            <div>
              <a href="<?= $base ?>/books/show?id=<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['title']) ?></a>
            </div>
            <div><?= htmlspecialchars($b['author']) ?></div>
            <div class="account-books__desc"><?= htmlspecialchars($desc) ?></div>
            <div>
              <span class="status-pill <?= $isAvailable ? 'status-pill--ok' : 'status-pill--off' ?>">
                <?= $isAvailable ? 'disponible' : 'non dispo.' ?>
              </span>
            </div>
            <div class="account-books__actions">
              <a href="<?= $base ?>/books/edit?id=<?= (int)$b['id'] ?>">Éditer</a>
              <form action="<?= $base ?>/books/delete" method="post" class="inline">
                <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                <button type="submit" onclick="return confirm('Supprimer ?')">Supprimer</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
</section>
