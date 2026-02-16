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
        $config = require CONFIG_PATH . '/config.php';
        $base = rtrim((string)($config['app']['base_url'] ?? (defined('BASE_URL') ? BASE_URL : '')), '/');
        header('Location: ' . $base . $path);
        exit;
    }

    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            Session::flash('error', 'Veuillez vous connecter.');
            $this->redirect('/login');
        }
    }
}
