<?php
declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf_token');
        if (!$token) {
            $token = bin2hex(random_bytes(16));
            Session::set('_csrf_token', $token);
        }
        return $token;
    }

    public static function input(): string
    {
        $t = self::token();
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars($t, ENT_QUOTES, 'UTF-8') . '">';
    }

    public static function verify(?string $token): bool
    {
        $expected = Session::get('_csrf_token');
        return is_string($expected) && is_string($token) && hash_equals($expected, $token);
    }
}
