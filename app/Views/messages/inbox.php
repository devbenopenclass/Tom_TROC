<?php use App\Models\Book; ?>
<?php use App\Models\User; ?>

<section class="messages-layout">
  <aside class="messages-sidebar">
    <div class="messages-sidebar__head">
      <h1>Messagerie</h1>
    </div>
    <?php if (empty($items)): ?>
      <p class="muted">Aucune conversation pour le moment.</p>
    <?php else: ?>
      <div class="conversation-list">
        <?php foreach ($items as $item): ?>
          <?php
          $avatar = User::avatarPath($item);
          $avatarFile = __DIR__ . '/../../../public' . $avatar;
          $avatarVersion = is_file($avatarFile) ? (string)filemtime($avatarFile) : '1';
          $avatar = $base . $avatar . '?v=' . $avatarVersion;
          $isActive = (int)($activeUserId ?? 0) === (int)$item['other_id'];
          ?>
          <a class="conversation-item <?= $isActive ? 'is-active' : '' ?>" href="<?= $base ?>/messages?user=<?= (int)$item['other_id'] ?>">
            <img src="<?= htmlspecialchars($avatar) ?>" alt="">
            <div class="conversation-item__body">
              <strong><?= htmlspecialchars($item['other_username']) ?></strong>
              <span><?= htmlspecialchars(mb_strimwidth((string)$item['last_message'], 0, 44, '…')) ?></span>
            </div>
            <div class="conversation-item__meta">
              <small><?= htmlspecialchars(date('d.m', strtotime((string)$item['last_at']))) ?></small>
              <?php if ((int)$item['unread_count'] > 0): ?>
                <span class="conversation-item__badge"><?= (int)$item['unread_count'] ?></span>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </aside>

  <section class="messages-thread">
    <?php if (empty($other)): ?>
      <div class="messages-empty">
        <h2>Sélectionne une conversation</h2>
        <p class="muted">Choisis un membre à gauche pour commencer à discuter.</p>
      </div>
    <?php else: ?>
      <?php
      $threadAvatar = User::avatarPath($other);
      $threadAvatarFile = __DIR__ . '/../../../public' . $threadAvatar;
      $threadAvatarVersion = is_file($threadAvatarFile) ? (string)filemtime($threadAvatarFile) : '1';
      $threadAvatar = $base . $threadAvatar . '?v=' . $threadAvatarVersion;
      ?>
      <header class="thread-head">
        <img src="<?= htmlspecialchars($threadAvatar) ?>" alt="">
        <div>
          <h2><?= htmlspecialchars($other['username']) ?></h2>
        </div>
      </header>

      <?php if (!empty($bookContext)): ?>
        <?php
        $bookImage = Book::imagePath($bookContext);
        if (!preg_match('#^https?://#i', $bookImage)) {
          $bookFile = __DIR__ . '/../../../public' . $bookImage;
          $bookVersion = is_file($bookFile) ? (string)filemtime($bookFile) : '1';
          $bookImage = $base . $bookImage . '?v=' . $bookVersion;
        }
        ?>
        <div class="thread-book-context">
          <img src="<?= htmlspecialchars($bookImage) ?>" alt="">
          <div>
            <strong>À propos de ce livre</strong>
            <span><?= htmlspecialchars($bookContext['title']) ?>, par <?= htmlspecialchars($bookContext['author']) ?></span>
          </div>
        </div>
      <?php endif; ?>

      <div class="thread-feed">
        <?php if (empty($messages)): ?>
          <p class="muted">
            <?= !empty($canCompose)
              ? "Aucun message pour l'instant. Tu peux écrire le premier message."
              : "Aucun message dans cette conversation pour le moment." ?>
          </p>
        <?php else: ?>
          <?php foreach ($messages as $m):
            $isMe = (int)$m['sender_id'] === (int)($_SESSION['user_id'] ?? 0);
            ?>
            <article class="bubble <?= $isMe ? 'me' : '' ?>">
              <?php if (!$isMe): ?>
                <div class="bubble-author">
                  <img src="<?= htmlspecialchars($threadAvatar) ?>" alt="">
                  <span class="bubble-meta"><?= htmlspecialchars(date('d.m H:i', strtotime((string)$m['created_at']))) ?></span>
                </div>
              <?php else: ?>
                <span class="bubble-meta bubble-meta--me"><?= htmlspecialchars(date('d.m H:i', strtotime((string)$m['created_at']))) ?></span>
              <?php endif; ?>
              <div class="bubble-body"><?= nl2br(htmlspecialchars($m['content'])) ?></div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php if (!empty($canCompose) && !empty($bookContext)): ?>
        <form method="post" action="<?= $base ?>/messages/send" class="thread-form">
          <input type="hidden" name="receiver_id" value="<?= (int)$other['id'] ?>">
          <input type="hidden" name="book_id" value="<?= (int)$bookContext['id'] ?>">
          <textarea name="content" rows="4" required placeholder="Écrire un message à propos de ce livre..."></textarea>
          <button class="btn" type="submit">Envoyer</button>
        </form>
      <?php elseif (!empty($canCompose)): ?>
        <form method="post" action="<?= $base ?>/messages/send" class="thread-form">
          <input type="hidden" name="receiver_id" value="<?= (int)$other['id'] ?>">
          <textarea name="content" rows="4" required placeholder="Tapez votre message ici"></textarea>
          <button class="btn" type="submit">Envoyer</button>
        </form>
      <?php else: ?>
        <div class="thread-locked"></div>
      <?php endif; ?>
    <?php endif; ?>
  </section>
</section>
