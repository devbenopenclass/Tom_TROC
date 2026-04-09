<?php
namespace App\Models;

use App\Managers\UserManager;
use App\Core\Url;

// Façade de compatibilité du domaine utilisateur.
// Les accès SQL sont maintenant portés par UserManager et hydratés
// dans une vraie entité App\Entities\User.
class User
{
  public const DEFAULT_AVATAR = '/assets/img/figma/mask-group-2.png';

  // Recherche un utilisateur par email uniquement,
  // utile pour vérifier l'unicité à l'inscription.
  public static function findByEmail(string $email): ?array
  {
    return UserManager::findByEmail($email)?->toArray();
  }

  // Vérifie l'unicité du pseudo côté application.
  // Un compte peut être exclu du contrôle lors d'une mise à jour de profil.
  public static function findByUsername(string $username, ?int $excludeId = null): ?array
  {
    return UserManager::findByUsername($username, $excludeId)?->toArray();
  }

  // Recherche par identifiant de connexion :
  // l'utilisateur peut se connecter avec son email ou son pseudo.
  public static function findByLogin(string $login): ?array
  {
    return UserManager::findByLogin($login)?->toArray();
  }

  // Retourne les informations publiques d'un utilisateur par son id.
  public static function find(int $id): ?array
  {
    return UserManager::find($id)?->toArray();
  }

  public static function adminSessionData(int $id): array
  {
    return UserManager::adminSessionData($id);
  }

  public static function isAdmin(int $id): bool
  {
    return UserManager::isAdmin($id);
  }

  public static function roleLabel(int $id): string
  {
    return UserManager::roleLabel($id);
  }

  public static function updateRole(int $id, string $role): void
  {
    UserManager::updateRole($id, $role);
  }

  public static function adminMembers(string $query = ''): array
  {
    return array_map(
      static fn (\App\Entities\User $user): array => $user->toArray(),
      UserManager::adminMembers($query)
    );
  }

  // Crée un nouveau compte membre avec un avatar par défaut.
  public static function create(string $username, string $email, string $passwordHash): int
  {
    return UserManager::create($username, $email, $passwordHash);
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
    UserManager::updateProfile($id, $username, $bio, $passwordHash, $avatarPath);
  }

  public static function delete(int $id): void
  {
    UserManager::delete($id);
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
