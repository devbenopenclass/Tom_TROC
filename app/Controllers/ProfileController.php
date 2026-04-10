<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Url;
use App\Models\User;
use App\Models\Book;

// Affiche les profils publics des membres et leur bibliothèque.
class ProfileController extends Controller
{
  // Charge le profil d'un membre à partir de son id en paramètre d'URL.
  public function show(): void
  {
    $id = (int)($_GET['id'] ?? 0);
    $user = $id ? User::find($id) : null;

    // Si le membre n'existe pas, on retourne un 404 explicite
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

  // Prépare toutes les données dont la vue de profil a besoin :
  // infos du membre, ses livres et le droit de le contacter.
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
        // On traduit le statut technique en libellé lisible pour la vue
        'status_label' => $status === 'reserved' ? 'réservé' : ($status === 'unavailable' ? 'indisponible' : 'disponible'),
      ];
    }

    $currentUserId = (int)($_SESSION['user_id'] ?? 0);
    $profileUserId = (int)($user['id'] ?? 0);

    return [
      'id' => $profileUserId,
      'username' => (string)($user['username'] ?? ''),
      'bio' => (string)($user['bio'] ?? ''),
      'avatar' => Url::asset(User::avatarPath($user)),
      // On peut contacter le membre seulement si on est connecté et que ce n'est pas soi-même
      'can_contact' => $currentUserId > 0 && $currentUserId !== $profileUserId,
      // Le propriétaire du profil peut ajouter un livre depuis sa propre page
      'is_own_profile' => $currentUserId > 0 && $currentUserId === $profileUserId,
      'books' => $bookCards,
    ];
  }
}
