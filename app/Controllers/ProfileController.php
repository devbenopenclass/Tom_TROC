<?php
namespace App\Controllers;

use App\Core\Controller;
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
      $this->renderStatusPage(404, 'errors/404', [
        'title' => 'Profil introuvable',
        'message' => "Le profil demandé n'existe pas ou n'est plus disponible.",
      ]);
      return;
    }

    $books = Book::byUser($id);
    $this->render('profiles/show', ['user' => $user, 'books' => $books]);
  }
}
