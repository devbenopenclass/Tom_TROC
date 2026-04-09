<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Models\Book;

// Contrôleur de la page d'accueil :
// récupère les derniers livres et alimente le home.
class HomeController extends Controller
{
  public function index(): void
  {
    $latest = Book::latest(4);
    $this->render('home/index', ['cards' => $this->buildHomeCards($latest)]);
  }

  public function legalNotice(): void
  {
    $this->render('home/legal_notice');
  }

  public function privacyPolicy(): void
  {
    $this->render('home/privacy_policy');
  }

  private function buildHomeCards(array $latest): array
  {
    $fallback = [
      ['title' => 'Esther', 'author' => 'Alabaster', 'owner' => 'CamilleDuCuir'],
      ['title' => 'The Kinfolk Table', 'author' => 'Nathan Williams', 'owner' => 'Nathalie'],
      ['title' => 'Wabi Sabi', 'author' => 'Beth Kempton', 'owner' => 'Alicecture'],
      ['title' => 'Milk & honey', 'author' => 'Rupi Kaur', 'owner' => 'jugo1980_17'],
    ];

    $cards = [];
    foreach (array_slice($latest, 0, 4) as $book) {
      $cards[] = [
        'title' => (string)($book['title'] ?? ''),
        'author' => (string)($book['author'] ?? ''),
        'owner' => (string)($book['username'] ?? ''),
        'img' => Url::asset(Book::imagePath($book)),
        'url' => '/books/show?id=' . (int)($book['id'] ?? 0),
      ];
    }

    if (count($cards) < 4) {
      foreach (array_slice($fallback, 0, 4 - count($cards)) as $book) {
        $cards[] = [
          'title' => $book['title'],
          'author' => $book['author'],
          'owner' => $book['owner'],
          'img' => Url::asset(Book::imagePath($book)),
          'url' => '/books/exchange',
        ];
      }
    }

    return $cards;
  }
}
