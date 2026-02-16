<?php
use App\Core\Auth;
use App\Core\Session;
use App\Core\View;

$config = require CONFIG_PATH . '/config.php';
$appName = $config['app']['name'] ?? 'Tom Troc';

$user = Auth::user();
$success = Session::flash('success');
$error = Session::flash('error');
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= View::e($appName) ?></title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/base.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/layout.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/home.css">
</head>
<body>
  <?php require APP_PATH . '/Views/layouts/header.php'; ?>

  <main>
    <?php if ($success): ?>
      <div class="page-flash-wrap">
        <div class="flash flash--success"><?= View::e($success) ?></div>
      </div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="page-flash-wrap">
        <div class="flash flash--error"><?= View::e($error) ?></div>
      </div>
    <?php endif; ?>

    <?= $content ?>
  </main>

  <?php require APP_PATH . '/Views/layouts/footer.php'; ?>
</body>
</html>
