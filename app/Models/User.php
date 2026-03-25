<?php
namespace App\Models;

use App\Core\Model;

// Modèle utilisateur : inscription, connexion, profil,
// avatar et mise à jour des informations de compte.
class User extends Model
{
  public const DEFAULT_AVATAR = '/assets/img/figma/mask-group-2.png';

  private static ?string $passwordColumn = null;

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

  // Recherche un utilisateur par email uniquement,
  // utile pour vérifier l'unicité à l'inscription.
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

  // Recherche par identifiant de connexion :
  // l'utilisateur peut se connecter avec son email ou son pseudo.
  public static function findByLogin(string $login): ?array
  {
    $passwordColumn = self::resolvePasswordColumn();
    $stmt = self::db()->prepare("
      SELECT id, username, email, avatar, bio, created_at, {$passwordColumn} AS password_hash
      FROM users
      WHERE email = :login OR username = :login
      LIMIT 1
    ");
    $stmt->execute(['login' => $login]);
    $u = $stmt->fetch();
    return $u ?: null;
  }

  // Retourne les informations publiques d'un utilisateur par son id.
  public static function find(int $id): ?array
  {
    $stmt = self::db()->prepare("SELECT id, username, email, avatar, bio, created_at FROM users WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $u = $stmt->fetch();
    return $u ?: null;
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
  public static function updateProfile(int $id, string $username, string $bio, ?string $passwordHash = null): void
  {
    if ($passwordHash !== null) {
      $passwordColumn = self::resolvePasswordColumn();
      $stmt = self::db()->prepare("UPDATE users SET username = :username, bio = :bio, {$passwordColumn} = :password_hash WHERE id = :id");
      $stmt->execute([
        'id' => $id,
        'username' => $username,
        'bio' => $bio,
        'password_hash' => $passwordHash,
      ]);
      return;
    }

    $stmt = self::db()->prepare("UPDATE users SET username = :username, bio = :bio WHERE id = :id");
    $stmt->execute([
      'id' => $id,
      'username' => $username,
      'bio' => $bio,
    ]);
  }

  // Retourne un avatar fiable : avatar utilisateur si le fichier existe,
  // sinon image de secours.
  public static function avatarPath(?array $user, string $fallback = '/assets/img/figma/mask-group-3.png'): string
  {
    $avatar = trim((string)($user['avatar'] ?? ''));
    if ($avatar !== '') {
      $path = '/' . ltrim($avatar, '/');
      $publicPath = realpath(__DIR__ . '/../../public');
      if ($publicPath !== false && is_file($publicPath . $path)) {
        return $path;
      }
    }

    return $fallback;
  }
}
