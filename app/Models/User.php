<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
  private static ?string $passwordColumn = null;

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

  public static function findByEmail(string $email): ?array
  {
    $passwordColumn = self::resolvePasswordColumn();
    $stmt = self::db()->prepare("
      SELECT id, username, email, avatar, bio, created_at, {$passwordColumn} AS password_hash
      FROM users
      WHERE email = :email
      LIMIT 1
    ");
    $stmt->execute(['email' => $email]);
    $u = $stmt->fetch();
    return $u ?: null;
  }

  public static function find(int $id): ?array
  {
    $stmt = self::db()->prepare("SELECT id, username, email, avatar, bio, created_at FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $u = $stmt->fetch();
    return $u ?: null;
  }

  public static function create(string $username, string $email, string $passwordHash): int
  {
    $passwordColumn = self::resolvePasswordColumn();
    $stmt = self::db()->prepare("
      INSERT INTO users (username, email, {$passwordColumn})
      VALUES (:username, :email, :password_hash)
    ");
    $stmt->execute([
      'username' => $username,
      'email' => $email,
      'password_hash' => $passwordHash,
    ]);

    return (int) self::db()->lastInsertId();
  }

  public static function updateProfile(int $id, string $username, string $bio): void
  {
    $stmt = self::db()->prepare("UPDATE users SET username = :username, bio = :bio WHERE id = :id");
    $stmt->execute([
      'id' => $id,
      'username' => $username,
      'bio' => $bio,
    ]);
  }
}
