<?php
namespace App\Managers;

use App\Core\Model;
use App\Entities\Message;
use PDOException;

final class MessageManager extends Model
{
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
      return 0;
    }
  }

  public static function send(int $senderId, int $receiverId, string $content): void
  {
    $stmt = self::db()->prepare("
      INSERT INTO messages (sender_id, receiver_id, content)
      VALUES (:s, :r, :c)
    ");
    $stmt->execute(['s' => $senderId, 'r' => $receiverId, 'c' => $content]);
  }

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
    }
  }

  public static function inbox(int $me): array
  {
    try {
      $stmt = self::db()->prepare(self::inboxQuery(true));
      $stmt->execute(['me' => $me]);
      return self::hydrateMany($stmt->fetchAll());
    } catch (PDOException $e) {
      $stmt = self::db()->prepare(self::inboxQuery(false));
      $stmt->execute(['me' => $me]);
      return self::hydrateMany($stmt->fetchAll());
    }
  }

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

  private static function hydrateMany(array $rows): array
  {
    return array_map(static fn (array $row): Message => Message::fromArray($row), $rows);
  }

  private static function inboxQuery(bool $withUnreadCount): string
  {
    $unreadSelect = $withUnreadCount ? 'COALESCE(unread.unread_count, 0)' : '0';
    $unreadJoin = $withUnreadCount ? "
        LEFT JOIN (
          SELECT sender_id, COUNT(*) AS unread_count
          FROM messages
          WHERE receiver_id = :me AND is_read = 0
          GROUP BY sender_id
        ) unread ON unread.sender_id = CASE WHEN m.sender_id = :me THEN m.receiver_id ELSE m.sender_id END" : '';

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
