<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

// Contrôleur d'authentification : formulaires d'inscription,
// connexion, déconnexion et validation des identifiants.
class AuthController extends Controller
{
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

    if ($username === '' || $email === '' || $password === '' || $password !== $confirm) {
      $this->render('auth/register', ['error' => 'Champs invalides ou mots de passe différents.']);
      return;
    }

    if (User::findByEmail($email)) {
      $this->render('auth/register', ['error' => 'Email déjà utilisé.']);
      return;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $id = User::create($username, $email, $hash);

    session_regenerate_id(true);
    $_SESSION['user_id'] = $id;
    $this->redirect('/account');
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
      $this->render('auth/login', ['error' => 'Identifiants invalides.']);
      return;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $this->redirect('/account');
  }

  public function logout(): void
  {
    $this->requireCsrf();
    $_SESSION = [];
    session_destroy();
    $this->redirect('/');
  }
}
