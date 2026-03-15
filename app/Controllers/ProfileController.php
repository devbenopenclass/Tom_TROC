<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Book;

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
    $this->render('profiles/show', ['user' => $user, 'books' => $books]);
  }
}
