<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'main'): void
    {
        $viewFile = APP_PATH . '/Views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'Vue introuvable : ' . htmlspecialchars($view);
            return;
        }

        extract($data, EXTR_SKIP);

        $layoutFile = APP_PATH . '/Views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            http_response_code(500);
            echo 'Layout introuvable : ' . htmlspecialchars($layout);
            return;
        }

        ob_start();
        require $viewFile;
        $content = (string)ob_get_clean();

        require $layoutFile;
    }

    public static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
