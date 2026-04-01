<?php use App\Core\Csrf; ?>
<?php // Vue d'inscription : création d'un nouveau compte membre. ?>
<?php
$errorMessage = trim((string)($error ?? ''));
$fields = [
  ['label' => 'Pseudo', 'name' => 'username', 'type' => 'text', 'autocomplete' => 'username'],
  ['label' => 'Email', 'name' => 'email', 'type' => 'email', 'autocomplete' => 'email'],
  ['label' => 'Mot de passe', 'name' => 'password', 'type' => 'password', 'autocomplete' => 'new-password'],
  ['label' => 'Confirmer', 'name' => 'confirm', 'type' => 'password', 'autocomplete' => 'new-password'],
];
?>
<section class="page-head">
  <div>
    <p class="kicker">TomTroc</p>
    <h1>Inscription</h1>
    <p>Crée ton compte et commence tes échanges.</p>
  </div>
  <img src="<?= $base ?>/assets/img/figma/group-10.svg" alt="Décor coeur">
</section>

<section class="auth-wrap">
  <article class="auth-panel">
    <?php if ($errorMessage !== ''): ?><p class="error"><?= htmlspecialchars($errorMessage) ?></p><?php endif; ?>

    <form method="post" action="<?= $base ?>/register" class="form form-wide">
      <?= Csrf::input(); ?>
      <?php foreach ($fields as $field): ?>
        <label class="mini-label" for="register-<?= htmlspecialchars($field['name']) ?>"><?= htmlspecialchars($field['label']) ?></label>
        <input
          id="register-<?= htmlspecialchars($field['name']) ?>"
          name="<?= htmlspecialchars($field['name']) ?>"
          type="<?= htmlspecialchars($field['type']) ?>"
          autocomplete="<?= htmlspecialchars($field['autocomplete']) ?>"
          required
        >
      <?php endforeach; ?>

      <button class="btn" type="submit">Créer le compte</button>
    </form>

    <p class="muted">Déjà un compte ? <a href="<?= $base ?>/login"><strong>Connectez-vous</strong></a></p>
  </article>

  <figure class="auth-visual">
    <img src="<?= $base ?>/assets/img/figma/mask-group.png" alt="Couverture de livre">
  </figure>
</section>
