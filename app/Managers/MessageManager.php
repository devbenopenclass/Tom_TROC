<?php
namespace App\Managers;

use App\Core\Model;
use App\Entities\Message;
use PDOException;

// Accès SQL pour la messagerie : envoi, lecture des fils, compteur non lus,
// et liste des conversations. Tout retourne des entités Message hydratées.
final class MessageManager extends Model
{
  // Compte les messages non lus pour l'utilisateur connecté.
  // En cas d'erreur SQL (ex : colonne is_read absente sur un vieux schéma), on renvoie 0.
  public static function unreadCount(int $me): int
  {
    try {
      $stmt = self::db()->prepare("
        SELECT COUNT(*) AS c
        FROM messages
        WHERE receiver_id = :me AND is_read = 0
      ");
      $stmt->execute(['me' => $me]);
      $row = $stmt->fetch();
      return (int)($row['c'] ?? 0);
    } catch (PDOException $e) {
      // On ne bloque pas toute la page si la colonne n'existe pas encore
      return 0;
    }
  }

  // Insère un message dans la table.
  public static function send(int $senderId, int $receiverId, string $content): void
  {
    $stmt = self::db()->prepare("
      INSERT INTO messages (sender_id, receiver_id, content)
      VALUES (:s, :r, :c)
    ");
    $stmt->execute(['s' => $senderId, 'r' => $receiverId, 'c' => $content]);
  }

  // Vérifie si deux membres ont déjà échangé au moins un message,
  // dans un sens ou dans l'autre.
  public static function hasThread(int $me, int $other): bool
  {
    $stmt = self::db()->prepare("
      SELECT 1
      FROM messages
      WHERE (sender_id = :me AND receiver_id = :other)
         OR (sender_id = :other AND receiver_id = :me)
      LIMIT 1
    ");
    $stmt->execute(['me' => $me, 'other' => $other]);

    return (bool)$stmt->fetchColumn();
  }

  // Retourne tous les messages entre deux membres, triés par date croissante
  // pour afficher la conversation dans l'ordre chronologique.
  // Les pseudos et avatars des deux côtés sont inclus pour éviter des requêtes supplémentaires.
  public static function thread(int $me, int $other): array
  {
    $stmt = self::db()->prepare("
      SELECT m.*, us.username AS sender_name, us.avatar AS sender_avatar, ur.username AS receiver_name, ur.avatar AS receiver_avatar
      FROM messages m
      JOIN users us ON us.id = m.sender_id
      JOIN users ur ON ur.id = m.receiver_id
      WHERE (m.sender_id = :me AND m.receiver_id = :other)
         OR (m.sender_id = :other AND m.receiver_id = :me)
      ORDER BY m.created_at ASC
    ");
    $stmt->execute(['me' => $me, 'other' => $other]);

    return self::hydrateMany($stmt->fetchAll());
  }

  // Marque comme lus tous les messages reçus dans le fil actif.
  // On ignore silencieusement les erreurs si la colonne is_read n'existe pas.
  public static function markThreadAsRead(int $me, int $other): void
  {
    try {
      $stmt = self::db()->prepare("
        UPDATE messages
        SET is_read = 1
        WHERE sender_id = :other
          AND receiver_id = :me
          AND is_read = 0
      ");
      $stmt->execute(['me' => $me, 'other' => $other]);
    } catch (PDOException $e) {
      // Pas grave si ça échoue, le badge sera juste potentiellement incorrect
    }
  }

  // Retourne la liste des conversations de l'utilisateur :
  // une ligne par interlocuteur avec le dernier message et le compteur de non lus.
  // On tente d'abord la requête avec les non lus, et si elle échoue (vieux schéma),
  // on retombe sur la version sans compteur.
  public static function inbox(int $me): array
  {
    try {
      $stmt = self::db()->prepare(self::inboxQuery(true));
      $stmt->execute(['me' => $me]);
      return self::hydrateMany($stmt->fetchAll());
    } catch (PDOException $e) {
      // Fallback si la colonne is_read n'existe pas encore dans la base
      $stmt = self::db()->prepare(self::inboxQuery(false));
      $stmt->execute(['me' => $me]);
      return self::hydrateMany($stmt->fetchAll());
    }
  }

  // Retourne tous les membres (sauf soi-même) avec leur nombre de livres.
  // Utilisé pour la liste des contacts dans la messagerie.
  public static function contacts(int $me): array
  {
    $stmt = self::db()->prepare("
      SELECT u.id, u.username, u.email, u.avatar, COUNT(b.id) AS books_count
      FROM users u
      LEFT JOIN books b ON b.user_id = u.id
      WHERE u.id <> :me
      GROUP BY u.id, u.username, u.email, u.avatar
      ORDER BY books_count DESC, u.username ASC
    ");
    $stmt->execute(['me' => $me]);

    return self::hydrateMany($stmt->fetchAll());
  }

  // Hydrate un tableau de lignes en tableau d'entités Message.
  private static function hydrateMany(array $rows): array
  {
    return array_map(static fn (array $row): Message => Message::fromArray($row), $rows);
  }

  // Construit la requête SQL de la boîte de réception.
  // $withUnreadCount permet d'activer ou désactiver le compteur de messages non lus
  // selon que la colonne is_read existe ou non dans la base.
  private static function inboxQuery(bool $withUnreadCount): string
  {
    // Si on peut compter les non lus, on prépare la jointure correspondante
    $unreadSelect = $withUnreadCount ? 'COALESCE(unread.unread_count, 0)' : '0';
    $unreadJoin = $withUnreadCount ? "
        LEFT JOIN (
          SELECT sender_id, COUNT(*) AS unread_count
          FROM messages
          WHERE receiver_id = :me AND is_read = 0
          GROUP BY sender_id
        ) unread ON unread.sender_id = CASE WHEN m.sender_id = :me THEN m.receiver_id ELSE m.sender_id END" : '';

    // La sous-requête "latest" garantit qu'on ne récupère que le dernier message par fil
    return "
      SELECT
        CASE WHEN m.sender_id = :me THEN m.receiver_id ELSE m.sender_id END AS other_id,
        u.username AS other_username,
        u.avatar AS other_avatar,
        m.content AS last_message,
        m.created_at AS last_at,
        {$unreadSelect} AS unread_count
      FROM messages m
      JOIN users u
        ON u.id = CASE WHEN m.sender_id = :me THEN m.receiver_id ELSE m.sender_id END
      JOIN (
        SELECT
          MAX(id) AS last_message_id
        FROM messages
        WHERE sender_id = :me OR receiver_id = :me
        GROUP BY CASE WHEN sender_id = :me THEN receiver_id ELSE sender_id END
      ) latest ON latest.last_message_id = m.id
      {$unreadJoin}
      ORDER BY m.created_at DESC, m.id DESC
    ";
  }
}
