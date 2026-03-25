<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Book;

// Contrôleur des livres : liste publique, fiche détail,
// formulaire d'ajout/édition et suppression.
class BookController extends Controller
{
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
    Auth::requireLogin();
    $this->render('books/form', ['mode' => 'create']);
  }

  // Valide les champs du formulaire puis crée le livre en base.
  public function create(): void
  {
    Auth::requireLogin();
    $this->requireCsrf();

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

  // Charge le formulaire d'édition d'un livre existant.
  // On bloque l'accès si le livre n'appartient pas au membre.
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

  // Enregistre les modifications d'un livre existant.
  public function update(): void
  {
    Auth::requireLogin();
    $this->requireCsrf();

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

  // Supprime un livre de la bibliothèque du membre connecté.
  public function delete(): void
  {
    Auth::requireLogin();
    $this->requireCsrf();

    $id = (int)($_POST['id'] ?? 0);
    Book::delete($id, Auth::id());
    $this->redirect('/account');
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
}
