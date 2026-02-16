<?php
declare(strict_types=1);

namespace App\Models;

final class Message extends BaseModel
{
    public function inbox(int $userId): array
    {
        // Liste des conversations: dernier message par interlocuteur
        $stmt = $this->pdo->prepare('
            SELECT m1.*
            FROM messages m1
            JOIN (
                SELECT
                    CASE
                        WHEN sender_id = :uid THEN receiver_id
                        ELSE sender_id
                    END AS other_id,
                    MAX(created_at) AS max_created
                FROM messages
                WHERE sender_id = :uid OR receiver_id = :uid
                GROUP BY other_id
            ) t
            ON (
                (
                  (m1.sender_id = :uid AND m1.receiver_id = t.other_id)
                  OR (m1.receiver_id = :uid AND m1.sender_id = t.other_id)
                )
                AND m1.created_at = t.max_created
            )
            ORDER BY m1.created_at DESC
        ');
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function thread(int $userId, int $otherId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT *
            FROM messages
            WHERE (sender_id = :uid AND receiver_id = :oid)
               OR (sender_id = :oid AND receiver_id = :uid)
            ORDER BY created_at ASC
        ');
        $stmt->execute([':uid' => $userId, ':oid' => $otherId]);
        return $stmt->fetchAll();
    }

    public function send(int $senderId, int $receiverId, string $content): int
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO messages (sender_id, receiver_id, content, created_at)
            VALUES (:sid, :rid, :content, NOW())
        ');
        $stmt->execute([
            ':sid' => $senderId,
            ':rid' => $receiverId,
            ':content' => $content,
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}
