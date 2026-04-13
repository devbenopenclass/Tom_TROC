<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Book;
use App\Presenters\ProfilePresenter;

// Affiche les profils publics des membres et leur bibliothèque.
class ProfileController extends Controller
{
  private ProfilePresenter $profilePresenter;

  public function __construct()
  {
    parent::__construct();
    $this->profilePresenter = new ProfilePresenter();
  }

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
      'profileView' => $this->profilePresenter->buildView($user, $books),
    ]);
  }
}
