<?php
use App\Core\View;
?>
<section class="hero-block">
  <div class="site-shell hero">
    <div class="hero__left">
      <h1 class="hero__title">Rejoignez nos<br>lecteurs passionnés</h1>
      <p class="hero__text">
        Donnez une nouvelle vie à vos livres en les échangeant avec d'autres amoureux de la
        lecture. Nous croyons en la magie du partage de connaissances et d'histoires à
        travers les livres.
      </p>
      <a class="btn btn--primary" href="<?= BASE_URL ?>/books">Découvrir</a>
    </div>

    <div class="hero__right">
      <img class="hero__image" src="<?= BASE_URL ?>/assets/img/Accueil.png" alt="Lecteur entouré de livres">
      <div class="hero__credit">Hamza</div>
    </div>
  </div>
</section>

<section class="latest-block">
  <div class="site-shell">
    <h2 class="section-title">Les derniers livres ajoutés</h2>

    <div class="book-grid">
      <?php foreach (($latest ?? []) as $b): ?>
        <?php require APP_PATH . '/Views/partials/book-card.php'; ?>
      <?php endforeach; ?>
    </div>

    <div class="section-actions">
      <a class="btn btn--primary" href="<?= BASE_URL ?>/books">Voir tous les livres</a>
    </div>
  </div>
</section>

<section class="steps-block">
  <div class="site-shell steps-wrap">
    <h2 class="section-title">Comment ça marche ?</h2>
    <p class="section-subtitle">Échanger des livres avec TomTroc c'est simple et amusant ! Suivez ces étapes pour commencer :</p>

    <div class="steps">
      <article class="step">Inscrivez-vous<br>gratuitement sur<br>notre plateforme.</article>
      <article class="step">Ajoutez les livres que vous<br>souhaitez échanger à<br>votre profil.</article>
      <article class="step">Parcourez les livres<br>disponibles chez d'autres<br>membres.</article>
      <article class="step">Proposez un échange et<br>discutez avec d'autres<br>passionnés de lecture.</article>
    </div>

    <div class="section-actions">
      <a class="btn btn--outline" href="<?= BASE_URL ?>/books">Voir tous les livres</a>
    </div>
  </div>
</section>

<section class="visual-band" aria-hidden="true"></section>

<section class="values-block">
  <div class="site-shell values">
    <div class="values__text">
      <h2 class="section-title section-title--left">Nos valeurs</h2>
      <p>Chez Tom Troc, nous mettons l'accent sur le partage, la découverte et la communauté. Nos valeurs sont ancrées dans notre passion pour les livres et notre désir de créer des liens entre les lecteurs.</p>
      <p>Nous croyons en la puissance des histoires pour rassembler les gens et inspirer des conversations enrichissantes.</p>
      <p>Notre association a été fondée avec une conviction profonde : chaque livre mérite d'être lu et partagé.</p>
      <p>Nous sommes passionnés par la création d'une plateforme conviviale qui permet aux lecteurs de se connecter, de partager leurs découvertes littéraires et d'échanger des livres qui attendent patiemment sur les étagères.</p>
      <p class="values__sig">L'équipe Tom Troc</p>
    </div>

    <div class="values__icon" aria-hidden="true">
      <svg width="120" height="120" viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M60 82C38 62 26 50 26 36C26 27 33 20 42 20C49 20 55 24 60 30C65 24 71 20 78 20C87 20 94 27 94 36C94 50 82 62 60 82Z" stroke="currentColor" stroke-width="2" fill="none"/>
        <path d="M60 82C46 95 35 103 22 110" stroke="currentColor" stroke-width="2" fill="none"/>
      </svg>
    </div>
  </div>
</section>
