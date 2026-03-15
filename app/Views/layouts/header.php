<?php
$base = \App\Core\Url::baseUrl();
$isLogged = !empty($_SESSION['user_id']);
$unreadCount = 0;
if ($isLogged) {
  $unreadCount = \App\Models\Message::unreadCount((int)$_SESSION['user_id']);
}
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TomTroc</title>
  <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
</head>
<body>
<header class="site-header <?= $isLogged ? 'is-auth' : '' ?>">
  <div class="shell header-row">
    <a class="brand" href="<?= $base ?>/" aria-label="Accueil TomTroc">
      <img src="<?= $base ?>/assets/img/figma/logo.svg" alt="TomTroc">
    </a>

    <input id="nav-toggle" class="nav-toggle" type="checkbox" aria-hidden="true">
    <label for="nav-toggle" class="menu-trigger" aria-label="Menu">
      <img src="<?= $base ?>/assets/img/figma/icon-menu.svg" alt="">
    </label>

    <nav class="main-nav" aria-label="Navigation principale">
      <div class="nav-group nav-left">
        <a href="<?= $base ?>/">Accueil</a>
        <a href="<?= $base ?>/books/exchange">Nos livres à l'échange</a>
      </div>

      <div class="nav-sep" aria-hidden="true"></div>

      <div class="nav-group nav-right">
        <?php if ($isLogged): ?>
          <a class="icon-link" href="<?= $base ?>/messages">
            <img src="<?= $base ?>/assets/img/figma/icon-messagerie.svg" alt="">
            <span>Messagerie</span>
            <?php if ($unreadCount > 0): ?>
              <span class="notif"><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
            <?php endif; ?>
          </a>
          <a class="icon-link" href="<?= $base ?>/account">
            <img src="<?= $base ?>/assets/img/figma/icon-mon-compte.svg" alt="">
            <span><strong>Mon compte</strong></span>
          </a>

          <form action="<?= $base ?>/logout" method="post" class="inline logout-form">
            <button class="text-link" type="submit">Déconnexion</button>
          </form>
        <?php else: ?>
          <a class="text-link" href="<?= $base ?>/login"><strong>Connexion</strong></a>
        <?php endif; ?>
      </div>
    </nav>
  </div>
</header>

<main class="shell main-content">
