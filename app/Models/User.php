<?php
declare(strict_types=1);

namespace App\Models;

final class User extends BaseModel
{
    public function create(string $username, string $email, string $passwordHash): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO users (username, email, password, created_at)
            VALUES (:username, :email, :password, NOW())
        ');
        $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $passwordHash,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, username, email, bio, avatar, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function updateProfile(int $id, string $username, ?string $bio): bool
    {
        $stmt = $this->pdo->prepare('
            UPDATE users
            SET username = :username, bio = :bio
            WHERE id = :id
        ');
        return $stmt->execute([
            ':username' => $username,
            ':bio' => $bio,
            ':id' => $id,
        ]);
    }
}
