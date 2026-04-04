<?php
use App\Core\Csrf;
use App\Core\Url;

$base = Url::baseUrl();
$isLogged = !empty($_SESSION['user_id']);
$isAdmin = $isLogged && \App\Models\User::isAdmin((int)$_SESSION['user_id']);
$unreadCount = 0;
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$normalizedPath = $requestPath;
if ($base !== '' && str_starts_with($normalizedPath, $base)) {
  $normalizedPath = substr($normalizedPath, strlen($base)) ?: '/';
}
$normalizedPath = '/' . ltrim($normalizedPath, '/');
$isAccountPage = str_contains($requestPath, '/account');
$isMessagesPage = str_contains($requestPath, '/messages');
$isAdminPage = str_starts_with($normalizedPath, '/admin');
if ($isLogged) {
  $unreadCount = \App\Models\Message::unreadCount((int)$_SESSION['user_id']);
}

$backFallback = '/';
if (str_starts_with($normalizedPath, '/account/profile')) {
  $backFallback = '/account';
} elseif (str_starts_with($normalizedPath, '/account')) {
  $backFallback = '/';
} elseif (str_starts_with($normalizedPath, '/books/create') || str_starts_with($normalizedPath, '/books/edit')) {
  $backFallback = '/account';
} elseif (str_starts_with($normalizedPath, '/books/show')) {
  $backFallback = '/books/exchange';
} elseif (str_starts_with($normalizedPath, '/books/exchange')) {
  $backFallback = '/';
} elseif (str_starts_with($normalizedPath, '/profiles/show')) {
  $backFallback = '/books/exchange';
} elseif (str_starts_with($normalizedPath, '/messages')) {
  $backFallback = '/account';
} elseif (str_starts_with($normalizedPath, '/admin')) {
  $backFallback = '/account';
}

$showBackMenu = $normalizedPath !== '/' && !$isAdminPage;
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TomTroc</title>
  <link rel="stylesheet" href="<?= htmlspecialchars(Url::asset('/assets/css/style.css')) ?>">
  <?php if ($isAccountPage): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(Url::asset('/assets/css/account-admin.css')) ?>">
  <?php endif; ?>
  <?php if ($isAdminPage): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars(Url::asset('/assets/css/admin.css')) ?>">
  <?php endif; ?>
</head>
<body class="<?= trim(($isAccountPage ? 'account-admin-page ' : '') . ($isMessagesPage ? 'messages-page ' : '') . ($isAdminPage ? 'admin-page' : '')) ?>">
<header class="site-header <?= $isLogged ? 'is-auth' : '' ?>">
  <div class="shell header-row">
    <a class="brand" href="<?= $base ?>/" aria-label="Accueil TomTroc">
      <img class="brand-logo" src="<?= htmlspecialchars(Url::asset('/assets/img/figma/logo.svg')) ?>" alt="TomTroc">
    </a>

    <input id="nav-toggle" class="nav-toggle" type="checkbox" aria-hidden="true">
    <label for="nav-toggle" class="menu-trigger" aria-label="Menu">
      <img src="<?= $base ?>/assets/img/figma/icon-menu.svg" alt="">
    </label>

    <nav class="main-nav" aria-label="Navigation principale">
      <div class="nav-group nav-left">
        <a href="<?= $base ?>/">Accueil</a>
        <a href="<?= $base ?>/books/exchange">Livres à l'échange</a>
      </div>

      <div class="nav-sep" aria-hidden="true"></div>

      <div class="nav-group nav-right">
        <?php if ($isLogged): ?>
          <?php if ($isAdmin): ?>
            <a class="text-link" href="<?= $base ?>/admin/books"><strong>Administration</strong></a>
          <?php endif; ?>
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
            <?= Csrf::input(); ?>
            <button class="text-link" type="submit">Déconnexion</button>
          </form>
        <?php else: ?>
          <a class="text-link" href="<?= $base ?>/login"><strong>Connexion</strong></a>
        <?php endif; ?>
      </div>
    </nav>
  </div>
</header>

<main class="shell main-content<?= $isMessagesPage ? ' main-content--messages' : '' ?>">
<?php if ($showBackMenu): ?>
  <div class="back-nav">
    <a
      class="back-link back-link--menu"
      href="<?= htmlspecialchars(Url::withBase($backFallback)) ?>"
    >← Retour</a>
  </div>
<?php endif; ?>
