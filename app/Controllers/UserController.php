<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Book;

final class UserController extends Controller
{
    public function show(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/');
        }

        $userModel = new User();
        $user = $userModel->findById($id);

        if (!$user) {
            http_response_code(404);
            echo 'Profil introuvable';
            return;
        }

        $bookModel = new Book();
        $books = $bookModel->mine($id);

        $this->view('users/show', [
            'profile' => $user,
            'books' => $books,
        ]);
    }
}
