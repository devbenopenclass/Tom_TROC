<?php
namespace App\Managers;

use App\Core\Model;
use App\Entities\User;

final class UserManager extends Model
{
  public const DEFAULT_AVATAR = '/assets/img/figma/mask-group-2.png';
  private const PUBLIC_FIELDS = 'id, username, email, avatar, bio, created_at';
  private const ROLE_USER = 'user';
  private const ROLE_ADMIN = 'admin';

  private static ?string $passwordColumn = null;
  private static ?array $userColumns = null;

  public static function findByEmail(string $email): ?User
  {
    $stmt = self::db()->prepare("
      SELECT " . self::PUBLIC_FIELDS . ', ' . self::resolvePasswordColumn() . " AS password_hash
      FROM users
      WHERE email = :email
      LIMIT 1
    ");
    $stmt->execute(['email' => $email]);

    return self::hydrateOne($stmt->fetch() ?: null);
  }

  public static function findByUsername(string $username, ?int $excludeId = null): ?User
  {
    $sql = "
      SELECT " . self::PUBLIC_FIELDS . ', ' . self::resolvePasswordColumn() . " AS password_hash
      FROM users
      WHERE username = :username
    ";
    $params = ['username' => $username];

    if ($excludeId !== null) {
      $sql .= ' AND id <> :exclude_id';
      $params['exclude_id'] = $excludeId;
    }

    $sql .= ' LIMIT 1';

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);

    return self::hydrateOne($stmt->fetch() ?: null);
  }

  public static function findByLogin(string $login): ?User
  {
    $stmt = self::db()->prepare("
      SELECT " . self::PUBLIC_FIELDS . ', ' . self::resolvePasswordColumn() . " AS password_hash
      FROM users
      WHERE email = :login OR username = :login
      LIMIT 1
    ");
    $stmt->execute(['login' => $login]);

    return self::hydrateOne($stmt->fetch() ?: null);
  }

  public static function find(int $id): ?User
  {
    $stmt = self::db()->prepare('SELECT ' . self::PUBLIC_FIELDS . ' FROM users WHERE id = :id');
    $stmt->execute(['id' => $id]);

    return self::hydrateOne($stmt->fetch() ?: null);
  }

  public static function create(string $username, string $email, string $passwordHash): int
  {
    $stmt = self::db()->prepare("
      INSERT INTO users (username, email, avatar, bio, " . self::resolvePasswordColumn() . ")
      VALUES (:username, :email, :avatar, :bio, :password_hash)
    ");
    $stmt->execute([
      'username' => $username,
      'email' => $email,
      'avatar' => self::DEFAULT_AVATAR,
      'bio' => '',
      'password_hash' => $passwordHash,
    ]);

    return (int)self::db()->lastInsertId();
  }

  public static function updateProfile(
    int $id,
    string $username,
    string $bio,
    ?string $passwordHash = null,
    ?string $avatarPath = null
  ): void {
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
      $fields[] = self::resolvePasswordColumn() . ' = :password_hash';
      $params['password_hash'] = $passwordHash;
    }

    $stmt = self::db()->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id');
    $stmt->execute($params);
  }

  public static function delete(int $id): void
  {
    $stmt = self::db()->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute(['id' => $id]);
  }

  public static function adminMembers(string $query = ''): array
  {
    $sql = implode("\n", [
      'SELECT',
      '    u.id,',
      '    u.username,',
      '    u.email,',
      '    u.avatar,',
      '    u.bio,',
      '    u.created_at,',
      '    COUNT(b.id) AS books_count',
      'FROM users u',
      'LEFT JOIN books b ON b.user_id = u.id',
    ]);
    $params = [];

    if ($query !== '') {
      $sql .= implode("\n", [
        '',
        'WHERE CAST(u.id AS CHAR) LIKE :query',
        '   OR u.username LIKE :query',
        '   OR u.email LIKE :query',
      ]);
      $params['query'] = '%' . $query . '%';
    }

    $sql .= implode("\n", [
      '',
      'GROUP BY u.id, u.username, u.email, u.avatar, u.bio, u.created_at',
      'ORDER BY u.created_at DESC, u.id DESC',
    ]);

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);

    return self::hydrateMany($stmt->fetchAll());
  }

  public static function adminSessionData(int $id): array
  {
    $user = self::find($id);
    $config = self::adminConfig();
    $email = self::lowercase(trim($user?->email() ?? ''));
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
      $isAdmin = $isAdmin || $role === self::ROLE_ADMIN;
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

  public static function roleLabel(int $id): string
  {
    return self::isAdmin($id) ? self::ROLE_ADMIN : self::ROLE_USER;
  }

  public static function updateRole(int $id, string $role): void
  {
    $role = $role === self::ROLE_ADMIN ? self::ROLE_ADMIN : self::ROLE_USER;

    if (self::hasColumn('role') && self::hasColumn('is_admin')) {
      $stmt = self::db()->prepare('UPDATE users SET role = :role, is_admin = :is_admin WHERE id = :id');
      $stmt->execute([
        'id' => $id,
        'role' => $role,
        'is_admin' => $role === self::ROLE_ADMIN ? 1 : 0,
      ]);
      return;
    }

    if (self::hasColumn('role')) {
      $stmt = self::db()->prepare('UPDATE users SET role = :role WHERE id = :id');
      $stmt->execute([
        'id' => $id,
        'role' => $role,
      ]);
      return;
    }

    if (!self::hasColumn('role') && !self::hasColumn('is_admin')) {
      throw new \RuntimeException("La gestion des roles requiert une colonne 'role' ou 'is_admin' dans la table users.");
    }

    $stmt = self::db()->prepare('UPDATE users SET is_admin = :is_admin WHERE id = :id');
    $stmt->execute([
      'id' => $id,
      'is_admin' => $role === self::ROLE_ADMIN ? 1 : 0,
    ]);
  }

  private static function hydrateOne(?array $row): ?User
  {
    return $row !== null ? User::fromArray($row) : null;
  }

  private static function hydrateMany(array $rows): array
  {
    return array_map(static fn (array $row): User => User::fromArray($row), $rows);
  }

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
}
