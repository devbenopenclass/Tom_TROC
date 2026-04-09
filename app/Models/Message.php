<?php
namespace App\Models;

use App\Managers\MessageManager;

// Modèle de messagerie interne : conversations, messages,
// compteur de non lus et création des fils de discussion.
// Les accès SQL sont maintenant centralisés dans MessageManager
// et hydratés dans une vraie entité App\Entities\Message.
class Message
{
  // Calcule le badge affiché dans le header.
  // Si l'ancien schéma SQL n'a pas `is_read`, on renvoie 0 au lieu de casser.
  public static function unreadCount(int $me): int
  {
    return MessageManager::unreadCount($me);
  }

  // Insère un message simple dans la table messages.
  public static function send(int $senderId, int $receiverId, string $content): void
  {
    MessageManager::send($senderId, $receiverId, $content);
  }

  // Vérifie si deux membres ont déjà un historique d'échange.
  // Cela permet d'autoriser les réponses même sans nouveau contexte livre.
  public static function hasThread(int $me, int $other): bool
  {
    return MessageManager::hasThread($me, $other);
  }

  // Charge tout le fil de discussion entre deux membres,
  // enrichi avec les pseudos et avatars des deux côtés.
  public static function thread(int $me, int $other): array
  {
    return array_map(
      static fn (\App\Entities\Message $message): array => $message->toArray(),
      MessageManager::thread($me, $other)
    );
  }

  // Marque comme lus les messages reçus dans le fil actif.
  public static function markThreadAsRead(int $me, int $other): void
  {
    MessageManager::markThreadAsRead($me, $other);
  }

  // Construit la colonne de gauche de la messagerie :
  // une ligne par conversation avec dernier message + compteur non lu.
  public static function inbox(int $me): array
  {
    return array_map(
      static fn (\App\Entities\Message $message): array => $message->toArray(),
      MessageManager::inbox($me)
    );
  }

  // Liste des autres membres contactables, avec nombre de livres.
  public static function contacts(int $me): array
  {
    return array_map(
      static fn (\App\Entities\Message $message): array => $message->toArray(),
      MessageManager::contacts($me)
    );
  }
}
