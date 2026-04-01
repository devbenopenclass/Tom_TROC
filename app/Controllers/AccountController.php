<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;
use App\Models\Book;

// Contrôleur de l'espace "Mon compte" :
// affiche le compte connecté et enregistre ses modifications.
class AccountController extends Controller
{
  private const MIN_PASSWORD_LENGTH = 6;

  private function renderAccountPage(array $extra = []): void
  {
    $me = User::find($this->currentUserId());
    $books = Book::byUser($this->currentUserId());
    $this->render('account/index', array_merge([
      'me' => $me,
      'books' => $books,
    ], $extra));
  }

  public function index(): void
  {
    $this->requireAccountLogin();
    $this->renderAccountPage();
  }

  public function editProfileForm(): void
  {
    $this->requireAccountLogin();
    $me = User::find($this->currentUserId());
    $this->render('account/profile_edit', ['me' => $me]);
  }

  public function updateProfile(): void
  {
    $this->requireAccountLogin();
    $this->requireCsrf();

    $username = trim($_POST['username'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $passwordConfirm = trim($_POST['password_confirm'] ?? '');
    $formData = [
      'username' => $username,
      'bio' => $bio,
    ];

    if ($username === '') {
      $this->renderAccountError('Le pseudo est obligatoire.', $formData);
      return;
    }

    if ($password !== '' && mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
      $this->renderAccountError('Le mot de passe doit contenir au moins 6 caractères.', $formData);
      return;
    }

    if ($password !== $passwordConfirm) {
      $this->renderAccountError('La confirmation du mot de passe ne correspond pas.', $formData);
      return;
    }

    $passwordHash = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : null;

    User::updateProfile($this->currentUserId(), $username, $bio, $passwordHash);
    $this->renderAccountPage([
      'success' => $passwordHash !== null
        ? 'Compte mis à jour. Le mot de passe a bien été modifié.'
        : 'Compte mis à jour.',
    ]);
  }

  private function requireAccountLogin(): void
  {
    Auth::requireLogin();
  }

  private function currentUserId(): int
  {
    return (int) Auth::id();
  }

  private function renderAccountError(string $message, array $formData): void
  {
    $this->renderAccountPage([
      'error' => $message,
      'form' => $formData,
    ]);
  }
}
