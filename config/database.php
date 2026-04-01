<?php
// Configuration base de données :
// privilégie un fichier local ignoré par Git puis tombe sur des variables d'environnement.
$localConfig = __DIR__ . '/database.local.php';
if (is_file($localConfig)) {
  return require $localConfig;
}

return [
  'db' => [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'name' => getenv('DB_NAME') ?: 'database_name',
    'user' => getenv('DB_USER') ?: 'database_user',
    'pass' => getenv('DB_PASS') ?: 'database_password',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
  ],
];
