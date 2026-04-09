<?php
namespace App\Core;

// Service d'authentification basé sur la session PHP.
// Centralise les vérifications "connecté / non connecté" pour toute l'appli.
class Auth
{
  // Retourne vrai si un utilisateur est connecté.
  public static function check(): bool
  {
    return isset($_SESSION['user_id']);
  }

  // Retourne l'id de l'utilisateur connecté, ou null si personne n'est connecté.
  public static function id(): ?int
  {
    return $_SESSION['user_id'] ?? null;
  }

  // Protège une page : redirige vers /login si la session est vide.
  public static function requireLogin(): void
  {
    if (!self::check()) {
      header('Location: ' . Url::withBase('/login'));
      exit;
    }
  }

  // Retourne le nombre de messages non lus pour afficher le badge dans le header.
  // Si personne n'est connecté, on renvoie 0 directement sans interroger la base.
  public static function unreadCount(): int
  {
    $userId = self::id();
    if ($userId === null) {
      return 0;
    }

    return \App\Models\Message::unreadCount($userId);
  }
}
