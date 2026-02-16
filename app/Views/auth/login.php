<?php
use App\Core\Csrf;
?>
<section class="auth">
  <h1 class="page-title">Connexion</h1>

  <form class="form" method="post" action="<?= BASE_URL ?>/login">
    <?= Csrf::input(); ?>

    <label class="field">
      <span>Email</span>
      <input type="email" name="email" required>
    </label>

    <label class="field">
      <span>Mot de passe</span>
      <input type="password" name="password" required>
    </label>

    <button class="btn btn--primary" type="submit">Se connecter</button>
  </form>

  <p class="muted">Pas encore de compte ? <a href="<?= BASE_URL ?>/register">Créer un compte</a></p>
</section>
