<?php use App\Core\Url; ?>
<?php use App\Models\Book; ?>
<?php use App\Models\User; ?>
<?php // Profil public d'un membre : avatar, bio et livres visibles par les autres utilisateurs. ?>

<?php
// Le profil public réutilise la même règle d'avatar que le compte connecté.
$avatar = Url::asset(User::avatarPath($user));
?>

<section class="page-head">
  <div>
    <p class="kicker">Profil public</p>
    <h1><?= htmlspecialchars($user['username']) ?></h1>
    <p>Profil public et bibliothèque partagée.</p>
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
      <a class="btn" href="<?= $base ?>/messages/thread?user=<?= (int)$user['id'] ?>">Contacter</a>
    <?php endif; ?>
  </div>
</section>

<section class="card">
  <h2>Bibliothèque</h2>
  <?php if (empty($books)): ?>
    <p class="muted">Aucun livre dans cette bibliothèque.</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($books as $b): ?>
        <?php
        // Chaque carte reprend les mêmes helpers que le catalogue public.
        $image = Url::asset(Book::imagePath($b));
        $status = (string)($b['status'] ?? 'available');
        $statusLabel = $status === 'reserved' ? 'réservé' : ($status === 'unavailable' ? 'indisponible' : 'disponible');
        ?>
        <a class="book" href="<?= $base ?>/books/show?id=<?= (int)$b['id'] ?>">
          <div class="thumb">
            <img src="<?= htmlspecialchars($image) ?>" alt="">
          </div>
          <div class="meta">
            <strong><?= htmlspecialchars($b['title']) ?></strong>
            <div class="muted"><?= htmlspecialchars($b['author']) ?></div>
            <div class="muted">Disponibilité : <?= htmlspecialchars($statusLabel) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
