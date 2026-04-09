<?php
namespace App\Managers;

use App\Core\Model;
use App\Entities\User;

// Accès SQL pour les utilisateurs : inscription, connexion, profil, rôles.
// Toutes les requêtes passent par ici et retournent des entités User hydratées.
final class UserManager extends Model
{
  // Avatar par défaut appliqué à la création d'un compte
  public const DEFAULT_AVATAR = '/assets/img/figma/mask-group-2.png';
  // Champs publics sélectionnés dans les requêtes qui n'ont pas besoin du mot de passe
  private const PUBLIC_FIELDS = 'id, username, email, avatar, bio, created_at';
  private const ROLE_USER = 'user';
  private const ROLE_ADMIN = 'admin';

  // Cache du nom de colonne mot de passe (password_hash ou password selon le schéma)
  private static ?string $passwordColumn = null;
  // Cache des colonnes de la table users pour éviter un SHOW COLUMNS à chaque appel
  private static ?array $userColumns = null;

  // Cherche un utilisateur par email.
  // Inclut le hash du mot de passe pour permettre la vérification à la connexion.
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

  // Cherche un utilisateur par pseudo, avec possibilité d'exclure un compte
  // (utile pour vérifier l'unicité lors d'une modification de profil).
  public static function findByUsername(string $username, ?int $excludeId = null): ?User
  {
    $sql = "
      SELECT " . self::PUBLIC_FIELDS . ', ' . self::resolvePasswordColumn() . " AS password_hash
      FROM users
      WHERE username = :username
    ";
    $params = ['username' => $username];

    // On exclut le compte courant pour ne pas bloquer la mise à jour de son propre pseudo
    if ($excludeId !== null) {
      $sql .= ' AND id <> :exclude_id';
      $params['exclude_id'] = $excludeId;
    }

    $sql .= ' LIMIT 1';

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);

