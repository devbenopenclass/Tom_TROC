<?php
declare(strict_types=1);

namespace App\Presenters;

use App\Core\Url;
use App\Models\Book;
use App\Models\User;

// Prépare les données de la page "Mon compte" pour la vue.
final class AccountPresenter
{
  public function buildView(?array $me, array $books, array $form = []): array
  {
    return [
      'avatar' => Url::asset(User::avatarPath($me, '/assets/img/figma/mask-group-2.png')),
      'username' => (string)($me['username'] ?? ''),
      'username_value' => (string)($form['username'] ?? ($me['username'] ?? '')),
      'email' => (string)($me['email'] ?? ''),
      'bio' => (string)($me['bio'] ?? ''),
      'member_since' => $this->memberSince($me),
      'books_count' => count($books),
      'book_rows' => $this->bookRows($books),
    ];
  }

  private function memberSince(?array $me): string
  {
    if (empty($me['created_at'])) {
      return '1 an';
    }

    $years = max(1, (int)date('Y') - (int)date('Y', strtotime((string)$me['created_at'])));
    return $years . ' an' . ($years > 1 ? 's' : '');
  }

  private function bookRows(array $books): array
  {
    $rows = [];

    foreach ($books as $book) {
      $description = $this->shortDescription((string)($book['description'] ?? ''));
      $isAvailable = ($book['status'] ?? '') === 'available';

      $rows[] = [
        'id' => (int)($book['id'] ?? 0),
        'title' => (string)($book['title'] ?? ''),
        'author' => (string)($book['author'] ?? ''),
        'cover' => Url::asset(Book::imagePath($book)),
        'description' => $description,
        'status_class' => $isAvailable ? 'status-pill--ok' : 'status-pill--off',
        'status_label' => $isAvailable ? 'disponible' : 'indisponible',
      ];
    }

    return $rows;
  }

  private function shortDescription(string $description): string
  {
    $description = trim($description);
    if ($description === '') {
      return 'Aucune description.';
    }

    if (mb_strlen($description) > 110) {
      return mb_substr($description, 0, 110) . '...';
    }

    return $description;
  }
}
