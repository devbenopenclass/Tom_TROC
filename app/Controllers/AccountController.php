<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Core\Csrf;
use App\Models\Book;
use App\Models\User;

final class AccountController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $userModel = new User();
        $bookModel = new Book();

        $userId = (int)Auth::id();
        $user = $userModel->findById($userId);
        $books = $bookModel->mine($userId);

        $memberSince = null;
        if (!empty($user['created_at'])) {
            $createdAt = new \DateTimeImmutable((string)$user['created_at']);
            $now = new \DateTimeImmutable();
            $memberSince = max(0, (int)$createdAt->diff($now)->y);
        }

        $this->view('account/index', [
            'user' => $user,
            'books' => $books,
            'memberSinceYears' => $memberSince,
            'bodyClass' => 'account-admin-page',
        ]);
    }

    public function update(): void
    {
        $this->requireAuth();

        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF token invalide';
            return;
        }

        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = trim((string)($_POST['password'] ?? ''));

        if ($username === '' || $email === '') {
            Session::flash('error', 'Le pseudo et l’email sont requis.');
            $this->redirect('/account');
        }

        $userModel = new User();
        $userId = (int)Auth::id();

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Adresse email invalide.');
            $this->redirect('/account');
        }

        $existing = $userModel->findByEmail($email);
        if ($existing && (int)$existing['id'] !== $userId) {
            Session::flash('error', 'Cette adresse email est déjà utilisée.');
            $this->redirect('/account');
        }

        $passwordHash = null;
        if ($password !== '') {
            if (strlen($password) < 6) {
                Session::flash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
                $this->redirect('/account');
            }
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }

        $userModel->updateAccount($userId, $username, $email, $passwordHash);

        Session::flash('success', 'Informations du compte mises à jour.');
        $this->redirect('/account');
    }
}
