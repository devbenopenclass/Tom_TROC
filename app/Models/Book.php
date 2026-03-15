<?php
namespace App\Models;

use App\Core\Model;

class Book extends Model
{
  public static function latest(int $limit = 4): array
  {
    $stmt = self::db()->prepare("
      SELECT b.*, u.username
      FROM books b
      JOIN users u ON u.id = b.user_id
      ORDER BY b.created_at DESC
      LIMIT {$limit}
    ");
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public static function exchangeList(?string $q = null): array
  {
    if ($q) {
      $stmt = self::db()->prepare("
        SELECT b.*, u.username
        FROM books b
        JOIN users u ON u.id = b.user_id
        WHERE b.status = 'available' AND b.title LIKE :q
        ORDER BY b.created_at DESC
      ");
      $stmt->execute(['q' => "%{$q}%"]);
      return $stmt->fetchAll();
    }

    $stmt = self::db()->query("
      SELECT b.*, u.username
      FROM books b
      JOIN users u ON u.id = b.user_id
      WHERE b.status = 'available'
      ORDER BY b.created_at DESC
    ");
    return $stmt->fetchAll();
  }

  public static function find(int $id): ?array
  {
    $stmt = self::db()->prepare("
      SELECT b.*, u.username
      FROM books b
      JOIN users u ON u.id = b.user_id
      WHERE b.id = :id
      LIMIT 1
    ");
    $stmt->execute(['id' => $id]);
    $b = $stmt->fetch();
    return $b ?: null;
  }

  public static function byUser(int $userId): array
  {
    $stmt = self::db()->prepare("SELECT * FROM books WHERE user_id = :uid ORDER BY created_at DESC");
    $stmt->execute(['uid' => $userId]);
    return $stmt->fetchAll();
  }

  public static function create(array $data): int
  {
    $stmt = self::db()->prepare("
      INSERT INTO books (user_id, title, author, image, description, status)
      VALUES (:user_id, :title, :author, :image, :description, :status)
    ");
    $stmt->execute($data);
    return (int) self::db()->lastInsertId();
  }

  public static function update(int $id, int $userId, array $data): void
  {
    $data['id'] = $id;
    $data['user_id'] = $userId;

    $stmt = self::db()->prepare("
      UPDATE books
      SET title=:title, author=:author, image=:image, description=:description, status=:status
      WHERE id=:id AND user_id=:user_id
    ");
    $stmt->execute($data);
  }

  public static function delete(int $id, int $userId): void
  {
    $stmt = self::db()->prepare("DELETE FROM books WHERE id = :id AND user_id = :uid");
    $stmt->execute(['id' => $id, 'uid' => $userId]);
  }
}
