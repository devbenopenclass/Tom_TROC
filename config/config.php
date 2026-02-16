<?php
declare(strict_types=1);

$defaultBaseUrl = defined('BASE_URL') ? BASE_URL : '';

return [
    'db' => [
        // Les identifiants doivent être fournis via variables d'environnement en production.
        'dsn' => getenv('DB_DSN') ?: 'mysql:host=localhost;dbname=tomtroc;charset=utf8mb4',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ],
    ],
    'app' => [
        // Fallback automatique sur le sous-dossier courant (ex: /tomtroc_mvc/public).
        'base_url' => getenv('APP_BASE_URL') ?: $defaultBaseUrl,
        'name' => 'Tom Troc',
    ],
];
