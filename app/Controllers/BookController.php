<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
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
    $this->render('books/exchange', ['books' => $books, 'q' => $q]);
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

    $this->render('books/show', ['book' => $book]);
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

    if ($data['title'] === '' || $data['author'] === '') {
      $this->renderBookFormError('create', 'Titre et auteur sont obligatoires.');
      return;
    }

    $data['image'] = $this->uploadedBookImage() ?? $data['image'];
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
    $this->findOwnedBook($id);

    $data = $this->bookPayloadForUpdate();
    $data['image'] = $this->uploadedBookImage() ?? $data['image'];

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
  private function handleUpload(array $file): ?string
  {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return null;

    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) return null;

    $ext = $allowed[$mime];
    $name = bin2hex(random_bytes(16)) . '.' . $ext;
    $destDir = __DIR__ . '/../../public/assets/uploads';

    if (!is_dir($destDir)) mkdir($destDir, 0777, true);

    $dest = $destDir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) return null;

    return '/assets/uploads/' . $name;
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

  private function uploadedBookImage(): ?string
  {
    if (empty($_FILES['image']['name'])) {
      return null;
    }

    return $this->handleUpload($_FILES['image']);
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

  private function renderBookFormError(string $mode, string $message): void
  {
    $this->render('books/form', [
      'mode' => $mode,
      'error' => $message,
    ]);
  }
}
