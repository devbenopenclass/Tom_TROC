<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, string>> */
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, string $handler): void
    {
        $this->map('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->map('POST', $path, $handler);
    }

    public function map(string $method, string $path, string $handler): void
    {
        $method = strtoupper(trim($method));
        if (!isset($this->routes[$method])) {
            throw new \InvalidArgumentException('Méthode HTTP non supportée : ' . $method);
        }

        $this->routes[$method][$this->normalize($path)] = $handler;
    }

    /** @param array<string, array<string, string>> $routes */
    public function register(array $routes): void
    {
        foreach ($routes as $method => $methodRoutes) {
            if (!is_array($methodRoutes)) {
                continue;
            }

            foreach ($methodRoutes as $path => $handler) {
                if (!is_string($path) || !is_string($handler)) {
                    continue;
                }

                $this->map($method, $path, $handler);
            }
        }
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $path = (string)(parse_url($uri, PHP_URL_PATH) ?: '/');

        $baseUrl = defined('BASE_URL') ? (string)BASE_URL : '';
        $baseUrl = rtrim($baseUrl, '/');
        if ($baseUrl !== '' && str_starts_with($path, $baseUrl)) {
            $path = substr($path, strlen($baseUrl)) ?: '/';
        }

        $path = $this->normalize($path);
        $handler = $this->routes[$method][$path] ?? null;

        if (!$handler) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        [$controllerName, $action] = explode('@', $handler, 2);

        $controllerClass = 'App\\Controllers\\' . $controllerName;
        if (!class_exists($controllerClass)) {
            http_response_code(500);
            echo 'Controller introuvable : ' . htmlspecialchars($controllerClass);
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            http_response_code(500);
            echo 'Action introuvable : ' . htmlspecialchars($action);
            return;
        }

        $controller->$action();
    }

    private function normalize(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path === '/') {
            return '/';
        }

        $normalized = '/' . trim($path, '/');
        $normalized = preg_replace('#/+#', '/', $normalized) ?: '/';

        return rtrim($normalized, '/') ?: '/';
    }
}
