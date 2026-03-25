<?php use App\Core\Csrf; ?>
<?php // Vue d'inscription : création d'un nouveau compte membre. ?>
<section class="page-head">
  <div>
    <p class="kicker">TomTroc</p>
    <h1>Inscription</h1>
    <p>Crée ton profil et commence tes échanges.</p>
  </div>
  <img src="<?= $base ?>/assets/img/figma/group-10.svg" alt="Décor coeur">
</section>

<section class="auth-wrap">
  <article class="auth-panel">
    <?php if (!empty($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

    <form method="post" action="<?= $base ?>/register" class="form form-wide">
      <?= Csrf::input(); ?>
      <label class="mini-label">Pseudo</label>
      <input name="username" required>

      <label class="mini-label">Email</label>
      <input name="email" type="email" required>

      <label class="mini-label">Mot de passe</label>
      <input name="password" type="password" required>

      <label class="mini-label">Confirmer</label>
      <input name="confirm" type="password" required>

      <button class="btn" type="submit">Créer le compte</button>
    </form>

    <p class="muted">Déjà un compte ? <a href="<?= $base ?>/login"><strong>Connectez-vous</strong></a></p>
  </article>

  <figure class="auth-visual">
    <img src="<?= $base ?>/assets/img/figma/mask-group.png" alt="Couverture de livre">
  </figure>
</section>
