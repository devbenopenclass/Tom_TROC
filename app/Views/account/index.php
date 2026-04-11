<?php use App\Core\Csrf; ?>
<?php // Tableau de bord "Mon compte" : profil, informations personnelles et bibliothèque du membre connecté. ?>

<section class="account-admin">
  <div class="account-admin__shell">
    <h1 class="account-admin__title">Mon compte</h1>

    <div class="account-admin__top">
      <article class="account-card account-card--profile">
        <div class="account-profile__avatar-wrap">
          <img class="account-profile__avatar" src="<?= htmlspecialchars($accountView['avatar']) ?>" alt="Avatar">
          <a class="account-profile__edit" href="<?= $base ?>/account/profile">modifier</a>
        </div>

        <div class="account-profile__identity">
          <h2 class="account-profile__name"><?= htmlspecialchars($accountView['username']) ?></h2>
          <p class="account-profile__since">Membre depuis <?= htmlspecialchars($accountView['member_since']) ?></p>
          <p class="account-profile__library">Ma bibliothèque</p>
          <p class="account-profile__count">
            <span class="account-profile__count-icon" aria-hidden="true">
              <svg viewBox="0 0 16 16" focusable="false">
                <path d="M3.75 2.25C3.75 1.97 3.97 1.75 4.25 1.75H7c.28 0 .5.22.5.5v10.5c0 .28-.22.5-.5.5H4.25a.5.5 0 0 1-.5-.5V2.25Z" />
                <path d="M8.5 2.25c0-.28.22-.5.5-.5h2.75c.28 0 .5.22.5.5v10.5c0 .28-.22.5-.5.5H9a.5.5 0 0 1-.5-.5V2.25Z" />
                <path d="M7.5 3.5h1" />
              </svg>
            </span>
            <span><?= (int)$accountView['books_count'] ?> livres</span>
          </p>
        </div>
      </article>

      <article class="account-card">
        <h2 class="account-form__title">Vos informations personnelles</h2>
        <?php if (!empty($error)): ?><p class="account-form__message account-form__message--error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <?php if (!empty($success)): ?><p class="account-form__message account-form__message--success"><?= htmlspecialchars($success) ?></p><?php endif; ?>
        <form method="post" action="<?= $base ?>/account/profile" class="account-form" enctype="multipart/form-data">
          <?= Csrf::input(); ?>
          <label class="account-form__field account-form__field--avatar">
            <span>Avatar</span>
            <span class="account-form__avatar-row">
              <img class="account-form__avatar-preview" src="<?= htmlspecialchars($accountView['avatar']) ?>" alt="Avatar actuel">
              <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp">
            </span>
          </label>

          <label class="account-form__field">
            <span>Adresse email</span>
            <input value="<?= htmlspecialchars($accountView['email']) ?>" readonly>
          </label>

          <label class="account-form__field">
            <span>Mot de passe</span>
            <input type="password" name="password" value="" placeholder="Nouveau mot de passe">
          </label>

          <label class="account-form__field">
            <span>Confirmation du mot de passe</span>
            <input type="password" name="password_confirm" value="" placeholder="Confirmer le mot de passe">
          </label>

          <label class="account-form__field">
            <span>Pseudo</span>
            <input name="username" value="<?= htmlspecialchars($accountView['username_value']) ?>" required>
          </label>

          <input type="hidden" name="bio" value="<?= htmlspecialchars($accountView['bio']) ?>">
          <button class="btn btn-outline account-form__submit" type="submit">Enregistrer</button>
        </form>

        <form method="post" action="<?= $base ?>/account/delete" class="account-delete">
          <?= Csrf::input(); ?>
          <button class="account-delete__button" type="submit">Supprimer mon compte</button>
        </form>
      </article>
    </div>

    <section class="account-books">
      <div class="account-books__toolbar">
        <h2 class="account-books__title">Mes livres</h2>
        <a class="btn account-books__add" href="<?= $base ?>/books/create">Ajouter un livre</a>
      </div>

      <div class="account-books__head">
        <span>Photo</span>
        <span>Titre</span>
        <span>Auteur</span>
        <span>Description</span>
        <span>Disponibilité</span>
        <span>Action</span>
      </div>

      <?php if (empty($accountView['book_rows'])): ?>
        <div class="account-books__empty">Vous n'avez pas encore ajouté de livre.</div>
      <?php else: ?>
        <?php foreach ($accountView['book_rows'] as $b): ?>
          <div class="account-books__row">
            <div class="account-books__photo">
              <span class="account-books__mobile-label">Photo</span>
              <img src="<?= htmlspecialchars($b['cover']) ?>" alt="">
            </div>
            <div>
              <span class="account-books__mobile-label">Titre</span>
              <a href="<?= $base ?>/books/show?id=<?= (int)$b['id'] ?>"><?= htmlspecialchars($b['title']) ?></a>
            </div>
            <div>
              <span class="account-books__mobile-label">Auteur</span>
              <?= htmlspecialchars($b['author']) ?>
            </div>
            <div class="account-books__desc">
              <span class="account-books__mobile-label">Description</span>
              <?= htmlspecialchars($b['description']) ?>
            </div>
            <div>
              <span class="account-books__mobile-label">Disponibilité</span>
              <span class="status-pill <?= htmlspecialchars($b['status_class']) ?>">
                <?= htmlspecialchars($b['status_label']) ?>
              </span>
            </div>
            <div class="account-books__actions">
              <span class="account-books__mobile-label">Action</span>
              <a href="<?= $base ?>/books/edit?id=<?= (int)$b['id'] ?>">Éditer</a>
              <form action="<?= $base ?>/books/delete" method="post" class="inline">
                <?= Csrf::input(); ?>
                <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                <button type="submit">Supprimer</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
</section>
