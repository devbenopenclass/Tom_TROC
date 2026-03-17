<?php use App\Models\Book; ?>
<?php use App\Models\User; ?>

<?php
$avatar = User::avatarPath($user);
$avatarFile = __DIR__ . '/../../../public' . $avatar;
$avatarVersion = is_file($avatarFile) ? (string)filemtime($avatarFile) : '1';
$avatar = $base . $avatar . '?v=' . $avatarVersion;
?>

<section class="page-head">
  <div>
    <p class="kicker">Profil public</p>
    <h1><?= htmlspecialchars($user['username']) ?></h1>
    <p>Bibliothèque partagée et informations du membre.</p>
  </div>
  <img src="<?= $base ?>/assets/img/figma/icon-mon-compte.svg" alt="Icône profil">
</section>

<section class="card profile-header">
  <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar utilisateur">
  <div>
    <?php if (!empty($user['bio'])): ?>
      <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
    <?php else: ?>
      <p class="muted">Pas de bio.</p>
    <?php endif; ?>
    <?php if (!empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] !== (int)$user['id']): ?>
      <p class="muted">Pour contacter ce membre, ouvre d'abord la fiche d'un de ses livres.</p>
    <?php endif; ?>
  </div>
</section>

<section class="card">
  <h2>Livres du profil</h2>
  <?php if (empty($books)): ?>
    <p class="muted">Aucun livre.</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($books as $b): ?>
        <?php
        $image = Book::imagePath($b);
        if (!preg_match('#^https?://#i', $image)) {
          $assetFile = __DIR__ . '/../../../public' . $image;
          $imageVersion = is_file($assetFile) ? (string)filemtime($assetFile) : '1';
          $image = $base . $image . '?v=' . $imageVersion;
        }
        ?>
        <a class="book" href="<?= $base ?>/books/show?id=<?= (int)$b['id'] ?>">
          <div class="thumb">
            <img src="<?= htmlspecialchars($image) ?>" alt="">
          </div>
          <div class="meta">
            <strong><?= htmlspecialchars($b['title']) ?></strong>
            <div class="muted"><?= htmlspecialchars($b['author']) ?></div>
            <div class="muted">statut : <?= htmlspecialchars($b['status']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
