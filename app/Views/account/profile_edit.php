<?php use App\Core\Csrf; ?>
<?php use App\Core\Url; ?>
<?php use App\Models\User; ?>
<?php // Vue d'édition rapide du compte : pseudo, bio et mot de passe. ?>
<?php
$errorMessage = trim((string)($error ?? ''));
$username = (string)($me['username'] ?? '');
$bio = (string)($me['bio'] ?? '');
$avatar = Url::asset(User::avatarPath($me, '/assets/img/figma/mask-group-2.png'));
$passwordFields = [
  ['label' => 'Nouveau mot de passe', 'name' => 'password', 'placeholder' => 'Laisser vide pour ne pas changer'],
  ['label' => 'Confirmation du mot de passe', 'name' => 'password_confirm', 'placeholder' => 'Confirmer le mot de passe'],
];
?>
<section class="page-head">
  <div>
    <p class="kicker">Mon compte</p>
    <h1>Modifier mon compte</h1>
    <p>Mets à jour ton pseudo, ta bio et ton mot de passe.</p>
  </div>
  <img src="<?= $base ?>/assets/img/figma/vector-2.svg" alt="Décor">
</section>

<section class="card">
  <?php if ($errorMessage !== ''): ?><p class="error"><?= htmlspecialchars($errorMessage) ?></p><?php endif; ?>

  <form method="post" action="<?= $base ?>/account/profile" class="form form-wide" enctype="multipart/form-data">
    <?= Csrf::input(); ?>
    <label class="mini-label" for="profile-avatar">Avatar</label>
    <div class="profile-avatar-field">
      <img class="profile-avatar-field__preview" src="<?= htmlspecialchars($avatar) ?>" alt="Avatar actuel">
      <input id="profile-avatar" name="avatar" type="file" accept="image/png,image/jpeg,image/webp">
    </div>

    <label class="mini-label" for="profile-username">Pseudo</label>
    <input id="profile-username" name="username" value="<?= htmlspecialchars($username) ?>" autocomplete="username" required>

    <label class="mini-label" for="profile-bio">Bio</label>
    <textarea id="profile-bio" name="bio" rows="6"><?= htmlspecialchars($bio) ?></textarea>

    <?php foreach ($passwordFields as $field): ?>
      <label class="mini-label" for="profile-<?= htmlspecialchars($field['name']) ?>"><?= htmlspecialchars($field['label']) ?></label>
      <input
        id="profile-<?= htmlspecialchars($field['name']) ?>"
        type="password"
        name="<?= htmlspecialchars($field['name']) ?>"
        autocomplete="new-password"
        placeholder="<?= htmlspecialchars($field['placeholder']) ?>"
      >
    <?php endforeach; ?>

    <button class="btn" type="submit">Enregistrer</button>
  </form>
</section>
