<?php
// Point d'entrée HTTP du site : initialise la session, charge le coeur du projet
// et délègue ensuite le traitement de la requête à l'application.
// Active le mode strict des types pour ce fichier : PHP vérifiera que les types des arguments
// passés aux fonctions correspondent exactement aux types déclarés (int, string, bool, etc.)
declare(strict_types=1);

// Démarre une nouvelle session PHP (ou reprend la session existante si elle existe déjà).
// Cela permet de stocker et d'accéder à des données persistantes entre les requêtes via $_SESSION.
session_start();

// Charge l'autoloader PSR-4 du projet. Si les dépendances Composer ne sont pas
// encore installées, un fallback local compatible PSR-4 prend le relais.
require_once __DIR__ . '/../app/bootstrap.php';

// Instancie la classe App dans l'espace de noms \App\Core.
// Le constructeur de cette classe initialise généralement les composants de l'application
// (routeur, base de données, configuration, etc.).
$app = new \App\Core\App();

// Lance l'exécution de l'application : traite la requête HTTP entrante,
// appelle le bon contrôleur et renvoie la réponse au navigateur.
$app->run();
