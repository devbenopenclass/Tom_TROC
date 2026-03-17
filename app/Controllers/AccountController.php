<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;
use App\Models\Book;

class AccountController extends Controller
{
  private function renderAccountPage(array $extra = []): void
  {
    $me = User::find(Auth::id());
    $books = Book::byUser(Auth::id());
    $this->render('account/index', array_merge([
      'me' => $me,
      'books' => $books,
    ], $extra));
  }

  public function index(): void
  {
    Auth::requireLogin();
    $this->renderAccountPage();
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
    $password = trim($_POST['password'] ?? '');
    $passwordConfirm = trim($_POST['password_confirm'] ?? '');

    if ($username === '') {
      $this->renderAccountPage([
        'error' => 'Le pseudo est obligatoire.',
        'form' => [
          'username' => $username,
          'bio' => $bio,
        ],
      ]);
      return;
    }

    if ($password !== '' && mb_strlen($password) < 6) {
      $this->renderAccountPage([
        'error' => 'Le mot de passe doit contenir au moins 6 caractères.',
        'form' => [
          'username' => $username,
          'bio' => $bio,
        ],
      ]);
      return;
    }

    if ($password !== $passwordConfirm) {
      $this->renderAccountPage([
        'error' => 'La confirmation du mot de passe ne correspond pas.',
        'form' => [
          'username' => $username,
          'bio' => $bio,
        ],
      ]);
      return;
    }

    $passwordHash = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : null;

    User::updateProfile(Auth::id(), $username, $bio, $passwordHash);
    $this->renderAccountPage([
      'success' => $passwordHash !== null
        ? 'Profil mis à jour. Le mot de passe a bien été modifié.'
        : 'Profil mis à jour.',
    ]);
  }
}
