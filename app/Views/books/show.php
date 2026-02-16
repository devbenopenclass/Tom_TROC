<?php
use App\Core\View;
use App\Core\Auth;
use App\Core\Csrf;

$img = $book['image'] ?? null;
$cover = $img ? '/assets/uploads/' . $img : '/assets/img/logo@2x.png';
$me = Auth::id();
?>
<section class="section">
  <a class="back" href="<?= BASE_URL ?>/books">← Retour</a>

  <div class="book-show">
    <img class="book-show__img" src="<?= View::e($cover) ?>" alt="">
    <div class="book-show__body">
      <h1 class="page-title"><?= View::e($book['title'] ?? '') ?></h1>
      <div class="muted">Auteur : <?= View::e($book['author'] ?? '') ?></div>
      <div class="muted">Statut : <?= View::e($book['status'] ?? '') ?></div>

      <?php if (!empty($book['description'])): ?>
        <p class="book-show__desc"><?= nl2br(View::e($book['description'])) ?></p>
      <?php endif; ?>

      <div class="book-show__actions">
        <a class="btn btn--outline" href="<?= BASE_URL ?>/profile?id=<?= (int)$book['owner_id'] ?>">Voir le profil du propriétaire</a>

        <?php if ($me && (int)$book['owner_id'] !== (int)$me): ?>
          <form method="post" action="<?= BASE_URL ?>/messages/send" class="inline">
            <?= Csrf::input(); ?>
            <input type="hidden" name="receiver_id" value="<?= (int)$book['owner_id'] ?>">
            <input type="hidden" name="content" value="Bonjour ! Je suis intéressé par votre livre « <?= View::e($book['title'] ?? '') ?> »." />
            <button class="btn btn--primary" type="submit">Envoyer un message</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
