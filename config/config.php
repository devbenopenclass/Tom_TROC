<?php
// Configuration générale du site : nom de l'application et URL de base
// utilisée pour générer des liens et des assets corrects.
return [
  'app' => [
    'name' => 'TomTroc',
    // Project served from XAMPP htdocs as https://localhost/tomtroc
    'base_url' => '/tomtroc',
    // En local, l'administration est pilotée par configuration
    // si la table users ne possède pas encore de colonne dédiée.
    'admin_user_ids' => [4],
    'admin_emails' => [],
  ],
];
