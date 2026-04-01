<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Url;

// Modèle utilisateur : inscription, connexion, profil,
// avatar et mise à jour des informations de compte.
class User extends Model
{
  public const DEFAULT_AVATAR = '/assets/img/figma/mask-group-2.png';
  private const PUBLIC_FIELDS = 'id, username, email, avatar, bio, created_at, deleted_at';

  private static ?string $passwordColumn = null;
  private static ?array $userColumns = null;

  // Certains environnements stockent le mot de passe dans `password`,
  // d'autres dans `password_hash`. Cette méthode détecte la bonne colonne
  // une seule fois et mémorise le résultat.
  private static function resolvePasswordColumn(): string
  {
    if (self::$passwordColumn !== null) {
      return self::$passwordColumn;
    }

    try {
      $stmt = self::db()->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
      $hasPasswordHash = (bool)$stmt->fetch();
      self::$passwordColumn = $hasPasswordHash ? 'password_hash' : 'password';
    } catch (\Throwable $e) {
      self::$passwordColumn = 'password';
    }

    return self::$passwordColumn;
  }

  private static function userColumns(): array
  {
    if (self::$userColumns !== null) {
      return self::$userColumns;
    }

    try {
      $stmt = self::db()->query('SHOW COLUMNS FROM users');
      self::$userColumns = array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN, 0));
    } catch (\Throwable $e) {
      self::$userColumns = [];
    }

    return self::$userColumns;
  }

  private static function hasColumn(string $column): bool
  {
    return in_array($column, self::userColumns(), true);
  }

  public static function supportsSoftDelete(): bool
  {
    return self::hasColumn('deleted_at');
  }

  public static function activeSqlCondition(string $alias = 'users'): string
  {
    return self::supportsSoftDelete() ? "{$alias}.deleted_at IS NULL" : '1=1';
  }

  private static function adminConfig(): array
  {
    $config = require __DIR__ . '/../../config/config.php';
    $app = $config['app'] ?? [];

    return [
      'ids' => array_values(array_unique(array_map('intval', (array)($app['admin_user_ids'] ?? [])))),
      'emails' => array_values(array_unique(array_map(
        static fn ($email): string => self::lowercase(trim((string)$email)),
        (array)($app['admin_emails'] ?? [])
      ))),
    ];
  }

  private static function lowercase(string $value): string
  {
    if (function_exists('mb_strtolower')) {
      return mb_strtolower($value);
    }

    return strtolower($value);
  }

  // Recherche un utilisateur par email uniquement,
  // utile pour vérifier l'unicité à l'inscription.
  public static function findByEmail(string $email): ?array
  {
    $passwordColumn = self::resolvePasswordColumn();
    $stmt = self::db()->prepare("
      SELECT " . self::PUBLIC_FIELDS . ", {$passwordColumn} AS password_hash
      FROM users
      WHERE email = :email AND " . self::activeSqlCondition('users') . "
      LIMIT 1
    ");
    $stmt->execute(['email' => $email]);
    $u = $stmt->fetch();
    return $u ?: null;
  }

  // Recherche par identifiant de connexion :
  // l'utilisateur peut se connecter avec son email ou son pseudo.
  public static function findByLogin(string $login): ?array
  {
    $passwordColumn = self::resolvePasswordColumn();
    $stmt = self::db()->prepare("
      SELECT " . self::PUBLIC_FIELDS . ", {$passwordColumn} AS password_hash
      FROM users
      WHERE (email = :login OR username = :login)
        AND " . self::activeSqlCondition('users') . "
      LIMIT 1
    ");
    $stmt->execute(['login' => $login]);
    $u = $stmt->fetch();
    return $u ?: null;
  }

  // Retourne les informations publiques d'un utilisateur par son id.
  public static function find(int $id): ?array
  {
    $stmt = self::db()->prepare("
      SELECT " . self::PUBLIC_FIELDS . "
      FROM users
      WHERE id = :id AND " . self::activeSqlCondition('users')
    );
    $stmt->execute(['id' => $id]);
    $u = $stmt->fetch();
    return $u ?: null;
  }

  public static function findWithDeleted(int $id): ?array
  {
    $stmt = self::db()->prepare("SELECT " . self::PUBLIC_FIELDS . " FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $u = $stmt->fetch();
    return $u ?: null;
  }

  public static function isDeleted(?array $user): bool
  {
    return !empty($user['deleted_at']);
  }

  public static function restoreDaysLeft(?array $user): ?int
  {
    if (!self::isDeleted($user)) {
      return null;
    }

    $deletedAt = strtotime((string)($user['deleted_at'] ?? ''));
    if ($deletedAt === false) {
      return null;
    }

    return max(0, (int)ceil(($deletedAt + (30 * 86400) - time()) / 86400));
  }

  public static function adminSessionData(int $id): array
  {
    $user = self::find($id);
    $config = self::adminConfig();
    $email = self::lowercase(trim((string)($user['email'] ?? '')));
    $isAdmin = in_array($id, $config['ids'], true) || in_array($email, $config['emails'], true);
    $role = null;

    if (self::hasColumn('is_admin') || self::hasColumn('role')) {
      $select = [];
      if (self::hasColumn('is_admin')) {
        $select[] = 'is_admin';
      }
      if (self::hasColumn('role')) {
        $select[] = 'role';
      }

      $stmt = self::db()->prepare('SELECT ' . implode(', ', $select) . ' FROM users WHERE id = :id LIMIT 1');
      $stmt->execute(['id' => $id]);
      $row = $stmt->fetch() ?: [];

      $isAdmin = $isAdmin || (bool)($row['is_admin'] ?? false);
      $role = isset($row['role']) ? (string)$row['role'] : null;
      $isAdmin = $isAdmin || $role === 'admin';
    }

    return [
      'is_admin' => $isAdmin,
      'user_role' => $role,
    ];
  }

  public static function isAdmin(int $id): bool
  {
    return self::adminSessionData($id)['is_admin'];
  }

  // Crée un nouveau compte membre avec un avatar par défaut.
  public static function create(string $username, string $email, string $passwordHash): int
  {
    $passwordColumn = self::resolvePasswordColumn();
    $stmt = self::db()->prepare("
      INSERT INTO users (username, email, avatar, bio, {$passwordColumn})
      VALUES (:username, :email, :avatar, :bio, :password_hash)
    ");
    $stmt->execute([
      'username' => $username,
      'email' => $email,
      'avatar' => self::DEFAULT_AVATAR,
      'bio' => '',
      'password_hash' => $passwordHash,
    ]);

    return (int) self::db()->lastInsertId();
  }

  // Met à jour le profil ; si un mot de passe est fourni,
  // on le sauvegarde en même temps que le pseudo et la bio.
  public static function updateProfile(
    int $id,
    string $username,
    string $bio,
    ?string $passwordHash = null,
    ?string $avatarPath = null
  ): void
  {
    $fields = [
      'username = :username',
      'bio = :bio',
    ];
    $params = [
      'id' => $id,
      'username' => $username,
      'bio' => $bio,
    ];

    if ($avatarPath !== null) {
      $fields[] = 'avatar = :avatar';
      $params['avatar'] = $avatarPath;
    }

    if ($passwordHash !== null) {
      $passwordColumn = self::resolvePasswordColumn();
      $fields[] = "{$passwordColumn} = :password_hash";
      $params['password_hash'] = $passwordHash;
    }

    $stmt = self::db()->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id');
    $stmt->execute($params);
  }

  // Masque un membre pendant 30 jours avant suppression définitive.
  public static function softDelete(int $id): void
  {
    if (!self::supportsSoftDelete()) {
      self::delete($id);
      return;
    }

    $stmt = self::db()->prepare('UPDATE users SET deleted_at = NOW() WHERE id = :id AND deleted_at IS NULL');
    $stmt->execute(['id' => $id]);
  }

  // Restaure un membre tant que la fenêtre de 30 jours n'est pas dépassée.
  public static function restore(int $id): bool
  {
    if (!self::supportsSoftDelete()) {
      return false;
    }

    $stmt = self::db()->prepare("
      UPDATE users
      SET deleted_at = NULL
      WHERE id = :id
        AND deleted_at IS NOT NULL
        AND deleted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute(['id' => $id]);

    return $stmt->rowCount() > 0;
  }

  // Purge définitivement les comptes supprimés depuis plus de 30 jours.
  public static function purgeExpiredDeleted(): void
  {
    if (!self::supportsSoftDelete()) {
      return;
    }

    $stmt = self::db()->prepare("
      DELETE FROM users
      WHERE deleted_at IS NOT NULL
        AND deleted_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
  }

  // Suppression définitive d'un membre par son identifiant.
  // Les clés étrangères gèrent ensuite le nettoyage des livres et messages liés.
  public static function delete(int $id): void
  {
    $stmt = self::db()->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute(['id' => $id]);
  }

  // Retourne un avatar fiable : avatar utilisateur si le fichier existe,
  // sinon image de secours.
  public static function avatarPath(?array $user, string $fallback = '/assets/img/figma/mask-group-3.png'): string
  {
    $avatar = trim((string)($user['avatar'] ?? ''));
    if ($avatar !== '') {
      $path = '/' . ltrim($avatar, '/');
      if (Url::publicFileExists($path)) {
        return $path;
      }
    }

    return $fallback;
  }
}
