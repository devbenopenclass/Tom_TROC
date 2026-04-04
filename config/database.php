<?php
// Paramètres de connexion à la base MySQL locale.
// Ce tableau est relu par le modèle de base pour créer la connexion PDO.
return [
  'db' => [
    'host' => getenv('TOMTROC_DB_HOST') ?: '',
    'name' => getenv('TOMTROC_DB_NAME') ?: '',
    'user' => getenv('TOMTROC_DB_USER') ?: '',
    'pass' => getenv('TOMTROC_DB_PASS') ?: '',
    'charset' => getenv('TOMTROC_DB_CHARSET') ?: 'utf8mb4',
  ],
];
