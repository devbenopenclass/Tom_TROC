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
  private const AVATAR_UPLOAD_DIR = '/assets/uploads';

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

    $avatarUpload = $this->uploadedAvatarPath();
    if ($avatarUpload['error'] !== null) {
      $this->renderAccountError($avatarUpload['error'], $formData);
      return;
    }

    $passwordHash = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : null;

    User::updateProfile(
      $this->currentUserId(),
      $username,
      $bio,
      $passwordHash,
      $avatarUpload['path']
    );
    $this->renderAccountPage([
      'success' => $this->profileSuccessMessage($passwordHash !== null, $avatarUpload['path'] !== null),
    ]);
  }

  public function deleteAccount(): void
  {
    $this->requireAccountLogin();
    $this->requireCsrf();

    User::delete($this->currentUserId());

    $_SESSION = [];
    session_destroy();

    $this->redirect('/');
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

  private function uploadedAvatarPath(): array
  {
    if (empty($_FILES['avatar']['name'])) {
      return ['path' => null, 'error' => null];
    }

    $file = $_FILES['avatar'];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      return ['path' => null, 'error' => "L'avatar n'a pas pu être envoyé."];
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tmpName = (string)($file['tmp_name'] ?? '');
    $mime = $tmpName !== '' ? mime_content_type($tmpName) : false;
    if (!is_string($mime) || !isset($allowed[$mime])) {
      return ['path' => null, 'error' => 'Le format de l’avatar doit être JPG, PNG ou WebP.'];
    }

    $destinationDir = __DIR__ . '/../../public' . self::AVATAR_UPLOAD_DIR;
    if (!is_dir($destinationDir) && !mkdir($destinationDir, 0777, true) && !is_dir($destinationDir)) {
      return ['path' => null, 'error' => "Impossible d'enregistrer l'avatar pour le moment."];
    }

    $fileName = 'avatar-' . $this->currentUserId() . '-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $destination = $destinationDir . '/' . $fileName;
    if (!move_uploaded_file($tmpName, $destination)) {
      return ['path' => null, 'error' => "Impossible d'enregistrer l'avatar pour le moment."];
    }

    return ['path' => self::AVATAR_UPLOAD_DIR . '/' . $fileName, 'error' => null];
  }

  private function profileSuccessMessage(bool $passwordChanged, bool $avatarChanged): string
  {
    if ($passwordChanged && $avatarChanged) {
      return 'Compte mis à jour. Le mot de passe et l’avatar ont bien été modifiés.';
    }

    if ($passwordChanged) {
      return 'Compte mis à jour. Le mot de passe a bien été modifié.';
    }

    if ($avatarChanged) {
      return 'Compte mis à jour. L’avatar a bien été modifié.';
    }

    return 'Compte mis à jour.';
  }
}
