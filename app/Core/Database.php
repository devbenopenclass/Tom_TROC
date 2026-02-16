<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = require CONFIG_PATH . '/config.php';
        $db = $config['db'];

        try {
            self::$pdo = new PDO(
                $db['dsn'],
                $db['user'],
                $db['pass'],
                $db['options'] ?? []
            );
            return self::$pdo;
        } catch (PDOException $e) {
            http_response_code(500);
            echo 'Erreur DB: ' . htmlspecialchars($e->getMessage());
            exit;
        }
    }
}
