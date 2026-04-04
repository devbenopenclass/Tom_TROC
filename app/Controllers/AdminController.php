<?php
declare(strict_types=1);

namespace App\Controllers;

final class AdminController extends \App\Core\Controller
{
    private const ADMIN_ANCHOR = '#admin-panel';
    private const BOOKS_PATH = '/admin/books#admin-panel';
    private const ALLOWED_BOOK_STATUSES = ['available', 'unavailable', 'reserved'];

    public function books(): void
    {
        $this->requireAdmin();

        $query = trim((string)($_GET['q'] ?? ''));
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

        $stmt = \App\Core\Model::connection()->prepare($sql);
        $stmt->execute($params);

        $this->render('admin/books', [
            'books' => $stmt->fetchAll(),
            'query' => $query,
            'adminAnchor' => self::ADMIN_ANCHOR,
        ]);
    }

    public function updateBookStatus(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $status = (string)($_POST['status'] ?? 'available');
        if (!in_array($status, self::ALLOWED_BOOK_STATUSES, true)) {
            $status = 'available';
        }
        if ($id <= 0) {
            $this->redirect(self::BOOKS_PATH);
        }

        $stmt = \App\Core\Model::connection()->prepare('UPDATE books SET status = :status WHERE id = :id');
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

        $stmt = \App\Core\Model::connection()->prepare('DELETE FROM books WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $this->redirect(self::BOOKS_PATH);
    }

    public function members(): void
    {
        $this->requireAdmin();

        $query = trim((string)($_GET['q'] ?? ''));
        $sql = implode("\n", [
            'SELECT',
            '    u.id,',
            '    u.username,',
            '    u.email,',
            '    u.created_at,',
            '    COUNT(b.id) AS books_count',
            'FROM users u',
            'LEFT JOIN books b ON b.user_id = u.id',
        ]);
        $params = [];

        if ($query !== '') {
            $sql .= implode("\n", [
                '',
                'WHERE CAST(u.id AS CHAR) LIKE :query',
                '   OR u.username LIKE :query',
                '   OR u.email LIKE :query',
            ]);
            $params['query'] = '%' . $query . '%';
        }

        $sql .= implode("\n", [
            '',
            'GROUP BY u.id, u.username, u.email, u.created_at',
            'ORDER BY u.created_at DESC, u.id DESC',
        ]);

        $stmt = \App\Core\Model::connection()->prepare($sql);
        $stmt->execute($params);

        $this->render('admin/members', [
            'members' => $stmt->fetchAll(),
            'query' => $query,
            'adminAnchor' => self::ADMIN_ANCHOR,
        ]);
    }

    public function deleteMember(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
        }

        \App\Models\User::delete($id);

        $currentUserId = \App\Core\Auth::id();
        if ($currentUserId !== null && (int)$currentUserId === $id) {
            $_SESSION = [];
            session_destroy();
            $this->redirect('/');
        }

        $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
    }

    private function requireAdmin(): void
    {
        \App\Core\Auth::requireLogin();

        $userId = \App\Core\Auth::id();
        $isAdmin = !empty($_SESSION['is_admin']);
        if (!$isAdmin && $userId !== null) {
            $isAdmin = \App\Models\User::isAdmin((int)$userId);
        }

        if (!$isAdmin) {
            http_response_code(403);
            echo 'Acces administrateur requis';
            exit;
        }
    }
}
