<section class="page-head">
  <div>
    <p class="kicker">Profil</p>
    <h1>Modifier mon profil</h1>
    <p>Mets à jour ton pseudo et ta bio.</p>
  </div>
  <img src="<?= $base ?>/assets/img/figma/vector-2.svg" alt="Décor">
</section>

<section class="card">
  <?php if (!empty($error)): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

  <form method="post" action="<?= $base ?>/account/profile" class="form form-wide">
    <label class="mini-label">Pseudo</label>
    <input name="username" value="<?= htmlspecialchars($me['username'] ?? '') ?>" required>

    <label class="mini-label">Bio</label>
    <textarea name="bio" rows="6"><?= htmlspecialchars($me['bio'] ?? '') ?></textarea>

    <button class="btn" type="submit">Enregistrer</button>
  </form>
</section>
