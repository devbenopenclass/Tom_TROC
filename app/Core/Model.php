<?php
namespace App\Core;

use PDO;

class Model
{
  protected static ?PDO $pdo = null;

  protected static function db(): PDO
  {
    if (self::$pdo) return self::$pdo;

    $dbConf = require __DIR__ . '/../../config/database.php';
    $db = $dbConf['db'];

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $db['host'], $db['name'], $db['charset']);

    self::$pdo = new PDO($dsn, $db['user'], $db['pass'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return self::$pdo;
  }
}
