<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Book;
use App\Models\User;

class BookController extends Controller
{
  private const ACCOUNT_PATH = '/account';
  private const ADMIN_BOOKS_PATH = '/admin/books';
  private const AVAILABLE_STATUSES = ['available', 'unavailable', 'reserved'];

  public function exchange(): void
  {
    $q = trim($_GET['q'] ?? '');
    $books = Book::exchangeList($q !== '' ? $q : null);
    $this->render('books/exchange', ['books' => $books, 'q' => $q]);
  }

  public function show(): void
  {
    $id = (int)($_GET['id'] ?? 0);
    $book = $id ? Book::find($id) : null;

    if (!$book) {
      $this->renderStatusPage(404, 'errors/404', [
        'title' => 'Livre introuvable',
        'message' => "Le livre demandé n'existe pas ou n'est plus disponible.",
      ]);
      return;
    }

    $this->render('books/show', ['book' => $book]);
  }

  public function createForm(): void
  {
    $this->requireBookLogin();
    $this->render('books/form', ['mode' => 'create']);
  }

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

    $data['image'] = $this->uploadedBookImage() ?? $data['image'];
    Book::create($data);
    $this->redirect(self::ACCOUNT_PATH);
  }

  public function editForm(): void
  {
    $this->requireBookLogin();
    $book = $this->findEditableBook((int)($_GET['id'] ?? 0));
    $this->render('books/form', ['mode' => 'edit', 'book' => $book]);
  }

  public function update(): void
  {
    $this->requireBookLogin();
    $this->requireCsrf();

    $id = (int)($_POST['id'] ?? 0);
    $book = $this->findEditableBook($id);

    $data = $this->bookPayloadForUpdate();
    $error = $this->validateBookPayload($data);
    if ($error !== null) {
      $this->renderBookFormError('edit', $error, array_merge($book, $data, ['id' => $id]));
      return;
    }

    $data['image'] = $this->uploadedBookImage() ?? $data['image'];

    if ($this->isAdmin()) {
      Book::update($id, (int)($book['user_id'] ?? 0), $data);
      $this->redirect(self::ADMIN_BOOKS_PATH);
    }

    Book::update($id, $this->currentUserId(), $data);
    $this->redirect(self::ACCOUNT_PATH);
  }

  public function delete(): void
  {
    $this->requireBookLogin();
    $this->requireCsrf();

    $id = (int)($_POST['id'] ?? 0);
    Book::delete($id, $this->currentUserId());
    $this->redirect(self::ACCOUNT_PATH);
  }

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

  private function findEditableBook(int $id): array
  {
    if ($this->isAdmin()) {
      $book = $id > 0 ? Book::find($id) : null;
      if ($book) {
        return $book;
      }

      http_response_code(404);
      echo 'Livre introuvable';
      exit;
    }

    return $this->findOwnedBook($id);
  }

  private function isAdmin(): bool
  {
    $userId = Auth::id();
    return $userId !== null && User::isAdmin((int)$userId);
  }

  private function renderBookFormError(string $mode, string $message, array $book = []): void
  {
    $this->render('books/form', [
      'mode' => $mode,
      'error' => $message,
      'book' => $book,
    ]);
  }
}
