<?php
declare(strict_types=1);

namespace App\Models;

final class Book extends BaseModel
{
    public function latest(int $limit = 4): array
    {
        $stmt = $this->pdo->prepare('
            SELECT b.*, u.username
            FROM books b
            JOIN users u ON u.id = b.user_id
            ORDER BY b.created_at DESC
            LIMIT :lim
        ');
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function searchAvailable(?string $q): array
    {
        $q = trim((string)$q);
        $sql = '
            SELECT b.*, u.username
            FROM books b
            JOIN users u ON u.id = b.user_id
            WHERE b.status = "available"
        ';
        $params = [];
        if ($q !== '') {
            $sql .= ' AND b.title LIKE :q ';
            $params[':q'] = '%' . $q . '%';
        }
        $sql .= ' ORDER BY b.created_at DESC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT b.*, u.username, u.id AS owner_id
            FROM books b
            JOIN users u ON u.id = b.user_id
            WHERE b.id = :id
            LIMIT 1
        ');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function mine(int $userId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT *
            FROM books
            WHERE user_id = :uid
            ORDER BY created_at DESC
        ');
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, string $title, string $author, ?string $image, ?string $description, string $status): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO books (user_id, title, author, image, description, status, created_at)
            VALUES (:uid, :title, :author, :image, :description, :status, NOW())
        ');
        $stmt->execute([
            ':uid' => $userId,
            ':title' => $title,
            ':author' => $author,
            ':image' => $image,
            ':description' => $description,
            ':status' => $status,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $bookId, int $userId, string $title, string $author, ?string $image, ?string $description, string $status): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE books
            SET title = :title, author = :author, image = :image, description = :description, status = :status
            WHERE id = :id AND user_id = :uid
        ');
        return $stmt->execute([
            ':title' => $title,
            ':author' => $author,
            ':image' => $image,
            ':description' => $description,
            ':status' => $status,
            ':id' => $bookId,
            ':uid' => $userId,
        ]);
    }

    public function delete(int $bookId, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM books WHERE id = :id AND user_id = :uid');
        return $stmt->execute([':id' => $bookId, ':uid' => $userId]);
    }

    public function allWithOwner(): array
    {
        $stmt = $this->pdo->query('
            SELECT b.*, u.username, u.email
            FROM books b
            JOIN users u ON u.id = b.user_id
            ORDER BY b.created_at DESC
        ');

        return $stmt->fetchAll();
    }

    public function adminSetStatus(int $bookId, string $status): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE books
            SET status = :status
            WHERE id = :id
        ');

        return $stmt->execute([
            ':status' => $status,
            ':id' => $bookId,
        ]);
    }

    public function adminDelete(int $bookId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM books WHERE id = :id');
        return $stmt->execute([':id' => $bookId]);
    }
}
