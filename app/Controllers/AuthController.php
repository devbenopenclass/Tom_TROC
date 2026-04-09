<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

// Contrôleur d'authentification : inscription, connexion et déconnexion.
// C'est ici que passe tout le monde avant d'accéder à son espace personnel.
class AuthController extends Controller
{
  // Chemin de redirection après une connexion ou inscription réussie
  private const ACCOUNT_PATH = '/account';

  // Affiche simplement le formulaire d'inscription.
  public function registerForm(): void
  {
    $this->render('auth/register');
  }

  // Traite le formulaire d'inscription :
  // on valide les champs, on vérifie l'unicité, puis on crée le compte et on connecte directement.
  public function register(): void
  {
    $this->requireCsrf();

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $confirm = (string)($_POST['confirm'] ?? '');

    // Un seul appel pour valider tous les champs obligatoires d'un coup
    if ($this->hasInvalidRegistrationInput($username, $email, $password, $confirm)) {
      $this->renderAuthError('auth/register', 'Champs invalides ou mots de passe différents.');
      return;
    }

    // L'adresse email doit être unique sur la plateforme
    if (User::findByEmail($email)) {
      $this->renderAuthError('auth/register', 'Email déjà utilisé.');
      return;
    }

    // Le pseudo doit aussi être unique
    if (User::findByUsername($username)) {
      $this->renderAuthError('auth/register', 'Pseudo déjà utilisé.');
      return;
    }

    // On crée le compte et on connecte le membre immédiatement après
    $this->loginUser(User::create($username, $email, password_hash($password, PASSWORD_BCRYPT)));
    $this->redirect(self::ACCOUNT_PATH);
  }

  // Affiche le formulaire de connexion.
  public function loginForm(): void
  {
    $this->render('auth/login');
  }

  // Vérifie les identifiants et ouvre la session si tout est bon.
  public function login(): void
  {
    $this->requireCsrf();

    $login = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    // On accepte email ou pseudo comme identifiant de connexion
    $user = User::findByLogin($login);

    // Si l'utilisateur n'existe pas ou si le mot de passe ne correspond pas, on refuse
    if (!$user || !password_verify($password, $user['password_hash'])) {
      $this->renderAuthError('auth/login', 'Identifiants invalides.');
      return;
    }

    $this->loginUser((int)$user['id']);
    $this->redirect(self::ACCOUNT_PATH);
  }

  // Déconnecte proprement : vide la session, la détruit et renvoie à l'accueil.
  public function logout(): void
  {
    $this->requireCsrf();
    $_SESSION = [];
    session_destroy();
    $this->redirect('/');
  }

  // Contrôle rapide des champs obligatoires du formulaire d'inscription.
  // Retourne vrai dès qu'un champ manque ou que les mots de passe diffèrent.
  private function hasInvalidRegistrationInput(string $username, string $email, string $password, string $confirm): bool
  {
    return $username === '' || $email === '' || $password === '' || $password !== $confirm;
  }

  // Réaffiche le formulaire avec le message d'erreur en clair.
  private function renderAuthError(string $view, string $message): void
  {
    $this->render($view, ['error' => $message]);
  }

  // Ouvre la session pour l'utilisateur qui vient de s'inscrire ou se connecter.
  // On régénère l'id de session pour éviter la fixation de session.
  private function loginUser(int $userId): void
  {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;

    // On charge les données admin depuis la base pour les mettre en session
    $adminSession = User::adminSessionData($userId);
    $_SESSION['is_admin'] = $adminSession['is_admin'];

    if ($adminSession['user_role'] !== null) {
      $_SESSION['user_role'] = $adminSession['user_role'];
      return;
    }

    // Si aucun rôle n'est défini en base, on supprime la clé de session
    unset($_SESSION['user_role']);
  }
}
