<?php use App\Core\Url; ?>
<?php use App\Models\Book; ?>
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
    <?php
    // Jeux de secours gardés pour le rendu de la maquette si la base est vide.
    $fallback = [
      ['title' => 'Esther', 'author' => 'Alabaster', 'owner' => 'CamilleDuCuir'],
      ['title' => 'The Kinfolk Table', 'author' => 'Nathan Williams', 'owner' => 'Nathalie'],
      ['title' => 'Wabi Sabi', 'author' => 'Beth Kempton', 'owner' => 'Alicecture'],
      ['title' => 'Milk & honey', 'author' => 'Rupi Kaur', 'owner' => 'jugo1980_17'],
    ];

    $cards = [];
    if (!empty($latest)) {
      foreach (array_slice($latest, 0, 4) as $i => $b) {
        $cards[] = [
          'title' => $b['title'] ?? '',
          'author' => $b['author'] ?? '',
          'owner' => $b['username'] ?? '',
          'img' => Url::asset(Book::imagePath($b)),
          'id' => (int)($b['id'] ?? 0),
        ];
      }
    }

    // On complète visuellement jusqu'à 4 cartes si nécessaire.
    if (count($cards) < 4) {
      foreach (array_slice($fallback, 0, 4 - count($cards)) as $f) {
        $f['img'] = Url::asset(Book::imagePath($f));
        $cards[] = $f;
      }
    }
    ?>

    <?php foreach ($cards as $c): ?>
      <?php $url = !empty($c['id']) ? ($base . '/books/show?id=' . (int)$c['id']) : ($base . '/books/exchange'); ?>
      <a class="home-book" href="<?= $url ?>">
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
