<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Models\User;
use App\Models\Book;

// Contrôleur des profils publics : affiche un membre
// et la bibliothèque rattachée à son compte.
class ProfileController extends Controller
{
  public function show(): void
  {
    $id = (int)($_GET['id'] ?? 0);
    $user = $id ? User::find($id) : null;

    if (!$user) {
      http_response_code(404);
      echo "Profil introuvable";
      return;
    }

    $books = Book::byUser($id);
    $this->render('profiles/show', [
      'profileView' => $this->buildProfileView($user, $books),
    ]);
  }

  private function buildProfileView(array $user, array $books): array
  {
    $bookCards = [];
    foreach ($books as $book) {
      $status = (string)($book['status'] ?? 'available');
      $bookCards[] = [
        'id' => (int)($book['id'] ?? 0),
        'title' => (string)($book['title'] ?? ''),
        'author' => (string)($book['author'] ?? ''),
        'image' => Url::asset(Book::imagePath($book)),
        'status_label' => $status === 'reserved' ? 'réservé' : ($status === 'unavailable' ? 'indisponible' : 'disponible'),
      ];
    }

    return [
      'id' => (int)($user['id'] ?? 0),
      'username' => (string)($user['username'] ?? ''),
      'bio' => (string)($user['bio'] ?? ''),
      'avatar' => Url::asset(User::avatarPath($user)),
      'can_contact' => !empty($_SESSION['user_id']) && (int)$_SESSION['user_id'] !== (int)$user['id'],
      'books' => $bookCards,
    ];
  }
}
