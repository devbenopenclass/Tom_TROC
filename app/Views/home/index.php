<?php use App\Core\Url; ?>
<?php // Page d'accueil : hero, derniers livres, étapes de fonctionnement et valeurs du projet. ?>
<section class="home-hero">
  <div class="home-copy">
    <h1>Rejoignez nos<br>lecteurs passionnés</h1>
    <p>
      Donnez une nouvelle vie à vos livres en les échangeant avec d'autres amoureux de la lecture.
      Nous croyons en la magie du partage de connaissances et d'histoires à travers les livres.
    </p>
    <a class="btn" href="<?= $base ?>/books/exchange">Découvrir</a>
  </div>

  <figure class="home-hero-image">
    <img src="<?= $base ?>/assets/img/figma/hero-reader.png" alt="Lecteur dans une librairie">
    <figcaption>Hamza</figcaption>
  </figure>
</section>

<section class="home-section home-section--framed">
  <h2>Les derniers livres ajoutés</h2>

  <div class="home-books">
    <?php foreach ($cards as $c): ?>
      <a class="home-book" href="<?= $base . $c['url'] ?>">
        <div class="img-wrap"><img src="<?= htmlspecialchars($c['img']) ?>" alt=""></div>
        <div class="txt">
          <strong><?= htmlspecialchars($c['title']) ?></strong>
          <div><?= htmlspecialchars($c['author']) ?></div>
          <small>Vendu par : <?= htmlspecialchars($c['owner']) ?></small>
        </div>
      </a>
    <?php endforeach; ?>
  </div>

  <p class="center-btn latest-books-cta latest-books-cta--home"><a class="btn" href="<?= $base ?>/books/exchange">Voir tous les livres</a></p>
</section>

<section class="home-section howto">
  <h2>Comment ça marche ?</h2>
  <p>Échanger des livres avec TomTroc c'est simple et amusant ! Suivez ces étapes pour commencer :</p>

  <div class="steps">
    <div class="step">Inscrivez-vous gratuitement sur notre plateforme.</div>
    <div class="step">Ajoutez les livres que vous souhaitez échanger à votre bibliothèque.</div>
    <div class="step">Parcourez les livres disponibles chez d'autres membres.</div>
    <div class="step">Proposez un échange et discutez avec d'autres passionnés de lecture.</div>
  </div>

  <p class="center-btn center-btn--spaced"><a class="btn btn-outline" href="<?= $base ?>/books/exchange">Voir tous les livres</a></p>
</section>

<section class="home-banner">
  <img src="<?= htmlspecialchars(Url::asset('/assets/img/figma/mask-group-1.png')) ?>" alt="Bannière bibliothèque">
</section>

<section class="home-section values">
  <h2>Nos valeurs</h2>
  <p>Chez Tom Troc, nous mettons l'accent sur le partage, la découverte et la communauté. Nos valeurs sont ancrées dans notre passion pour les livres et notre désir de créer des liens entre les lecteurs.</p>
  <p>Nous croyons en la puissance des histoires pour rassembler les gens et inspirer des conversations enrichissantes.</p>
  <p>Notre association a été fondée avec une conviction profonde : chaque livre mérite d'être lu et partagé.</p>
  <p>Nous sommes passionnés par la création d'une plateforme conviviale qui permet aux lecteurs de se connecter, de partager leurs découvertes littéraires et d'échanger des livres qui attendent patiemment sur les étagères.</p>
  <small>L'équipe Tom Troc</small>
  <img class="values-heart" src="<?= $base ?>/assets/img/figma/vector-2b.svg" alt="Décor coeur">
</section>
