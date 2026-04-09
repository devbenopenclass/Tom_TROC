<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Book;
use App\Models\User;

final class AdminController extends \App\Core\Controller
{
    private const ADMIN_ANCHOR = '#admin-panel';
    private const BOOKS_PATH = '/admin/books#admin-panel';
    private const ALLOWED_BOOK_STATUSES = ['available', 'unavailable', 'reserved'];
    private const ALLOWED_MEMBER_ROLES = ['user', 'admin'];

    public function books(): void
    {
        $this->requireAdmin();

        $query = trim((string)($_GET['q'] ?? ''));
        $this->render('admin/books', [
            'books' => Book::adminList($query),
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

        Book::adminUpdateStatus($id, $status);

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

        Book::adminDelete($id);

        $this->redirect(self::BOOKS_PATH);
    }

    public function members(): void
    {
        $this->requireAdmin();

        $query = trim((string)($_GET['q'] ?? ''));
        $members = User::adminMembers($query);
        foreach ($members as &$member) {
            $member['role_label'] = User::roleLabel((int)$member['id']);
            $member['is_current_admin'] = (int)($member['id'] ?? 0) === (int)(Auth::id() ?? 0);
        }
        unset($member);

        $this->render('admin/members', [
            'members' => $members,
            'query' => $query,
            'adminAnchor' => self::ADMIN_ANCHOR,
        ]);
    }

    public function updateMemberRole(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        $role = (string)($_POST['role'] ?? 'user');

        if ($id <= 0 || !in_array($role, self::ALLOWED_MEMBER_ROLES, true)) {
            $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
        }

        $currentUserId = (int)(\App\Core\Auth::id() ?? 0);
        if ($currentUserId > 0 && $currentUserId === $id && $role !== 'admin') {
            $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
        }

        try {
            User::updateRole($id, $role);
        } catch (\RuntimeException $e) {
            $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
        }

        if ($currentUserId > 0 && $currentUserId === $id) {
            $_SESSION['is_admin'] = $role === 'admin';
            $_SESSION['user_role'] = $role;
        }

        $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
    }

    public function deleteMember(): void
    {
        $this->requireAdmin();
        $this->requireCsrf();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
        }

        User::delete($id);

        $currentUserId = Auth::id();
        if ($currentUserId !== null && (int)$currentUserId === $id) {
            $_SESSION = [];
            session_destroy();
            $this->redirect('/');
        }

        $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
    }

    private function requireAdmin(): void
    {
        Auth::requireLogin();

        $userId = Auth::id();
        $isAdmin = !empty($_SESSION['is_admin']);
        if (!$isAdmin && $userId !== null) {
            $isAdmin = User::isAdmin((int)$userId);
        }

        if (!$isAdmin) {
            http_response_code(403);
            echo 'Acces administrateur requis';
            exit;
        }
    }
}
