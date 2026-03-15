<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    public static function check(): bool
    {
        return (bool) Session::get('user_id');
    }

    public static function id(): ?int
    {
        $id = Session::get('user_id');
        return $id ? (int)$id : null;
    }

    public static function user(): ?array
    {
        $id = self::id();
        if (!$id) return null;

        $u = new User();
        return $u->findById($id);
    }

    public static function isAdmin(): bool
    {
        $user = self::user();
        if (!$user || empty($user['email'])) {
            return false;
        }

        $config = require CONFIG_PATH . '/config.php';
        $raw = (string)($config['app']['admin_emails'] ?? '');
        if ($raw === '') {
            return false;
        }

        $adminEmails = array_filter(array_map(
            static fn(string $email): string => strtolower(trim($email)),
            explode(',', $raw)
        ));

        return in_array(strtolower((string)$user['email']), $adminEmails, true);
    }

    public static function login(int $userId): void
    {
        Session::regenerate();
        Session::set('user_id', $userId);
    }

    public static function logout(): void
    {
        Session::forget('user_id');
        Session::regenerate();
    }
}
