<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Url;
use App\Models\User;
use App\Models\Book;

// Gère tout ce qui touche à l'espace "Mon compte" :
// affichage du profil, modification des infos et suppression du compte.
class AccountController extends Controller
{
  // Longueur minimum exigée pour le mot de passe
  private const MIN_PASSWORD_LENGTH = 6;
  // Dossier où on enregistre les avatars uploadés
  private const AVATAR_UPLOAD_DIR = '/assets/uploads';

  // Charge la page Mon compte en injectant les infos du membre et ses livres.
  // Le tableau $extra permet de passer un message d'erreur ou de succès.
  private function renderAccountPage(array $extra = []): void
  {
    $me = User::find($this->currentUserId());
    $books = Book::byUser($this->currentUserId());
    $this->render('account/index', array_merge([
      'accountView' => $this->buildAccountView($me, $books, $extra['form'] ?? []),
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

  // Gère l'upload de l'avatar : vérifie le format, crée le dossier si besoin,
  // déplace le fichier et retourne son chemin public (ou une erreur).
  private function uploadedAvatarPath(): array
  {
    // Pas de fichier envoyé, c'est normal : on continue sans erreur
    if (empty($_FILES['avatar']['name'])) {
      return ['path' => null, 'error' => null];
    }

    $file = $_FILES['avatar'];

    // Le transfert côté PHP a échoué avant même qu'on puisse traiter le fichier
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      return ['path' => null, 'error' => "L'avatar n'a pas pu être envoyé."];
    }

    // On n'accepte que les formats image courants : JPG, PNG ou WebP
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tmpName = (string)($file['tmp_name'] ?? '');
    $mime = $tmpName !== '' ? mime_content_type($tmpName) : false;
    if (!is_string($mime) || !isset($allowed[$mime])) {
      return ['path' => null, 'error' => 'Le format de l'avatar doit être JPG, PNG ou WebP.'];
    }

    // On crée le dossier de destination si ce n'est pas déjà fait
    $destinationDir = __DIR__ . '/../../public' . self::AVATAR_UPLOAD_DIR;
    if (!is_dir($destinationDir) && !mkdir($destinationDir, 0777, true) && !is_dir($destinationDir)) {
      return ['path' => null, 'error' => "Impossible d'enregistrer l'avatar pour le moment."];
    }

    // Le nom du fichier inclut l'id du membre et des octets aléatoires pour éviter les collisions
    $fileName = 'avatar-' . $this->currentUserId() . '-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
    $destination = $destinationDir . '/' . $fileName;
    if (!move_uploaded_file($tmpName, $destination)) {
      return ['path' => null, 'error' => "Impossible d'enregistrer l'avatar pour le moment."];
    }

    return ['path' => self::AVATAR_UPLOAD_DIR . '/' . $fileName, 'error' => null];
  }

  // Compose un message de succès précis selon ce qui a réellement changé.
  private function profileSuccessMessage(bool $passwordChanged, bool $avatarChanged): string
  {
    if ($passwordChanged && $avatarChanged) {
      return 'Compte mis à jour. Le mot de passe et l'avatar ont bien été modifiés.';
    }

    if ($passwordChanged) {
      return 'Compte mis à jour. Le mot de passe a bien été modifié.';
    }

    if ($avatarChanged) {
      return 'Compte mis à jour. L'avatar a bien été modifié.';
    }

    return 'Compte mis à jour.';
  }

  // Prépare toutes les données que la vue du compte va afficher :
  // avatar, pseudo, email, ancienneté et liste des livres du membre.
  private function buildAccountView(?array $me, array $books, array $form = []): array
  {
    // Ancienneté par défaut à 1 an si la date d'inscription est absente
    $memberSince = '1 an';
    if (!empty($me['created_at'])) {
      $years = max(1, (int)date('Y') - (int)date('Y', strtotime((string)$me['created_at'])));
      $memberSince = $years . ' an' . ($years > 1 ? 's' : '');
    }

    $bookRows = [];
    foreach ($books as $book) {
      $description = trim((string)($book['description'] ?? ''));

      // Si la description est vide, on met un texte par défaut plutôt que de laisser un blanc
      if ($description === '') {
        $description = 'Aucune description.';
      }

      // On tronque les descriptions trop longues pour que les cartes restent lisibles
      if (mb_strlen($description) > 110) {
        $description = mb_substr($description, 0, 110) . '...';
      }

      $isAvailable = ($book['status'] ?? '') === 'available';
      $bookRows[] = [
        'id' => (int)($book['id'] ?? 0),
        'title' => (string)($book['title'] ?? ''),
        'author' => (string)($book['author'] ?? ''),
        'cover' => Url::asset(Book::imagePath($book)),
        'description' => $description,
        // La classe CSS change selon la disponibilité du livre
        'status_class' => $isAvailable ? 'status-pill--ok' : 'status-pill--off',
        'status_label' => $isAvailable ? 'disponible' : 'indisponible',
      ];
    }

    return [
      'avatar' => Url::asset(User::avatarPath($me, '/assets/img/figma/mask-group-2.png')),
      'username' => (string)($me['username'] ?? ''),
      // Si le formulaire a été soumis avec une erreur, on remet la valeur saisie
      'username_value' => (string)($form['username'] ?? ($me['username'] ?? '')),
      'email' => (string)($me['email'] ?? ''),
      'bio' => (string)($me['bio'] ?? ''),
      'member_since' => $memberSince,
      'books_count' => count($books),
      'book_rows' => $bookRows,
    ];
  }
}