    return self::hydrateOne($stmt->fetch() ?: null);
  }

  // Cherche un utilisateur par email ou par pseudo.
  // On accepte les deux pour que le membre puisse se connecter avec l'un ou l'autre.
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

  // Charge les informations publiques d'un utilisateur par son identifiant.
  // On ne charge pas le mot de passe ici, c'est intentionnel.
  public static function find(int $id): ?User
  {
    $stmt = self::db()->prepare('SELECT ' . self::PUBLIC_FIELDS . ' FROM users WHERE id = :id');
    $stmt->execute(['id' => $id]);

    return self::hydrateOne($stmt->fetch() ?: null);
  }

  // Crée un nouveau compte avec un avatar par défaut et retourne son identifiant.
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

  // Met à jour le profil d'un membre.
  // Le mot de passe et l'avatar ne sont modifiés que si de nouvelles valeurs sont fournies.
  public static function updateProfile(
    int $id,
    string $username,
    string $bio,
    ?string $passwordHash = null,
    ?string $avatarPath = null
  ): void {
    // On construit la liste des champs à mettre à jour dynamiquement
    $fields = [
      'username = :username',
      'bio = :bio',
    ];
    $params = [
      'id' => $id,
      'username' => $username,
      'bio' => $bio,
    ];

    // L'avatar n'est mis à jour que si un nouveau fichier a été uploadé
    if ($avatarPath !== null) {
      $fields[] = 'avatar = :avatar';
      $params['avatar'] = $avatarPath;
    }

    // Le mot de passe n'est mis à jour que si le membre en a saisi un nouveau
    if ($passwordHash !== null) {
      $fields[] = self::resolvePasswordColumn() . ' = :password_hash';
      $params['password_hash'] = $passwordHash;
    }

    $stmt = self::db()->prepare('UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = :id');
    $stmt->execute($params);
  }

  // Supprime définitivement un compte utilisateur.
  public static function delete(int $id): void
  {
    $stmt = self::db()->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute(['id' => $id]);
  }

  // Retourne la liste des membres pour le panneau admin, avec leur nombre de livres.
  // Filtre optionnel sur l'id, le pseudo ou l'email.
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

  // Prépare les données de session d'un utilisateur après connexion.
  // On détermine s'il est admin en croisant la config et les colonnes disponibles en base.
  public static function adminSessionData(int $id): array
  {
    $user = self::find($id);
    $config = self::adminConfig();
    $email = self::lowercase(trim($user?->email() ?? ''));

    // Un admin peut être défini par son id ou son email dans la config
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

      // On combine les sources : config statique + colonne is_admin + colonne role
      $isAdmin = $isAdmin || (bool)($row['is_admin'] ?? false);
      $role = isset($row['role']) ? (string)$row['role'] : null;
      $isAdmin = $isAdmin || $role === self::ROLE_ADMIN;
    }

    return [
      'is_admin' => $isAdmin,
      'user_role' => $role,
    ];
  }

  // Retourne vrai si l'utilisateur est administrateur.
  public static function isAdmin(int $id): bool
  {
    return self::adminSessionData($id)['is_admin'];
  }

  // Retourne le libellé du rôle d'un utilisateur ("admin" ou "user").
  public static function roleLabel(int $id): string
  {
    return self::isAdmin($id) ? self::ROLE_ADMIN : self::ROLE_USER;
  }

  // Met à jour le rôle d'un membre.
  // Fonctionne avec les colonnes role et/ou is_admin selon ce que le schéma possède.
  public static function updateRole(int $id, string $role): void
  {
    // On s'assure que seules les valeurs "admin" et "user" peuvent passer
    $role = $role === self::ROLE_ADMIN ? self::ROLE_ADMIN : self::ROLE_USER;

    // Cas idéal : on a les deux colonnes, on met à jour les deux en cohérence
    if (self::hasColumn('role') && self::hasColumn('is_admin')) {
      $stmt = self::db()->prepare('UPDATE users SET role = :role, is_admin = :is_admin WHERE id = :id');
      $stmt->execute([
        'id' => $id,
        'role' => $role,
        'is_admin' => $role === self::ROLE_ADMIN ? 1 : 0,
      ]);
      return;
    }

    // Uniquement la colonne role
    if (self::hasColumn('role')) {
      $stmt = self::db()->prepare('UPDATE users SET role = :role WHERE id = :id');
      $stmt->execute([
        'id' => $id,
        'role' => $role,
      ]);
      return;
    }

    // Aucune colonne disponible : on ne peut pas gérer les rôles, on lève une exception
    if (!self::hasColumn('role') && !self::hasColumn('is_admin')) {
      throw new \RuntimeException("La gestion des roles requiert une colonne 'role' ou 'is_admin' dans la table users.");
    }

    // Uniquement la colonne is_admin (schéma plus ancien)
    $stmt = self::db()->prepare('UPDATE users SET is_admin = :is_admin WHERE id = :id');
    $stmt->execute([
      'id' => $id,
      'is_admin' => $role === self::ROLE_ADMIN ? 1 : 0,
    ]);
  }

  // Hydrate une seule ligne en entité User, ou retourne null si la ligne est vide.
  private static function hydrateOne(?array $row): ?User
  {
    return $row !== null ? User::fromArray($row) : null;
  }

  // Hydrate un tableau de lignes en tableau d'entités User.
  private static function hydrateMany(array $rows): array
  {
    return array_map(static fn (array $row): User => User::fromArray($row), $rows);
  }

  // Détecte le bon nom de colonne pour le mot de passe (password_hash ou password).
  // On met le résultat en cache pour ne faire ce SHOW COLUMNS qu'une seule fois.
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
      // En cas d'erreur, on suppose le nom le plus courant
      self::$passwordColumn = 'password';
    }

    return self::$passwordColumn;
  }

  // Récupère et met en cache la liste des colonnes de la table users.
  // Évite des SHOW COLUMNS répétés à chaque vérification de rôle.
  private static function userColumns(): array
  {
    if (self::$userColumns !== null) {
      return self::$userColumns;
    }

    try {
      $stmt = self::db()->query('SHOW COLUMNS FROM users');
      self::$userColumns = array_map('strval', $stmt->fetchAll(\PDO::FETCH_COLUMN, 0));
    } catch (\Throwable $e) {
      // Si la requête échoue, on retourne un tableau vide : toutes les vérifications retourneront false
      self::$userColumns = [];
    }

    return self::$userColumns;
  }

  // Vérifie si une colonne donnée existe dans la table users.
  private static function hasColumn(string $column): bool
  {
    return in_array($column, self::userColumns(), true);
  }

  // Lit la configuration admin depuis config.php :
  // liste des ids et emails qui ont les droits admin sans passer par la base.
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

  // Convertit une chaîne en minuscules, avec ou sans mbstring.
  private static function lowercase(string $value): string
  {
    if (function_exists('mb_strtolower')) {
      return mb_strtolower($value);
    }

    return strtolower($value);
  }
}
