<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Core\Url;
use App\Models\Book;
use App\Models\User;

// Prépare les données d'un profil public pour la vue.
final class ProfilePresenter
{
  public function buildView(array $user, array $books): array
  {
    $currentUserId = (int)($_SESSION['user_id'] ?? 0);
    $profileUserId = (int)($user['id'] ?? 0);

    return [
      'id' => $profileUserId,
      'username' => (string)($user['username'] ?? ''),
      'bio' => (string)($user['bio'] ?? ''),
      'avatar' => Url::asset(User::avatarPath($user)),
      'can_contact' => $currentUserId > 0 && $currentUserId !== $profileUserId,
      'is_own_profile' => $currentUserId > 0 && $currentUserId === $profileUserId,
      'books' => $this->bookCards($books),
    ];
  }

  private function bookCards(array $books): array
  {
    $cards = [];

    foreach ($books as $book) {
      $status = (string)($book['status'] ?? 'available');
      $cards[] = [
        'id' => (int)($book['id'] ?? 0),
        'title' => (string)($book['title'] ?? ''),
        'author' => (string)($book['author'] ?? ''),
        'image' => Url::asset(Book::imagePath($book)),
        'status_label' => $status === 'reserved' ? 'réservé' : ($status === 'unavailable' ? 'indisponible' : 'disponible'),
      ];
    }

    return $cards;
  }
}
