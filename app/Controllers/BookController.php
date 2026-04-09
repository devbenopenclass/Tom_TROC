<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Url;
use App\Models\Book;

// Contrôleur des livres : liste publique, fiche détail,
// formulaire d'ajout/édition et suppression.
class BookController extends Controller
{
  private const ACCOUNT_PATH = '/account';
  private const AVAILABLE_STATUSES = ['available', 'unavailable', 'reserved'];

  // Affiche le catalogue public des livres avec le moteur de recherche.
  public function exchange(): void
  {
    $q = trim($_GET['q'] ?? '');
    $books = Book::exchangeList($q !== '' ? $q : null);
    $this->render('books/exchange', [
      'books' => $this->buildExchangeCards($books),
      'q' => $q,
    ]);
  }

  // Affiche une seule fiche livre à partir de son id dans l'URL.
  public function show(): void
  {
    $id = (int)($_GET['id'] ?? 0);
    $book = $id ? Book::find($id) : null;

    if (!$book) {
      http_response_code(404);
      echo "Livre introuvable";
      return;
    }

    $this->render('books/show', ['bookView' => $this->buildBookShowView($book)]);
  }

  // Ouvre le formulaire d'ajout d'un livre pour le membre connecté.
  public function createForm(): void
  {
    $this->requireBookLogin();
    $this->render('books/form', ['mode' => 'create']);
  }

  // Valide les champs du formulaire puis crée le livre en base.
  public function create(): void
  {
    $this->requireBookLogin();
    $this->requireCsrf();

    $data = $this->bookPayloadForCreate();
    $error = $this->validateBookPayload($data);
    if ($error !== null) {
      $this->renderBookFormError('create', $error, $data);
      return;
    }

    $imageUpload = $this->uploadedBookImage();
    if ($imageUpload['error'] !== null) {
      $this->renderBookFormError('create', $imageUpload['error'], $data);
      return;
    }

    $data['image'] = $imageUpload['path'] ?? $data['image'];
    Book::create($data);
    $this->redirect(self::ACCOUNT_PATH);
  }

  // Charge le formulaire d'édition d'un livre existant.
  // On bloque l'accès si le livre n'appartient pas au membre.
  public function editForm(): void
  {
    $this->requireBookLogin();
    $book = $this->findOwnedBook((int)($_GET['id'] ?? 0));
    $this->render('books/form', ['mode' => 'edit', 'book' => $book]);
  }

  // Enregistre les modifications d'un livre existant.
  public function update(): void
  {
    $this->requireBookLogin();
    $this->requireCsrf();

    $id = (int)($_POST['id'] ?? 0);
    $book = $this->findOwnedBook($id);

    $data = $this->bookPayloadForUpdate();
    $error = $this->validateBookPayload($data);
    if ($error !== null) {
      $this->renderBookFormError('edit', $error, array_merge($book, $data, ['id' => $id]));
      return;
    }

    $imageUpload = $this->uploadedBookImage();
    if ($imageUpload['error'] !== null) {
      $this->renderBookFormError('edit', $imageUpload['error'], array_merge($book, $data, ['id' => $id]));
      return;
    }

    $data['image'] = $imageUpload['path'] ?? $data['image'];

    Book::update($id, $this->currentUserId(), $data);
    $this->redirect(self::ACCOUNT_PATH);
  }

  // Supprime un livre de la bibliothèque du membre connecté.
  public function delete(): void
  {
    $this->requireBookLogin();
    $this->requireCsrf();

    $id = (int)($_POST['id'] ?? 0);
    Book::delete($id, $this->currentUserId());
    $this->redirect(self::ACCOUNT_PATH);
  }

  // Gère l'upload d'une couverture utilisateur dans public/assets/uploads.
  private function handleUpload(array $file): array
  {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      return ['path' => null, 'error' => "L'image n'a pas pu être envoyée."];
    }

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $tmpName = (string)($file['tmp_name'] ?? '');
    $mime = $tmpName !== '' ? mime_content_type($tmpName) : false;
    if (!is_string($mime) || !isset($allowed[$mime])) {
      return ['path' => null, 'error' => "Le format de l'image doit être JPG, PNG ou WebP."];
    }

