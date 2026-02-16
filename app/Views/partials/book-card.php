<?php
use App\Core\View;

$img = $b['image'] ?? null;
$cover = $img ? '/assets/uploads/' . $img : '/assets/img/Accueil.png';
?>
<a class="book-card" href="<?= BASE_URL ?>/books/show?id=<?= (int)$b['id'] ?>">
  <img class="book-card__img" src="<?= View::e($cover) ?>" alt="Couverture du livre <?= View::e($b['title'] ?? '') ?>">
  <div class="book-card__body">
    <div class="book-card__title"><?= View::e($b['title'] ?? '') ?></div>
    <div class="book-card__meta"><?= View::e($b['author'] ?? '') ?></div>
    <div class="book-card__owner">Vendu par : <?= View::e($b['username'] ?? '') ?></div>
  </div>
</a>
