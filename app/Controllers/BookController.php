<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Book;

class BookController extends Controller
{
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
      http_response_code(404);
      echo "Livre introuvable";
      return;
    }

    $this->render('books/show', ['book' => $book]);
  }

  public function createForm(): void
  {
    Auth::requireLogin();
    $this->render('books/form', ['mode' => 'create']);
  }

  public function create(): void
  {
    Auth::requireLogin();

    $data = [
      'user_id' => Auth::id(),
      'title' => trim($_POST['title'] ?? ''),
      'author' => trim($_POST['author'] ?? ''),
      'image' => null,
      'description' => trim($_POST['description'] ?? ''),
      'status' => $_POST['status'] ?? 'available',
    ];

    if ($data['title'] === '' || $data['author'] === '') {
      $this->render('books/form', ['mode' => 'create', 'error' => 'Titre et auteur sont obligatoires.']);
      return;
    }

    if (!empty($_FILES['image']['name'])) {
      $data['image'] = $this->handleUpload($_FILES['image']);
    }

    Book::create($data);
    $this->redirect('/account');
  }

  public function editForm(): void
  {
    Auth::requireLogin();

    $id = (int)($_GET['id'] ?? 0);
    $book = $id ? Book::find($id) : null;

    if (!$book || (int)$book['user_id'] !== Auth::id()) {
      http_response_code(403);
      echo "Accès interdit";
      return;
    }

    $this->render('books/form', ['mode' => 'edit', 'book' => $book]);
  }

  public function update(): void
  {
    Auth::requireLogin();

    $id = (int)($_POST['id'] ?? 0);

    $data = [
      'title' => trim($_POST['title'] ?? ''),
      'author' => trim($_POST['author'] ?? ''),
      'image' => $_POST['existing_image'] ?? null,
      'description' => trim($_POST['description'] ?? ''),
      'status' => $_POST['status'] ?? 'available',
    ];

    if (!empty($_FILES['image']['name'])) {
      $data['image'] = $this->handleUpload($_FILES['image']);
    }

    Book::update($id, Auth::id(), $data);
    $this->redirect('/account');
  }

  public function delete(): void
  {
    Auth::requireLogin();

    $id = (int)($_POST['id'] ?? 0);
    Book::delete($id, Auth::id());
    $this->redirect('/account');
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
}
