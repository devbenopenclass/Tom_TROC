<?php
$adminTitle = (string)($adminTitle ?? '');
$adminDescription = (string)($adminDescription ?? '');
$adminActiveTab = (string)($adminActiveTab ?? 'books');
$adminSectionEyebrow = (string)($adminSectionEyebrow ?? '');
$adminSectionTitle = (string)($adminSectionTitle ?? '');
$adminSectionMeta = (string)($adminSectionMeta ?? '');
$adminSearchAction = (string)($adminSearchAction ?? '');
$adminSearchPlaceholder = (string)($adminSearchPlaceholder ?? '');
$adminQuery = (string)($adminQuery ?? '');
?>
<section class="admin-hero">
  <div class="admin-hero__copy">
    <p class="admin-hero__eyebrow">Espace management</p>
    <h1><?= \App\Core\View::e($adminTitle) ?></h1>
    <p><?= \App\Core\View::e($adminDescription) ?></p>
  </div>
</section>

<section class="admin-banner">
  <img src="<?= htmlspecialchars(\App\Core\Url::asset('/assets/img/figma/mask-group-1.png')) ?>" alt="Bannière bibliothèque">
</section>

<section class="admin-panel">
  <div class="admin-panel__shell">
    <nav class="admin-tabs" aria-label="Navigation management">
      <a class="admin-tabs__link<?= $adminActiveTab === 'books' ? ' admin-tabs__link--active' : '' ?>" href="<?= $base ?>/admin/books">Livres</a>
      <a class="admin-tabs__link<?= $adminActiveTab === 'members' ? ' admin-tabs__link--active' : '' ?>" href="<?= $base ?>/admin/members">Membres</a>
    </nav>

    <div class="admin-section-head">
      <div>
        <p class="admin-section-head__eyebrow"><?= \App\Core\View::e($adminSectionEyebrow) ?></p>
        <h2><?= \App\Core\View::e($adminSectionTitle) ?></h2>
      </div>
      <p class="admin-section-head__meta"><?= \App\Core\View::e($adminSectionMeta) ?></p>
    </div>

    <form class="admin-search" method="get" action="<?= $adminSearchAction ?>">
      <label class="admin-search__field">
        <span>Recherche</span>
        <input
          type="search"
          name="q"
          value="<?= \App\Core\View::e($adminQuery) ?>"
          placeholder="<?= \App\Core\View::e($adminSearchPlaceholder) ?>"
        >
      </label>
      <button class="btn" type="submit">Rechercher</button>
      <?php if ($adminQuery !== ''): ?>
        <a class="btn btn-outline" href="<?= $adminSearchAction ?>">Effacer</a>
      <?php endif; ?>
    </form>
