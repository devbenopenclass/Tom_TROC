<section class="page-head">
  <div>
    <p class="kicker">Messagerie</p>
    <h1>Mes conversations</h1>
    <p>Retrouve tes derniers échanges.</p>
  </div>
  <img src="<?= $base ?>/assets/img/figma/icon-messagerie.svg" alt="Icône messagerie">
</section>

<section class="card">
  <?php if (empty($items)): ?>
    <p class="muted">Aucun message.</p>
  <?php else: ?>
    <ul class="list">
      <?php foreach ($items as $m):
        $me = (int)($_SESSION['user_id'] ?? 0);
        $otherId = ((int)$m['sender_id'] === $me) ? (int)$m['receiver_id'] : (int)$m['sender_id'];
      ?>
        <li class="list-item">
          <div>
            <a href="<?= $base ?>/messages/thread?user=<?= $otherId ?>"><strong>Conversation #<?= $otherId ?></strong></a>
            <div class="muted"><?= htmlspecialchars(mb_strimwidth($m['content'], 0, 100, '…')) ?></div>
          </div>
          <small class="muted"><?= htmlspecialchars($m['created_at']) ?></small>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
</section>
