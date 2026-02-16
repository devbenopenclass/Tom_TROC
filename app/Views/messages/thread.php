<?php
use App\Core\View;
use App\Core\Auth;
use App\Core\Csrf;

$me = (int)Auth::id();
?>
<section class="section">
  <a class="back" href="<?= BASE_URL ?>/messages">← Retour</a>
  <h1 class="page-title">Discussion avec <?= View::e($other['username'] ?? '') ?></h1>

  <div class="thread">
    <?php foreach (($messages ?? []) as $m): ?>
      <?php $isMine = ((int)$m['sender_id'] === $me); ?>
      <div class="bubble <?= $isMine ? 'bubble--mine' : 'bubble--theirs' ?>">
        <div class="bubble__content"><?= nl2br(View::e((string)$m['content'])) ?></div>
        <div class="bubble__meta"><?= View::e((string)$m['created_at']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <form class="form thread-form" method="post" action="<?= BASE_URL ?>/messages/send">
    <?= Csrf::input(); ?>
    <input type="hidden" name="receiver_id" value="<?= (int)($other['id'] ?? 0) ?>">
    <label class="field">
      <span>Votre message</span>
      <textarea name="content" rows="3" required></textarea>
    </label>
    <button class="btn btn--primary" type="submit">Envoyer</button>
  </form>
</section>
