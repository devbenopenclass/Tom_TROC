<?php use App\Core\Csrf; ?>
<?php use App\Core\Url; ?>
<?php use App\Models\User; ?>
<?php $hasActiveThread = !empty($other); ?>

<section class="messages-layout<?= $hasActiveThread ? ' messages-layout--thread-open' : ' messages-layout--list-open' ?>">
  <aside class="messages-sidebar card">
    <h2>Messagerie</h2>
    <?php if (empty($items)): ?>
      <p class="muted">Aucune conversation pour le moment.</p>
    <?php else: ?>
      <div class="conversation-list">
        <?php foreach ($items as $item): ?>
          <?php
          $avatar = Url::asset(User::avatarPath($item));
          $isActive = (int)($activeUserId ?? 0) === (int)$item['other_id'];
          ?>
          <a class="conversation-item <?= $isActive ? 'is-active' : '' ?>" href="<?= $base ?>/messages?user=<?= (int)$item['other_id'] ?>">
            <img src="<?= htmlspecialchars($avatar) ?>" alt="">
            <div class="conversation-item__body">
              <strong><?= htmlspecialchars($item['other_username']) ?></strong>
              <span><?= htmlspecialchars(mb_strimwidth((string)$item['last_message'], 0, 72, '…')) ?></span>
            </div>
            <div class="conversation-item__meta">
              <small><?= htmlspecialchars(date('d/m H:i', strtotime((string)$item['last_at']))) ?></small>
              <?php if ((int)$item['unread_count'] > 0): ?>
                <span class="conversation-item__badge"><?= (int)$item['unread_count'] ?></span>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </aside>

  <section class="messages-thread card">
    <?php if (empty($other)): ?>
      <div class="messages-empty">
        <h2>Sélectionne une conversation</h2>
        <p class="muted">Choisis un membre à gauche pour commencer à discuter.</p>
      </div>
    <?php else: ?>
      <?php $threadAvatar = Url::asset(User::avatarPath($other)); ?>
      <header class="thread-head">
        <img src="<?= htmlspecialchars($threadAvatar) ?>" alt="">
        <div>
          <h2><?= htmlspecialchars($other['username']) ?></h2>
          <p class="muted"><?= (int)($other['books_count'] ?? 0) ?> livre(s) partage(s)</p>
        </div>
      </header>

      <div class="thread-feed">
        <?php if (empty($messages)): ?>
          <p class="muted">Aucun message pour l'instant. Écris le premier.</p>
        <?php else: ?>
          <?php foreach ($messages as $m):
            $isMe = (int)$m['sender_id'] === (int)($_SESSION['user_id'] ?? 0);
            ?>
            <article class="bubble <?= $isMe ? 'me' : '' ?>">
              <strong><?= htmlspecialchars($m['sender_name']) ?></strong><br>
              <?= nl2br(htmlspecialchars($m['content'])) ?>
              <span class="bubble-meta"><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string)$m['created_at']))) ?></span>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php if (!empty($canCompose)): ?>
        <form method="post" action="<?= $base ?>/messages/send" class="thread-form">
          <?= Csrf::input(); ?>
          <input type="hidden" name="receiver_id" value="<?= (int)$other['id'] ?>">
          <?php if (!empty($bookContext)): ?>
            <input type="hidden" name="book_id" value="<?= (int)$bookContext['id'] ?>">
          <?php endif; ?>
          <textarea name="content" rows="4" required placeholder="Écrire un message..."></textarea>
          <button class="btn" type="submit">Envoyer</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</section>
