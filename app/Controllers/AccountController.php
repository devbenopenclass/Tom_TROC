<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Core\Csrf;
use App\Models\User;

final class AccountController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $userModel = new User();
        $user = $userModel->findById((int)Auth::id());

        $this->view('account/index', [
            'user' => $user,
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
        $bio = trim((string)($_POST['bio'] ?? ''));

        if ($username === '') {
            Session::flash('error', 'Le nom d’utilisateur est requis.');
            $this->redirect('/account');
        }

        $userModel = new User();
        $userModel->updateProfile((int)Auth::id(), $username, $bio !== '' ? $bio : null);

        Session::flash('success', 'Profil mis à jour.');
        $this->redirect('/account');
    }
}
