<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

// Contrôleur d'authentification : formulaires d'inscription,
// connexion, déconnexion et validation des identifiants.
class AuthController extends Controller
{
  private const ACCOUNT_PATH = '/account';

  public function registerForm(): void
  {
    $this->render('auth/register');
  }

  public function register(): void
  {
    $this->requireCsrf();

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm'] ?? '');

    if ($this->hasInvalidRegistrationInput($username, $email, $password, $confirm)) {
      $this->renderAuthError('auth/register', 'Champs invalides ou mots de passe différents.');
      return;
    }

    if (User::findByEmail($email)) {
      $this->renderAuthError('auth/register', 'Email déjà utilisé.');
      return;
    }

    $this->loginUser(User::create($username, $email, password_hash($password, PASSWORD_BCRYPT)));
    $this->redirect(self::ACCOUNT_PATH);
  }

  public function loginForm(): void
  {
    $this->render('auth/login');
  }

  public function login(): void
  {
    $this->requireCsrf();

    $login = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    $user = User::findByLogin($login);
    if (!$user || !password_verify($password, $user['password_hash'])) {
      $this->renderAuthError('auth/login', 'Identifiants invalides.');
      return;
    }

    $this->loginUser((int)$user['id']);
    $this->redirect(self::ACCOUNT_PATH);
  }

  public function logout(): void
  {
    $this->requireCsrf();
    $_SESSION = [];
    session_destroy();
    $this->redirect('/');
  }

  private function hasInvalidRegistrationInput(string $username, string $email, string $password, string $confirm): bool
  {
    return $username === '' || $email === '' || $password === '' || $password !== $confirm;
  }

  private function renderAuthError(string $view, string $message): void
  {
    $this->render($view, ['error' => $message]);
  }

  private function loginUser(int $userId): void
  {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
  }
}
