<?php
namespace App\Core;

use PDO;

// Classe de base pour tous les modèles : ouvre la connexion PDO une seule fois
// et la garde en mémoire pour éviter de se reconnecter à chaque requête.
class Model
{
  // Connexion PDO partagée par tous les modèles (singleton léger)
  protected static ?PDO $pdo = null;

  // Accès public à la connexion, utile pour les cas ponctuels hors modèle.
  public static function connection(): PDO
  {
    return self::db();
  }

  // Retourne la connexion PDO, en l'ouvrant si ce n'est pas encore fait.
  // On charge d'abord config/database.local.php s'il existe (pour le dev local),
  // sinon on tombe sur config/database.php.
  protected static function db(): PDO
  {
    // Connexion déjà ouverte, on la réutilise directement
    if (self::$pdo) return self::$pdo;

    $localConfig = __DIR__ . '/../../config/database.local.php';
    $defaultConfig = __DIR__ . '/../../config/database.php';
    $dbConf = file_exists($localConfig) ? require $localConfig : require $defaultConfig;
    $db = $dbConf['db'];

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'], $db['name'], $db['charset']);

    // On active les exceptions PDO pour ne pas rater les erreurs SQL silencieusement
    self::$pdo = new PDO($dsn, $db['user'], $db['pass'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return self::$pdo;
  }
}
