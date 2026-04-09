<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Models\Book;

// Contrôleur de la page d'accueil : récupère les derniers livres ajoutés
// et les formate pour les cartes de présentation.
class HomeController extends Controller
{
  // Charge jusqu'à 4 livres récents pour les afficher en page d'accueil.
  public function index(): void
  {
    $latest = Book::latest(4);
    $this->render('home/index', ['cards' => $this->buildHomeCards($latest)]);
  }

  // Affiche la page des mentions légales.
  public function legalNotice(): void
  {
    $this->render('home/legal_notice');
  }

  // Affiche la page de politique de confidentialité.
  public function privacyPolicy(): void
  {
    $this->render('home/privacy_policy');
  }

  // Prépare les cartes à afficher en home.
  // Si la base de données est vide, on complète avec des livres de démonstration
  // pour que la page ne soit jamais vide au premier démarrage.
  private function buildHomeCards(array $latest): array
  {
    // Livres de démo pour remplir les slots manquants si la base est vide
    $fallback = [
      ['title' => 'Esther', 'author' => 'Alabaster', 'owner' => 'CamilleDuCuir'],
      ['title' => 'The Kinfolk Table', 'author' => 'Nathan Williams', 'owner' => 'Nathalie'],
      ['title' => 'Wabi Sabi', 'author' => 'Beth Kempton', 'owner' => 'Alicecture'],
      ['title' => 'Milk & honey', 'author' => 'Rupi Kaur', 'owner' => 'jugo1980_17'],
    ];

    $cards = [];

    // On traite en priorité les livres réels venant de la base
    foreach (array_slice($latest, 0, 4) as $book) {
      $cards[] = [
        'title' => (string)($book['title'] ?? ''),
        'author' => (string)($book['author'] ?? ''),
        'owner' => (string)($book['username'] ?? ''),
        'img' => Url::asset(Book::imagePath($book)),
        'url' => '/books/show?id=' . (int)($book['id'] ?? 0),
      ];
    }

    // Si on a moins de 4 livres réels, on complète avec les démos
    if (count($cards) < 4) {
      foreach (array_slice($fallback, 0, 4 - count($cards)) as $book) {
        $cards[] = [
          'title' => $book['title'],
          'author' => $book['author'],
          'owner' => $book['owner'],
          'img' => Url::asset(Book::imagePath($book)),
          // Les livres de démo pointent vers le catalogue général, pas une fiche précise
          'url' => '/books/exchange',
        ];
      }
    }

    return $cards;
  }
}
