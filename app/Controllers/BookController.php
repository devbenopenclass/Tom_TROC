<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Core\Csrf;
use App\Models\Book;

final class BookController extends Controller
{
    public function index(): void
    {
        $q = $_GET['q'] ?? null;
        $bookModel = new Book();
        $books = $bookModel->searchAvailable(is_string($q) ? $q : null);

        $this->view('books/index', [
            'books' => $books,
            'q' => is_string($q) ? $q : '',
        ]);
    }

    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/books');
        }

        $bookModel = new Book();
        $book = $bookModel->findById($id);
        if (!$book) {
            http_response_code(404);
            echo 'Livre introuvable';
            return;
        }

        $this->view('books/show', [
            'book' => $book,
        ]);
    }

    public function myBooks(): void
    {
        $this->requireAuth();

        $bookModel = new Book();
        $books = $bookModel->mine((int)Auth::id());

        $this->view('books/my', [
            'books' => $books,
        ]);
    }

    public function createForm(): void
    {
        $this->requireAuth();
        $this->view('books/create');
    }

    public function store(): void
    {
        $this->requireAuth();

        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF token invalide';
            return;
        }

        $title = trim((string)($_POST['title'] ?? ''));
        $author = trim((string)($_POST['author'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $status = (string)($_POST['status'] ?? 'available');

        if ($title === '' || $author === '') {
            Session::flash('error', 'Titre et auteur sont requis.');
            $this->redirect('/library/create');
        }

        // Upload image (optionnel)
        $imageName = null;
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = BASE_PATH . '/public/assets/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $tmp = $_FILES['image']['tmp_name'] ?? '';
            $orig = basename((string)$_FILES['image']['name']);
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if (in_array($ext, $allowed, true)) {
                $imageName = uniqid('book_', true) . '.' . $ext;
                move_uploaded_file($tmp, $uploadDir . '/' . $imageName);
            }
        }

        $bookModel = new Book();
        $bookModel->create((int)Auth::id(), $title, $author, $imageName, $description !== '' ? $description : null, $status);

        Session::flash('success', 'Livre ajouté.');
        $this->redirect('/library');
    }

    public function editForm(): void
    {
        $this->requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->redirect('/library');

        $bookModel = new Book();
        $book = $bookModel->findById($id);
        if (!$book || (int)$book['owner_id'] !== (int)Auth::id()) {
            Session::flash('error', 'Accès refusé.');
            $this->redirect('/library');
        }

        $this->view('books/edit', ['book' => $book]);
    }

    public function update(): void
    {
        $this->requireAuth();
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) $this->redirect('/library');

        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF token invalide';
            return;
        }

        $title = trim((string)($_POST['title'] ?? ''));
        $author = trim((string)($_POST['author'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $status = (string)($_POST['status'] ?? 'available');

        if ($title === '' || $author === '') {
            Session::flash('error', 'Titre et auteur sont requis.');
            $this->redirect('/library/edit?id=' . $id);
        }

        $bookModel = new Book();
        $existing = $bookModel->findById($id);
        if (!$existing || (int)$existing['owner_id'] !== (int)Auth::id()) {
            Session::flash('error', 'Accès refusé.');
            $this->redirect('/library');
        }

        $imageName = $existing['image'] ?? null;
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = BASE_PATH . '/public/assets/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $tmp = $_FILES['image']['tmp_name'] ?? '';
            $orig = basename((string)$_FILES['image']['name']);
            $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];
            if (in_array($ext, $allowed, true)) {
                $imageName = uniqid('book_', true) . '.' . $ext;
                move_uploaded_file($tmp, $uploadDir . '/' . $imageName);
            }
        }

        $bookModel->update($id, (int)Auth::id(), $title, $author, $imageName, $description !== '' ? $description : null, $status);

        Session::flash('success', 'Livre mis à jour.');
        $this->redirect('/library');
    }

    public function delete(): void
    {
        $this->requireAuth();
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF token invalide';
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) $this->redirect('/library');

        $bookModel = new Book();
        $bookModel->delete($id, (int)Auth::id());

        Session::flash('success', 'Livre supprimé.');
        $this->redirect('/library');
    }
}
