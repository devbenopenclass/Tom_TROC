<?php
declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function view(string $view, array $data = [], string $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $path): void
    {
        $target = $this->toUrl($path);
        header('Location: ' . $target);
        exit;
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Veuillez vous connecter.');
            $this->redirect('/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        if (!Auth::isAdmin()) {
            Session::flash('error', 'Accès administrateur requis.');
            $this->redirect('/account');
        }
    }

    private function toUrl(string $path): string
    {
        $base = defined('BASE_URL') ? (string)BASE_URL : '';
        $base = rtrim($base, '/');
        $path = '/' . ltrim($path, '/');

        return ($base === '' ? '' : $base) . $path;
    }
}
