<?php
use App\Core\View;
?>
<section class="section">
  <h1 class="page-title">Messagerie</h1>

  <div class="table">
    <div class="table__row table__head">
      <div>Conversation</div><div>Dernier message</div><div>Date</div><div></div>
    </div>

    <?php foreach (($conversations ?? []) as $c): ?>
      <?php $other = $c['other']; $last = $c['last']; ?>
      <div class="table__row">
        <div><?= View::e($other['username'] ?? 'Utilisateur') ?></div>
        <div class="muted"><?= View::e(mb_strimwidth((string)($last['content'] ?? ''), 0, 70, '…')) ?></div>
        <div class="muted"><?= View::e((string)($last['created_at'] ?? '')) ?></div>
        <div><a class="btn btn--small" href="<?= BASE_URL ?>/messages/thread?user_id=<?= (int)($other['id'] ?? 0) ?>">Ouvrir</a></div>
      </div>
    <?php endforeach; ?>

    <?php if (empty($conversations)): ?>
      <p class="muted">Aucune conversation pour le moment.</p>
    <?php endif; ?>
  </div>
</section>
