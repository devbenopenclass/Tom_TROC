<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;
use App\Models\Book;

class AccountController extends Controller
{
  public function index(): void
  {
    Auth::requireLogin();
    $me = User::find(Auth::id());
    $books = Book::byUser(Auth::id());
    $this->render('account/index', ['me' => $me, 'books' => $books]);
  }

  public function editProfileForm(): void
  {
    Auth::requireLogin();
    $me = User::find(Auth::id());
    $this->render('account/profile_edit', ['me' => $me]);
  }

  public function updateProfile(): void
  {
    Auth::requireLogin();

    $username = trim($_POST['username'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    if ($username === '') {
      $this->render('account/profile_edit', [
        'error' => 'Le pseudo est obligatoire.',
        'me' => User::find(Auth::id())
      ]);
      return;
    }

    User::updateProfile(Auth::id(), $username, $bio);
    $this->redirect('/account');
  }
}
