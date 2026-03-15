<section class="page-head">
  <div>
    <p class="kicker">Conversation</p>
    <h1><?= htmlspecialchars($other['username']) ?></h1>
    <p>Échangez autour des livres.</p>
  </div>
  <img src="<?= $base ?>/assets/img/figma/icon-messagerie.svg" alt="Icône messagerie">
</section>

<section class="card">
  <div class="thread">
    <?php if (empty($messages)): ?>
      <p class="muted">Aucun message pour l'instant.</p>
    <?php else: ?>
      <?php foreach ($messages as $m):
        $isMe = (int)$m['sender_id'] === (int)($_SESSION['user_id'] ?? 0);
      ?>
        <article class="bubble <?= $isMe ? 'me' : '' ?>">
          <strong><?= htmlspecialchars($m['sender_name']) ?></strong><br>
          <?= nl2br(htmlspecialchars($m['content'])) ?>
          <span class="bubble-meta"><?= htmlspecialchars($m['created_at']) ?></span>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <form method="post" action="<?= $base ?>/messages/send" class="form form-wide">
    <input type="hidden" name="receiver_id" value="<?= (int)$other['id'] ?>">
    <label class="mini-label">Votre message</label>
    <textarea name="content" rows="4" required placeholder="Écrire un message..."></textarea>
    <button class="btn" type="submit">Envoyer</button>
  </form>
</section>
