<?php
use App\Core\Csrf;
use App\Core\View;
?>
<section class="section">
  <h1 class="page-title">Mon compte</h1>

  <div class="account-grid">
    <div class="card">
      <h2 class="card__title">Mon profil</h2>
      <form class="form" method="post" action="<?= BASE_URL ?>/account">
        <?= Csrf::input(); ?>

        <label class="field">
          <span>Nom d’utilisateur</span>
          <input type="text" name="username" value="<?= View::e($user['username'] ?? '') ?>" required>
        </label>

        <label class="field">
          <span>Bio</span>
          <textarea name="bio" rows="5"><?= View::e($user['bio'] ?? '') ?></textarea>
        </label>

        <button class="btn btn--primary" type="submit">Enregistrer</button>
      </form>
    </div>

    <div class="card">
      <h2 class="card__title">Ma bibliothèque</h2>
      <p class="muted">Gérez les livres que vous proposez à l’échange.</p>
      <a class="btn btn--outline" href="<?= BASE_URL ?>/library">Accéder à ma bibliothèque</a>
    </div>
  </div>
</section>
