<?php use App\Core\Csrf; ?>
<?php // Vue de connexion : formulaire de login avec email/pseudo et mot de passe. ?>
<section class="login-page full-bleed">
  <div class="login-left">
    <h1>Connexion</h1>
    <?php if (!empty($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="post" action="<?= $base ?>/login" class="login-form">
      <?= Csrf::input(); ?>
      <label>Email ou pseudo</label>
      <input name="email" type="text" required>

      <label>Mot de passe</label>
      <input name="password" type="password" required>

      <button class="btn" type="submit">Connexion</button>
    </form>

    <p>Pas de compte ? <a href="<?= $base ?>/register">Inscrivez-vous</a></p>
  </div>

  <div class="login-right">
    <img src="<?= $base ?>/assets/img/figma/mask-group-1.png" alt="Bibliothèque">
  </div>
</section>
