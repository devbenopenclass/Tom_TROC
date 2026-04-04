<?php
declare(strict_types=1);

namespace App\Controllers;
public/index.php → app/Core/App.php → app/Core/Router.php → app/Core/View.php → app/Models/Book.php → app/Controllers/AuthController.php → app/Core/Csrf.php → app/Controllers/AdminController.php → storage/schema.sql
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
    private const MEMBERS_PATH = '/admin/members';
    private const ALLOWED_BOOK_STATUSES = ['available', 'unavailable', 'reserved'];
    private const ALLOWED_BOOK_SORTS = ['recent', 'title_asc', 'title_desc'];

    public function books(): void
    {
        $this->requireAdmin();
        User::purgeExpiredDeleted();

        $q = trim((string)($_GET['q'] ?? ''));
        $status = $this->normalizeBookStatusFilter((string)($_GET['status'] ?? 'all'));
        $sort = $this->normalizeBookSort((string)($_GET['sort'] ?? 'recent'));

        $sql = 'SELECT b.*, u.username, u.email
             FROM books b
             JOIN users u ON u.id = b.user_id
             WHERE ' . User::activeSqlCondition('u');
        $params = [];

        if ($q !== '') {
            $sql .= '
             AND (b.title LIKE :q OR b.author LIKE :q OR u.username LIKE :q)';
            $params['q'] = "%{$q}%";
        }

        if ($status !== 'all') {
            $sql .= '
             AND b.status = :status';
            $params['status'] = $status;
        }

        $sql .= match ($sort) {
            'title_asc' => '
             ORDER BY b.title ASC, b.id DESC',
            'title_desc' => '
             ORDER BY b.title DESC, b.id DESC',
            default => '
             ORDER BY b.created_at DESC, b.id DESC',
        };

        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);

        $this->render('admin/books', [
            'books' => $stmt->fetchAll(),
            'q' => $q,
            'statusFilter' => $status,
            'sortFilter' => $sort,
        ]);
    }

    public function members(): void
    {
        $this->requireAdmin();
        User::purgeExpiredDeleted();

        $stmt = $this->db()->prepare(
            'SELECT
                u.id,
                u.username,
                u.email,
                u.created_at,
                u.deleted_at,
                COUNT(b.id) AS books_count
             FROM users u
             LEFT JOIN books b ON b.user_id = u.id
             GROUP BY u.id, u.username, u.email, u.created_at, u.deleted_at
             ORDER BY (u.deleted_at IS NULL) DESC, u.username ASC, u.id ASC'
        );
        $stmt->execute();

        $this->render('admin/members', [
            'members' => $stmt->fetchAll(),
        ]);
    }

    public function updateBookStatus(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $status = $this->normalizeBookStatus((string)($_POST['status'] ?? 'available'));
        if ($id <= 0) {
            $this->redirectBooks();
        }

        $stmt = $this->db()->prepare('UPDATE books SET status = :status WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'status' => $status,
        ]);

        $this->redirectBooks();
    }

    public function deleteBook(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirectBooks();
        }

        $stmt = $this->db()->prepare('DELETE FROM books WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $this->redirectBooks();
    }

    public function deleteMember(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $currentUserId = (int)(Auth::id() ?? 0);

        if ($id <= 0 || $id === $currentUserId || User::isAdmin($id)) {
            $this->redirectMembers();
            return;
        }

        User::softDelete($id);
        $this->redirectMembers();
    }

    public function restoreMember(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirectMembers();
            return;
        }

        User::restore($id);
        $this->redirectMembers();
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

    private function redirectBooks(): void
    {
        $this->redirect(self::BOOKS_PATH);
    }

    private function redirectMembers(): void
    {
        $this->redirect(self::MEMBERS_PATH);
    }

    private function normalizeBookStatus(string $status): string
    {
        return in_array($status, self::ALLOWED_BOOK_STATUSES, true) ? $status : 'available';
    }

    private function normalizeBookStatusFilter(string $status): string
    {
        return $status === 'all' || in_array($status, self::ALLOWED_BOOK_STATUSES, true)
            ? $status
            : 'all';
    }

    private function normalizeBookSort(string $sort): string
    {
        return in_array($sort, self::ALLOWED_BOOK_SORTS, true) ? $sort : 'recent';
    }
}
