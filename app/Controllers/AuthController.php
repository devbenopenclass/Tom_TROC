<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Auth;
use App\Core\Csrf;
use App\Models\User;

final class AuthController extends Controller
{
    public function registerForm(): void
    {
        $this->view('auth/register');
    }

    public function register(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF token invalide';
            return;
        }

        $username = trim((string)($_POST['username'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $email === '' || $password === '') {
            Session::flash('error', 'Tous les champs sont requis.');
            $this->redirect('/register');
        }

        $userModel = new User();
        if ($userModel->findByEmail($email)) {
            Session::flash('error', 'Cet email est déjà utilisé.');
            $this->redirect('/register');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $id = $userModel->create($username, $email, $hash);

        Auth::login($id);
        Session::flash('success', 'Bienvenue sur TomTroc !');
        $this->redirect('/account');
    }

    public function loginForm(): void
    {
        $this->view('auth/login');
    }

    public function login(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF token invalide';
            return;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            Session::flash('error', 'Identifiants invalides.');
            $this->redirect('/login');
        }

        Auth::login((int)$user['id']);
        Session::flash('success', 'Connexion réussie.');
        $this->redirect('/account');
    }

    public function logout(): void
    {
        Auth::logout();
        Session::flash('success', 'Vous êtes déconnecté.');
        $this->redirect('/');
    }
}
