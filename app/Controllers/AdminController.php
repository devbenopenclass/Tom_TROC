<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Model;
use App\Models\User;
use PDO;

// Contrôleur d'administration : version alignée avec le framework actuel.
// Il évite les dépendances à d'anciens helpers absents du projet.
final class AdminController extends Controller
{
    private const BOOKS_PATH = '/admin/books';
    private const ALLOWED_BOOK_STATUSES = ['available', 'unavailable', 'reserved'];

    public function books(): void
    {
        $this->requireAdmin();

        $stmt = $this->db()->query(
            'SELECT b.*, u.username, u.email
             FROM books b
             JOIN users u ON u.id = b.user_id
             ORDER BY b.created_at DESC, b.id DESC'
        );

        $this->render('admin/books', [
            'books' => $stmt->fetchAll(),
        ]);
    }

    public function updateBookStatus(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $status = $this->normalizeBookStatus((string)($_POST['status'] ?? 'available'));
        if ($id <= 0) {
            $this->redirect(self::BOOKS_PATH);
        }

        $stmt = $this->db()->prepare('UPDATE books SET status = :status WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);

        $this->redirect(self::BOOKS_PATH);
    }

    public function deleteBook(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect(self::BOOKS_PATH);
        }

        $stmt = $this->db()->prepare('DELETE FROM books WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $this->redirect(self::BOOKS_PATH);
    }

    public function members(): void
    {
        $this->requireAdmin();

        $stmt = $this->db()->query(
            'SELECT
                u.id,
                u.username,
                u.email,
                u.created_at,
                COUNT(b.id) AS books_count
             FROM users u
             LEFT JOIN books b ON b.user_id = u.id
             GROUP BY u.id, u.username, u.email, u.created_at
             ORDER BY u.created_at DESC, u.id DESC'
        );

        $this->render('admin/members', [
            'members' => $stmt->fetchAll(),
        ]);
    }

    private function requireAdmin(): void
    {
        Auth::requireLogin();

        $userId = Auth::id();
        $isAdmin = $userId !== null && User::isAdmin((int)$userId);

        if (!$isAdmin) {
            http_response_code(403);
            echo 'Accès administrateur requis';
            exit;
        }
    }

    private function db(): PDO
    {
        return Model::connection();
    }

    private function normalizeBookStatus(string $status): string
    {
        return in_array($status, self::ALLOWED_BOOK_STATUSES, true) ? $status : 'available';
    }
}
