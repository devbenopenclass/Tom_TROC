<?php
namespace App\Models;

use App\Managers\MessageManager;

// Façade du domaine messagerie : point d'entrée unique pour tout ce qui touche aux messages.
// Les requêtes SQL sont déléguées à MessageManager, qui hydrate des entités App\Entities\Message.
class Message
{
  // Retourne le nombre de messages non lus pour afficher le badge dans le header.
  // Si l'ancien schéma SQL n'a pas la colonne is_read, MessageManager renvoie 0 sans casser.
  public static function unreadCount(int $me): int
  {
    return MessageManager::unreadCount($me);
  }

  // Insère un message dans la base.
  public static function send(int $senderId, int $receiverId, string $content): void
  {
    MessageManager::send($senderId, $receiverId, $content);
  }

  // Vérifie si deux membres ont déjà un historique d'échange, dans un sens ou dans l'autre.
  // Cela permet d'autoriser les réponses sans avoir besoin d'un nouveau contexte livre.
  public static function hasThread(int $me, int $other): bool
  {
    return MessageManager::hasThread($me, $other);
  }

  // Charge tout le fil de discussion entre deux membres dans l'ordre chronologique.
  // Les pseudos et avatars des deux côtés sont inclus pour l'affichage.
  public static function thread(int $me, int $other): array
  {
    return array_map(
      static fn (\App\Entities\Message $message): array => $message->toArray(),
      MessageManager::thread($me, $other)
    );
  }

  // Marque comme lus les messages reçus dans le fil actif.
  // On appelle ça dès qu'on ouvre une conversation pour mettre le badge à jour.
  public static function markThreadAsRead(int $me, int $other): void
  {
    MessageManager::markThreadAsRead($me, $other);
  }

  // Construit la colonne de gauche de la messagerie :
  // une ligne par interlocuteur avec le dernier message et le compteur de non lus.
  public static function inbox(int $me): array
  {
    return array_map(
      static fn (\App\Entities\Message $message): array => $message->toArray(),
      MessageManager::inbox($me)
    );
  }

  // Retourne tous les autres membres avec leur nombre de livres.
  // Utilisé pour composer un premier message depuis la liste des contacts.
  public static function contacts(int $me): array
  {
    return array_map(
      static fn (\App\Entities\Message $message): array => $message->toArray(),
      MessageManager::contacts($me)
    );
  }
}
