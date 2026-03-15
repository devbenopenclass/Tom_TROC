<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Book;
use App\Models\User;

final class AdminController extends Controller
{
    public function books(): void
    {
        $this->requireAdmin();

        $bookModel = new Book();
        $books = $bookModel->allWithOwner();

        $this->view('admin/books', [
            'books' => $books,
            'bodyClass' => 'admin-page',
        ]);
    }

    public function updateBookStatus(): void
    {
        $this->requireAdmin();

        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF token invalide';
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $status = (string)($_POST['status'] ?? 'available');
        if ($id <= 0 || !in_array($status, ['available', 'unavailable'], true)) {
            Session::flash('error', 'Données invalides.');
            $this->redirect('/admin/books');
        }

        $bookModel = new Book();
        $bookModel->adminSetStatus($id, $status);

        Session::flash('success', 'Disponibilité du livre mise à jour.');
        $this->redirect('/admin/books');
    }

    public function deleteBook(): void
    {
        $this->requireAdmin();

        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF token invalide';
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            Session::flash('error', 'Livre invalide.');
            $this->redirect('/admin/books');
        }

        $bookModel = new Book();
        $bookModel->adminDelete($id);

        Session::flash('success', 'Livre supprimé.');
        $this->redirect('/admin/books');
    }

    public function members(): void
    {
        $this->requireAdmin();

        $userModel = new User();
        $members = $userModel->allMembers();

        $this->view('admin/members', [
            'members' => $members,
            'bodyClass' => 'admin-page',
        ]);
    }
}