    $ext = $allowed[$mime];
    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    $destDir = __DIR__ . '/../../public/assets/uploads';

    if (!is_dir($destDir) && !mkdir($destDir, 0777, true) && !is_dir($destDir)) {
      return ['path' => null, 'error' => "Impossible d'enregistrer l'image pour le moment."];
    }

    $dest = $destDir . '/' . $name;
    if (!move_uploaded_file($tmpName, $dest)) {
      return ['path' => null, 'error' => "Impossible d'enregistrer l'image pour le moment."];
    }

    return ['path' => '/assets/uploads/' . $name, 'error' => null];
  }

  private function requireBookLogin(): void
  {
    Auth::requireLogin();
  }

  private function currentUserId(): int
  {
    return (int) Auth::id();
  }

  private function bookPayloadForCreate(): array
  {
    return [
      'user_id' => $this->currentUserId(),
      'title' => trim($_POST['title'] ?? ''),
      'author' => trim($_POST['author'] ?? ''),
      'image' => null,
      'description' => trim($_POST['description'] ?? ''),
      'status' => $this->normalizeStatus((string)($_POST['status'] ?? 'available')),
    ];
  }

  private function bookPayloadForUpdate(): array
  {
    return [
      'title' => trim($_POST['title'] ?? ''),
      'author' => trim($_POST['author'] ?? ''),
      'image' => $_POST['existing_image'] ?? null,
      'description' => trim($_POST['description'] ?? ''),
      'status' => $this->normalizeStatus((string)($_POST['status'] ?? 'available')),
    ];
  }

  private function uploadedBookImage(): array
  {
    if (empty($_FILES['image']['name'])) {
      return ['path' => null, 'error' => null];
    }

    return $this->handleUpload($_FILES['image']);
  }

  private function validateBookPayload(array $data): ?string
  {
    if ($data['title'] === '' || $data['author'] === '') {
      return 'Titre et auteur sont obligatoires.';
    }

    return null;
  }

  private function normalizeStatus(string $status): string
  {
    return in_array($status, self::AVAILABLE_STATUSES, true) ? $status : 'available';
  }

  private function findOwnedBook(int $id): array
  {
    $book = $id > 0 ? Book::find($id) : null;
    if ($book && (int)($book['user_id'] ?? 0) === $this->currentUserId()) {
      return $book;
    }

    http_response_code(403);
    echo 'Accès interdit';
    exit;
  }

  private function renderBookFormError(string $mode, string $message, array $book = []): void
  {
    $this->render('books/form', [
      'mode' => $mode,
      'error' => $message,
      'book' => $book,
    ]);
  }

  private function buildExchangeCards(array $books): array
  {
    $cards = [];

    foreach ($books as $book) {
      $isAvailable = ($book['status'] ?? '') === 'available';
      $cards[] = [
        'id' => (int)($book['id'] ?? 0),
        'title' => (string)($book['title'] ?? ''),
        'author' => (string)($book['author'] ?? ''),
        'owner' => (string)($book['username'] ?? ''),
        'image' => Url::asset(Book::imagePath($book)),
        'status_class' => $isAvailable ? 'book-status--ok' : 'book-status--off',
        'status_label' => $isAvailable ? 'disponible' : 'non dispo.',
      ];
    }

    return $cards;
  }

  private function buildBookShowView(array $book): array
  {
    $description = Book::detailDescription($book);
    $paragraphs = preg_split("/\n\s*\n/", $description) ?: [$description];

    return [
      'id' => (int)($book['id'] ?? 0),
      'user_id' => (int)($book['user_id'] ?? 0),
      'title' => trim((string)($book['title'] ?? 'Livre')),
      'author' => trim((string)($book['author'] ?? 'Auteur inconnu')),
      'owner' => trim((string)($book['username'] ?? 'membre de la communauté')),
      'image' => Url::asset(Book::detailImagePath($book, '/assets/img/figma/mask-group-1.png')),
      'owner_avatar' => Url::asset(\App\Models\User::avatarPath($book)),
      'paragraphs' => array_map(static fn (string $paragraph): string => trim($paragraph), $paragraphs),
      'can_message_owner' => Auth::check(),
    ];
  }
}
