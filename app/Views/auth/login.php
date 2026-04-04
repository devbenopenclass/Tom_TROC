<?php use App\Core\Csrf; ?>
<?php // Vue de connexion : formulaire de login avec email/pseudo et mot de passe. ?>
<?php
$errorMessage = trim((string)($error ?? ''));
// Même approche que l'inscription pour éviter du HTML dupliqué.
$fields = [
  ['label' => 'Email ou pseudo', 'name' => 'email', 'type' => 'text', 'autocomplete' => 'username'],
  ['label' => 'Mot de passe', 'name' => 'password', 'type' => 'password', 'autocomplete' => 'current-password'],
];
?>
<section class="login-page full-bleed">
  <div class="login-left">
    <h1>Connexion</h1>
    <?php if ($errorMessage !== ''): ?><p class="error"><?= htmlspecialchars($errorMessage) ?></p><?php endif; ?>

    <form method="post" action="<?= $base ?>/login" class="login-form">
      <?= Csrf::input(); ?>
      <?php foreach ($fields as $field): ?>
        <label class="mini-label" for="login-<?= htmlspecialchars($field['name']) ?>"><?= htmlspecialchars($field['label']) ?></label>
        <input
          id="login-<?= htmlspecialchars($field['name']) ?>"
          name="<?= htmlspecialchars($field['name']) ?>"
          type="<?= htmlspecialchars($field['type']) ?>"
          autocomplete="<?= htmlspecialchars($field['autocomplete']) ?>"
          required
        >
      <?php endforeach; ?>

      <button class="btn" type="submit">Connexion</button>
    </form>

    <p>Pas de compte ? <a href="<?= $base ?>/register">Inscrivez-vous</a></p>
  </div>

  <div class="login-right">
    <img src="<?= $base ?>/assets/img/figma/mask-group-1.png" alt="Bibliothèque">
  </div>
</section>
