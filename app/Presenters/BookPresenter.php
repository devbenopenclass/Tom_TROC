<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Core\Auth;
use App\Core\Url;
use App\Models\Book;
use App\Models\User;

// Prépare les données d'affichage liées aux livres.
// Cette classe garde les contrôleurs centrés sur le flux HTTP.
final class BookPresenter
{
  // Formate les livres pour les cartes du catalogue public.
  public function exchangeCards(array $books): array
  {
    $cards = [];

    foreach ($books as $book) {
      $isAvailable = ($book['status'] ?? '') === 'available';
      $cards[] = [
        'id' => (int)($book['id'] ?? 0),
        'title' => (string)($book['title'] ?? ''),
        'author' => (string)($book['author'] ?? ''),
        'owner' => (string)($book['username'] ?? ''),
        'image' => Url::asset(Book::imagePath($book)),
        'status_class' => $isAvailable ? 'book-status--ok' : 'book-status--off',
        'status_label' => $isAvailable ? 'disponible' : 'non dispo.',
      ];
    }

    return $cards;
  }

  // Prépare toutes les données nécessaires à la vue "fiche livre".
  public function showView(array $book): array
  {
    $description = Book::detailDescription($book);
    $paragraphs = preg_split("/\n\s*\n/", $description) ?: [$description];

    return [
      'id' => (int)($book['id'] ?? 0),
      'user_id' => (int)($book['user_id'] ?? 0),
      'title' => trim((string)($book['title'] ?? 'Livre')),
      'author' => trim((string)($book['author'] ?? 'Auteur inconnu')),
      'owner' => trim((string)($book['username'] ?? 'membre de la communauté')),
      'image' => Url::asset(Book::detailImagePath($book, '/assets/img/figma/mask-group-1.png')),
      'owner_avatar' => Url::asset(User::avatarPath($book)),
      'paragraphs' => array_map(static fn (string $paragraph): string => trim($paragraph), $paragraphs),
      'can_message_owner' => Auth::check(),
    ];
  }
}
