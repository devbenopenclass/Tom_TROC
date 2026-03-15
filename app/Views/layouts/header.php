<?php
use App\Core\Auth;

$user = Auth::user();
?>
<header class="site-header">
  <div class="site-shell site-header__inner">
    <a class="brand" href="<?= BASE_URL ?>/" aria-label="Accueil Tom Troc">
      <img class="brand__logo" src="<?= BASE_URL ?>/assets/img/logo.svg" alt="Tom Troc">
    </a>

    <nav class="nav" aria-label="Navigation principale">
      <a class="nav__link" href="<?= BASE_URL ?>/">Accueil</a>
      <a class="nav__link" href="<?= BASE_URL ?>/books">Nos livres à l'échange</a>
    </nav>

    <nav class="nav nav--right" aria-label="Navigation utilisateur">
      <?php if ($user): ?>
        <a class="nav__link" href="<?= BASE_URL ?>/messages">Messagerie</a>
        <a class="nav__link" href="<?= BASE_URL ?>/account">Mon compte</a>
        <?php if (Auth::isAdmin()): ?>
          <a class="nav__link" href="<?= BASE_URL ?>/admin/books">Admin livres</a>
          <a class="nav__link" href="<?= BASE_URL ?>/admin/members">Membres</a>
        <?php endif; ?>
        <a class="nav__link" href="<?= BASE_URL ?>/logout">Déconnexion</a>
      <?php else: ?>
        <a class="nav__link" href="<?= BASE_URL ?>/messages">Messagerie</a>
        <a class="nav__link" href="<?= BASE_URL ?>/account">Mon compte</a>
        <a class="nav__link" href="<?= BASE_URL ?>/login">Connexion</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
