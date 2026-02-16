<?php
use App\Core\Csrf;
?>
<section class="auth">
  <h1 class="page-title">Inscription</h1>

  <form class="form" method="post" action="<?= BASE_URL ?>/register">
    <?= Csrf::input(); ?>

    <label class="field">
      <span>Nom d’utilisateur</span>
      <input type="text" name="username" required>
    </label>

    <label class="field">
      <span>Email</span>
      <input type="email" name="email" required>
    </label>

    <label class="field">
      <span>Mot de passe</span>
      <input type="password" name="password" minlength="6" required>
    </label>

    <button class="btn btn--primary" type="submit">Créer mon compte</button>
  </form>

  <p class="muted">Déjà inscrit ? <a href="<?= BASE_URL ?>/login">Se connecter</a></p>
</section>
