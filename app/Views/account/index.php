<?php
use App\Core\Csrf;
use App\Core\View;

$bookCount = count($books ?? []);
$avatarUrl = !empty($user['avatar']) ? (BASE_URL . '/assets/uploads/' . $user['avatar']) : null;
$initial = strtoupper(substr((string)($user['username'] ?? 'U'), 0, 1));
?>
<section class="account-admin">
  <div class="site-shell">
    <div class="account-admin__top">
      <article class="account-card account-card--profile">
        <div class="account-profile__avatar-wrap">
          <?php if ($avatarUrl): ?>
            <img class="account-profile__avatar" src="<?= View::e($avatarUrl) ?>" alt="Photo de profil">
          <?php else: ?>
            <div class="account-profile__avatar account-profile__avatar--fallback" aria-hidden="true">
              <?= View::e($initial) ?>
            </div>
          <?php endif; ?>
          <button class="account-profile__edit" type="button" disabled>modifier</button>
        </div>

        <div class="account-profile__identity">
          <h1 class="account-profile__name"><?= View::e($user['username'] ?? '') ?></h1>
          <p class="account-profile__since">Membre depuis <?= (int)($memberSinceYears ?? 0) ?> an<?= (int)($memberSinceYears ?? 0) > 1 ? 's' : '' ?></p>
          <p class="account-profile__library">BIBLIOTHEQUE</p>
          <p class="account-profile__count"><?= (int)$bookCount ?> livres</p>
        </div>
      </article>

      <article class="account-card">
        <h2 class="account-form__title">Vos informations personnelles</h2>
        <form class="account-form" method="post" action="<?= BASE_URL ?>/account">
          <?= Csrf::input(); ?>

          <label class="account-form__field">
            <span>Adresse email</span>
            <input type="email" name="email" value="<?= View::e($user['email'] ?? '') ?>" required>
          </label>

          <label class="account-form__field">
            <span>Mot de passe</span>
            <input type="password" name="password" placeholder="••••••••">
          </label>

          <label class="account-form__field">
            <span>Pseudo</span>
            <input type="text" name="username" value="<?= View::e($user['username'] ?? '') ?>" required>
          </label>

          <button class="btn btn--outline account-form__submit" type="submit">Enregistrer</button>
        </form>
      </article>
    </div>

    <div class="account-books">
      <div class="account-books__head">
        <span>PHOTO</span>
        <span>TITRE</span>
        <span>AUTEUR</span>
        <span>DESCRIPTION</span>
        <span>DISPONIBILITE</span>
        <span>ACTION</span>
      </div>

      <?php foreach (($books ?? []) as $book): ?>
        <?php
          $img = !empty($book['image']) ? '/assets/uploads/' . $book['image'] : '/assets/img/logo.png';
          $isAvailable = ($book['status'] ?? '') === 'available';
          $desc = trim((string)($book['description'] ?? ''));
          if ($desc === '') {
              $desc = 'Aucune description.';
          }
          if (strlen($desc) > 90) {
              $desc = substr($desc, 0, 87) . '...';
          }
        ?>
        <div class="account-books__row">
          <div class="account-books__photo">
            <img src="<?= BASE_URL . View::e($img) ?>" alt="">
          </div>
          <div><?= View::e($book['title'] ?? '') ?></div>
          <div><?= View::e($book['author'] ?? '') ?></div>
          <div class="account-books__desc"><?= View::e($desc) ?></div>
          <div>
            <span class="status-pill <?= $isAvailable ? 'status-pill--ok' : 'status-pill--off' ?>">
              <?= $isAvailable ? 'disponible' : 'non dispo.' ?>
            </span>
          </div>
          <div class="account-books__actions">
            <a href="<?= BASE_URL ?>/library/edit?id=<?= (int)$book['id'] ?>">Editer</a>
            <form method="post" action="<?= BASE_URL ?>/library/delete" onsubmit="return confirm('Supprimer ce livre ?');">
              <?= Csrf::input(); ?>
              <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
              <button type="submit">Supprimer</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>

      <?php if (empty($books)): ?>
        <div class="account-books__empty">Aucun livre dans votre bibliothèque pour le moment.</div>
      <?php endif; ?>
    </div>
  </div>
</section>
