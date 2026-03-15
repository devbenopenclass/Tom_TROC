<?php
namespace App\Models;

use App\Core\Model;

class Book extends Model
{
  public static function latest(int $limit = 4): array
  {
    try {
      $stmt = self::db()->prepare("
        SELECT b.*, u.username
        FROM books b
        JOIN users u ON u.id = b.user_id
        ORDER BY b.created_at DESC
        LIMIT {$limit}
      ");
      $stmt->execute();
      $books = $stmt->fetchAll();
      if (!empty($books)) {
        return $books;
      }
    } catch (\Throwable $e) {
    }

    return array_slice(self::fallbackCatalog(), 0, $limit);
  }

  public static function exchangeList(?string $q = null): array
  {
    try {
      if ($q) {
        $stmt = self::db()->prepare("
          SELECT b.*, u.username
          FROM books b
          JOIN users u ON u.id = b.user_id
          WHERE b.title LIKE :q OR b.author LIKE :q OR u.username LIKE :q
          ORDER BY b.created_at DESC
        ");
        $stmt->execute(['q' => "%{$q}%"]);
        $books = $stmt->fetchAll();
      } else {
        $stmt = self::db()->query("
          SELECT b.*, u.username
          FROM books b
          JOIN users u ON u.id = b.user_id
          ORDER BY b.created_at DESC
        ");
        $books = $stmt->fetchAll();
      }

      if (!empty($books)) {
        return $books;
      }
    } catch (\Throwable $e) {
    }

    return self::filterFallbackCatalog($q);
  }

  public static function find(int $id): ?array
  {
    try {
      $stmt = self::db()->prepare("
        SELECT b.*, u.username
        FROM books b
        JOIN users u ON u.id = b.user_id
        WHERE b.id = :id
        LIMIT 1
      ");
      $stmt->execute(['id' => $id]);
      $book = $stmt->fetch();
      if ($book) {
        return $book;
      }
    } catch (\Throwable $e) {
    }

    foreach (self::fallbackCatalog() as $book) {
      if ((int)$book['id'] === $id) {
        return $book;
      }
    }

    return null;
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

  private static function filterFallbackCatalog(?string $q = null): array
  {
    $books = self::fallbackCatalog();

    if (!$q) {
      return $books;
    }

    $needle = mb_strtolower($q);

    return array_values(array_filter($books, static function (array $book) use ($needle): bool {
      $haystack = mb_strtolower(
        ($book['title'] ?? '') . ' ' .
        ($book['author'] ?? '') . ' ' .
        ($book['username'] ?? '')
      );

      return str_contains($haystack, $needle);
    }));
  }

  private static function fallbackCatalog(): array
  {
    static $books = null;

    if ($books !== null) {
      return $books;
    }

    $books = [
      self::fallbackBook(9001, 301, 'Esther', 'Alabaster', 'CamilleDuCuir', 'available', '/assets/img/figma/latest-card-1.png'),
      self::fallbackBook(9002, 302, 'The Kinfolk Table', 'Nathan Williams', 'Nathalie', 'available', '/assets/img/figma/latest-card-2.png'),
      self::fallbackBook(9003, 303, 'Wabi Sabi', 'Beth Kempton', 'Alicecture', 'available', '/assets/img/figma/latest-card-3.png'),
      self::fallbackBook(9004, 304, 'Milk & honey', 'Rupi Kaur', 'Hugo1990_12', 'available', '/assets/img/figma/latest-card-4.png'),
      self::fallbackBook(9005, 305, 'Delight!', 'Justin Rossow', 'Juju1432', 'unavailable', '/assets/img/Card livre-1.png'),
      self::fallbackBook(9006, 306, 'Milwaukee Mission', 'Elder Cooper Low', 'Christiane75014', 'available', '/assets/img/Card livre-2.png'),
      self::fallbackBook(9007, 307, 'Minimalist Graphics', 'Julia Schonlau', 'Hamzalecture', 'available', '/assets/img/Card livre-3.png'),
      self::fallbackBook(9008, 308, 'Hygge', 'Meik Wiking', 'Hugo1990_12', 'available', '/assets/img/Card livre-4.png'),
      self::fallbackBook(9009, 309, 'Innovation', 'Matt Ridley', 'LouBen50', 'available', '/assets/img/Card livre-5.png'),
      self::fallbackBook(9010, 310, 'Psalms', 'Alabaster', 'Lolobzh', 'available', '/assets/img/Card livre-6.png'),
      self::fallbackBook(9011, 311, 'Thinking, Fast & Slow', 'Daniel Kahneman', 'Sas634', 'unavailable', '/assets/img/Card livre-7.png'),
      self::fallbackBook(9012, 312, 'A Book Full Of Hope', 'Rupi Kaur', 'ML95', 'available', '/assets/img/Card livre-8.png'),
      self::fallbackBook(9013, 313, 'The Subtle Art Of Not Giving A F*ck', 'Mark Manson', 'Verogo33', 'available', '/assets/img/Card livre-9.png'),
      self::fallbackBook(9014, 314, 'Narnia', 'C.S Lewis', 'AnnikaBrahms', 'unavailable', '/assets/img/Card livre-10.png'),
      self::fallbackBook(9015, 315, 'Company Of One', 'Paul Jarvis', 'Victoirefabr912', 'available', '/assets/img/Card livre-11.png'),
      self::fallbackBook(9016, 316, 'The Two Towers', 'J.R.R Tolkien', 'Lotrfanclub67', 'available', '/assets/img/Card livre-12.png'),
    ];

    return $books;
  }

  private static function fallbackBook(
    int $id,
    int $userId,
    string $title,
    string $author,
    string $username,
    string $status,
    string $image
  ): array {
    return [
      'id' => $id,
      'user_id' => $userId,
      'title' => $title,
      'author' => $author,
      'username' => $username,
      'status' => $status,
      'image' => $image,
      'description' => sprintf(
        '%s par %s, propose par %s dans la bibliotheque Tom Troc.',
        $title,
        $author,
        $username
      ),
      'created_at' => date('Y-m-d H:i:s'),
    ];
  }
}
