<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Book;
use App\Models\User;
use App\Presenters\AccountPresenter;
use App\Services\ImageUploadService;

// Gère tout ce qui touche à l'espace "Mon compte" :
// affichage du profil, modification des infos et suppression du compte.
class AccountController extends Controller
{
  // Longueur minimum exigée pour le mot de passe
  private const MIN_PASSWORD_LENGTH = 6;

  private AccountPresenter $accountPresenter;
  private ImageUploadService $imageUploadService;

  public function __construct()
  {
    parent::__construct();
    $this->accountPresenter = new AccountPresenter();
    $this->imageUploadService = new ImageUploadService();
  }

  // Charge la page Mon compte en injectant les infos du membre et ses livres.
  // Le tableau $extra permet de passer un message d'erreur ou de succès.
  private function renderAccountPage(array $extra = []): void
  {
    $me = User::find($this->currentUserId());
    $books = Book::byUser($this->currentUserId());
    $this->render('account/index', array_merge([
      'accountView' => $this->accountPresenter->buildView($me, $books, $extra['form'] ?? []),
    ], $extra));
  }

  // Page principale du compte : on vérifie d'abord que l'utilisateur est connecté.
  public function index(): void
  {
    $this->requireAccountLogin();
    $this->renderAccountPage();
  }

  // Ouvre le formulaire d'édition du profil pré-rempli avec les infos actuelles.
  public function editProfileForm(): void
  {
    $this->requireAccountLogin();
    $me = User::find($this->currentUserId());
    $this->render('account/profile_edit', ['me' => $me]);
  }

  // Traite la soumission du formulaire de modification du profil.
  // On valide les champs un par un avant de toucher à la base.
  public function updateProfile(): void
  {
    $this->requireAccountLogin();
    $this->requireCsrf();

    $username = trim($_POST['username'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $passwordConfirm = trim($_POST['password_confirm'] ?? '');

    // Garde les valeurs saisies pour re-remplir le formulaire en cas d'erreur
    $formData = [
      'username' => $username,
      'bio' => $bio,
    ];

    // Le pseudo est obligatoire, on refuse si le champ est vide
    if ($username === '') {
      $this->renderAccountError('Le pseudo est obligatoire.', $formData);
      return;
    }

    // Le pseudo doit être unique, on exclut l'utilisateur courant du contrôle
    if (User::findByUsername($username, $this->currentUserId())) {
      $this->renderAccountError('Ce pseudo est déjà utilisé.', $formData);
      return;
    }

    // Si un nouveau mot de passe est fourni, on contrôle sa longueur minimale
    if ($password !== '' && mb_strlen($password) < self::MIN_PASSWORD_LENGTH) {
      $this->renderAccountError('Le mot de passe doit contenir au moins 6 caractères.', $formData);
      return;
    }

    // Les deux saisies de mot de passe doivent correspondre exactement
    if ($password !== $passwordConfirm) {
      $this->renderAccountError('La confirmation du mot de passe ne correspond pas.', $formData);
      return;
    }

    // On tente d'uploader l'avatar s'il y en a un, sinon on reçoit null sans erreur
    $avatarUpload = $this->uploadedAvatarPath();
    if ($avatarUpload['error'] !== null) {
      $this->renderAccountError($avatarUpload['error'], $formData);
      return;
    }

    // On ne hache le mot de passe que si l'utilisateur en a saisi un nouveau
    $passwordHash = $password !== '' ? password_hash($password, PASSWORD_BCRYPT) : null;

    User::updateProfile(
      $this->currentUserId(),
      $username,
      $bio,
      $passwordHash,
      $avatarUpload['path']
    );

    // On réaffiche le compte avec un message de succès adapté à ce qui a changé
    $this->renderAccountPage([
      'success' => $this->profileSuccessMessage($passwordHash !== null, $avatarUpload['path'] !== null),
    ]);
  }

  // Supprime définitivement le compte, puis détruit la session et renvoie à l'accueil.
  public function deleteAccount(): void
  {
    $this->requireAccountLogin();
    $this->requireCsrf();

    User::delete($this->currentUserId());

    // On efface tout en mémoire avant de détruire la session
    $_SESSION = [];
    session_destroy();

    $this->redirect('/');
  }

  // Raccourci interne : redirige vers la connexion si la session est vide.
  private function requireAccountLogin(): void
  {
    Auth::requireLogin();
  }

  // Retourne l'identifiant de l'utilisateur connecté en entier.
  private function currentUserId(): int
  {
    return (int) Auth::id();
  }

  // Réaffiche la page compte en injectant le message d'erreur et les données du formulaire.
  private function renderAccountError(string $message, array $formData): void
  {
    $this->renderAccountPage([
      'error' => $message,
      'form' => $formData,
    ]);
  }

  private function uploadedAvatarPath(): array
  {
    return $this->imageUploadService->uploadOptional(
      $_FILES['avatar'] ?? [],
      __DIR__ . '/../../public/assets/uploads',
      '/assets/uploads',
      'avatar-' . $this->currentUserId(),
      'avatar'
    );
  }

  // Compose un message de succès précis selon ce qui a réellement changé.
  private function profileSuccessMessage(bool $passwordChanged, bool $avatarChanged): string
  {
    if ($passwordChanged && $avatarChanged) {
      return "Compte mis à jour. Le mot de passe et l'avatar ont bien été modifiés.";
    }

    if ($passwordChanged) {
      return 'Compte mis à jour. Le mot de passe a bien été modifié.';
    }

    if ($avatarChanged) {
      return "Compte mis à jour. L'avatar a bien été modifié.";
    }

    return 'Compte mis à jour.';
  }
}
