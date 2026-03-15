<?php
namespace App\Models;

use App\Core\Model;
use PDOException;

class Message extends Model
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
      // Fallback for older schemas where `is_read` is missing.
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

  public static function thread(int $me, int $other): array
  {
    $stmt = self::db()->prepare("
      SELECT m.*, us.username AS sender_name, ur.username AS receiver_name
      FROM messages m
      JOIN users us ON us.id = m.sender_id
      JOIN users ur ON ur.id = m.receiver_id
      WHERE (m.sender_id = :me AND m.receiver_id = :other)
         OR (m.sender_id = :other AND m.receiver_id = :me)
      ORDER BY m.created_at ASC
    ");
    $stmt->execute(['me' => $me, 'other' => $other]);
    return $stmt->fetchAll();
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
      // Ignore on legacy schemas.
    }
  }

  public static function inbox(int $me): array
  {
    $stmt = self::db()->prepare("
      SELECT m.*
      FROM messages m
      WHERE m.id IN (
        SELECT MAX(id)
        FROM messages
        WHERE sender_id = :me OR receiver_id = :me
        GROUP BY LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id)
      )
      ORDER BY m.created_at DESC
    ");
    $stmt->execute(['me' => $me]);
    return $stmt->fetchAll();
  }
}
