<?php
namespace App\Models;

use App\Managers\UserManager;
use App\Core\Url;

// Façade du domaine utilisateur : point d'entrée unique pour tout ce qui touche aux membres.
// Les requêtes SQL sont déléguées à UserManager, qui hydrate des entités App\Entities\User.
class User
{
  // Avatar par défaut utilisé si l'utilisateur n'en a pas encore défini un
  public const DEFAULT_AVATAR = '/assets/img/figma/mask-group-2.png';

  // Recherche un utilisateur par email.
  // Utile à l'inscription pour vérifier qu'une adresse n'est pas déjà prise.
  public static function findByEmail(string $email): ?array
  {
    return UserManager::findByEmail($email)?->toArray();
  }

  // Vérifie si un pseudo est déjà pris.
  // On peut exclure un compte de la recherche pour ne pas bloquer une mise à jour de profil.
  public static function findByUsername(string $username, ?int $excludeId = null): ?array
  {
    return UserManager::findByUsername($username, $excludeId)?->toArray();
  }

  // Recherche un utilisateur par email ou par pseudo.
  // Le membre peut se connecter avec l'un ou l'autre.
  public static function findByLogin(string $login): ?array
  {
    return UserManager::findByLogin($login)?->toArray();
  }

  // Retourne les informations publiques d'un utilisateur par son identifiant.
  // Le mot de passe n'est pas inclus dans cette requête.
  public static function find(int $id): ?array
  {
    return UserManager::find($id)?->toArray();
  }

  // Prépare les données de session juste après une connexion ou une inscription.
  // Détermine si le membre est admin et quel est son rôle.
  public static function adminSessionData(int $id): array
  {
    return UserManager::adminSessionData($id);
  }

  // Retourne vrai si l'utilisateur est administrateur.
  public static function isAdmin(int $id): bool
  {
    return UserManager::isAdmin($id);
  }

  // Retourne le libellé du rôle ("admin" ou "user") pour l'affichage dans le panneau admin.
  public static function roleLabel(int $id): string
  {
    return UserManager::roleLabel($id);
  }

  // Change le rôle d'un membre depuis le panneau admin.
  public static function updateRole(int $id, string $role): void
  {
    UserManager::updateRole($id, $role);
  }

  // Retourne la liste des membres pour le panneau admin, avec filtre optionnel.
  public static function adminMembers(string $query = ''): array
  {
    return array_map(
      static fn (\App\Entities\User $user): array => $user->toArray(),
      UserManager::adminMembers($query)
    );
  }

  // Crée un nouveau compte et retourne son identifiant.
  // L'avatar par défaut est appliqué automatiquement par UserManager.
  public static function create(string $username, string $email, string $passwordHash): int
  {
    return UserManager::create($username, $email, $passwordHash);
  }

  // Met à jour le profil d'un membre.
  // Si un nouveau mot de passe ou un nouvel avatar est fourni, il est enregistré aussi.
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

  // Supprime définitivement un compte utilisateur.
  public static function delete(int $id): void
  {
    UserManager::delete($id);
  }

  // Retourne le chemin de l'avatar d'un utilisateur si le fichier existe bien sur le serveur,
  // sinon on utilise l'image de secours pour éviter les images cassées.
  public static function avatarPath(?array $user, string $fallback = '/assets/img/figma/mask-group-3.png'): string
  {
    $avatar = trim((string)($user['avatar'] ?? ''));

    if ($avatar !== '') {
      $path = '/' . ltrim($avatar, '/');
      // On vérifie que le fichier existe physiquement avant de l'utiliser
      if (Url::publicFileExists($path)) {
        return $path;
      }
    }

    return $fallback;
  }
}
