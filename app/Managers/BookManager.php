<?php
namespace App\Managers;

use App\Core\Model;
use App\Entities\Book;

final class BookManager extends Model
{
  public static function latest(int $limit = 4): array
  {
    $stmt = self::db()->prepare("
      SELECT b.*, u.username
      FROM books b
      JOIN users u ON u.id = b.user_id
      ORDER BY b.created_at DESC
      LIMIT :limit
    ");
    $stmt->bindValue('limit', max(1, $limit), \PDO::PARAM_INT);
    $stmt->execute();

    return self::hydrateMany($stmt->fetchAll());
  }

  public static function exchangeList(?string $query = null): array
  {
    if ($query !== null && $query !== '') {
      $stmt = self::db()->prepare("
        SELECT b.*, u.username
        FROM books b
        JOIN users u ON u.id = b.user_id
        WHERE b.title LIKE :query OR b.author LIKE :query OR u.username LIKE :query
        ORDER BY b.created_at DESC
      ");
      $stmt->execute(['query' => '%' . $query . '%']);
      return self::hydrateMany($stmt->fetchAll());
    }

    $stmt = self::db()->query("
      SELECT b.*, u.username
      FROM books b
      JOIN users u ON u.id = b.user_id
      ORDER BY b.created_at DESC
    ");

    return self::hydrateMany($stmt->fetchAll());
  }

  public static function find(int $id): ?Book
  {
    $stmt = self::db()->prepare("
      SELECT b.*, u.username
      FROM books b
      JOIN users u ON u.id = b.user_id
      WHERE b.id = :id
      LIMIT 1
    ");
    $stmt->execute(['id' => $id]);

    return self::hydrateOne($stmt->fetch() ?: null);
  }

  public static function byUser(int $userId): array
  {
    $stmt = self::db()->prepare('SELECT * FROM books WHERE user_id = :uid ORDER BY created_at DESC');
    $stmt->execute(['uid' => $userId]);

    return self::hydrateMany($stmt->fetchAll());
  }

  public static function create(array $data): int
  {
    $stmt = self::db()->prepare("
      INSERT INTO books (user_id, title, author, image, description, status)
      VALUES (:user_id, :title, :author, :image, :description, :status)
    ");
    $stmt->execute($data);

    return (int)self::db()->lastInsertId();
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
    $stmt = self::db()->prepare('DELETE FROM books WHERE id = :id AND user_id = :uid');
    $stmt->execute(['id' => $id, 'uid' => $userId]);
  }

  public static function adminList(string $query = ''): array
  {
    $sql = implode("\n", [
      'SELECT b.*, u.username, u.email',
      'FROM books b',
      'JOIN users u ON u.id = b.user_id',
    ]);
    $params = [];

    if ($query !== '') {
      $sql .= implode("\n", [
        '',
        'WHERE b.title LIKE :query',
        '   OR b.author LIKE :query',
        '   OR u.username LIKE :query',
        '   OR u.email LIKE :query',
      ]);
      $params['query'] = '%' . $query . '%';
    }

    $sql .= implode("\n", [
      '',
      'ORDER BY b.created_at DESC, b.id DESC',
    ]);

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);

    return self::hydrateMany($stmt->fetchAll());
  }

  public static function adminUpdateStatus(int $id, string $status): void
  {
    $stmt = self::db()->prepare('UPDATE books SET status = :status WHERE id = :id');
    $stmt->execute([
      'id' => $id,
      'status' => $status,
    ]);
  }

  public static function adminDelete(int $id): void
  {
    $stmt = self::db()->prepare('DELETE FROM books WHERE id = :id');
    $stmt->execute(['id' => $id]);
  }

  private static function hydrateOne(?array $row): ?Book
  {
    return $row !== null ? Book::fromArray($row) : null;
  }

  private static function hydrateMany(array $rows): array
  {
    return array_map(static fn (array $row): Book => Book::fromArray($row), $rows);
  }
}
